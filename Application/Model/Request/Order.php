<?php

namespace Mollie\Payment\Application\Model\Request;

use Mollie\Payment\Application\Helper\Payment as PaymentHelper;
use OxidEsales\Eshop\Application\Model\Order as CoreOrder;
use OxidEsales\Eshop\Core\Registry;

class Order extends Base
{
    /**
     * Determines if the extended address is needed in the params
     *
     * @var bool
     */
    protected $blNeedsExtendedAddress = true;

    /**
     * Format prices to always have 2 decimal places
     *
     * @param double $dPrice
     * @return string
     */
    protected function formatPrice($dPrice)
    {
        return number_format($dPrice, 2, '.', '');
    }

    /**
     * Returns amount array used for different prices
     *
     * @param double $dPrice
     * @param string $sCurrency
     * @return array
     */
    protected function getAmountArray($dPrice, $sCurrency)
    {
        return [
            'value' => $this->formatPrice($dPrice),
            'currency' => $sCurrency
        ];
    }

    /**
     * Add all different types of basket items to the basketline array
     *
     * @param CoreOrder $oOrder
     * @return array
     */
    protected function getBasketItems(CoreOrder $oOrder)
    {
        $aItems = [];

        $sCurrency = $oOrder->oxorder__oxcurrency->value;

        $aOrderArticleListe = $oOrder->getOrderArticles();
        foreach ($aOrderArticleListe->getArray() as $oOrderarticle) {
            $aItems[] = [
                'name' => $oOrderarticle->oxorderarticles__oxtitle->value,
                'sku' => $oOrderarticle->oxorderarticles__oxartnum->value,
                'type' => $oOrderarticle->getArticle()->isDownloadable() ? 'digital' : 'physical',
                'quantity' => $oOrderarticle->oxorderarticles__oxamount->value,
                'unitPrice' => $this->getAmountArray($oOrderarticle->oxorderarticles__oxbprice->value, $sCurrency),
                'discountAmount' => $this->getAmountArray(0, $sCurrency),
                'totalAmount' => $this->getAmountArray($oOrderarticle->oxorderarticles__oxbrutprice->value, $sCurrency),
                'vatRate' => $oOrderarticle->oxorderarticles__oxvat->value,
                'vatAmount' => $this->getAmountArray($oOrderarticle->oxorderarticles__oxvatprice->value, $sCurrency),
                'productUrl' => $oOrderarticle->getArticle()->getLink(),
            ];
        }

        if ($oOrder->oxorder__oxdelcost->value != 0) {
            $aItems[] = [
                'name' => Registry::getLang()->translateString('MOLLIE_SHIPPINGCOST').': '.$oOrder->getDelSet()->oxdeliveryset__oxtitle->value,
                'sku' => $oOrder->oxorder__oxdeltype->value,
                'type' => 'shipping_fee',
                'quantity' => 1,
                'unitPrice' => $this->getAmountArray($oOrder->oxorder__oxdelcost->value, $sCurrency),
                'discountAmount' => $this->getAmountArray(0, $sCurrency),
                'totalAmount' => $this->getAmountArray($oOrder->oxorder__oxdelcost->value, $sCurrency),
                'vatRate' => $oOrder->oxorder__oxdelvat->value,
                'vatAmount' => $this->getAmountArray($oOrder->getOrderDeliveryPrice()->getVatValue(), $sCurrency),
            ];
        }

        if ($oOrder->oxorder__oxpaycost->value != 0) {
            $aItems[] = [
                'name' => Registry::getLang()->translateString('MOLLIE_PAYMENTTYPESURCHARGE').': '.$oOrder->getPaymentType()->oxpayments__oxdesc->value,
                'sku' => $oOrder->oxorder__oxpaymenttype->value,
                'type' => 'surcharge',
                'quantity' => 1,
                'unitPrice' => $this->getAmountArray($oOrder->oxorder__oxpaycost->value, $sCurrency),
                'discountAmount' => $this->getAmountArray(0, $sCurrency),
                'totalAmount' => $this->getAmountArray($oOrder->oxorder__oxpaycost->value, $sCurrency),
                'vatRate' => $oOrder->oxorder__oxpayvat->value,
                'vatAmount' => $this->getAmountArray($oOrder->getOrderPaymentPrice()->getVatValue(), $sCurrency),
            ];
        }

        if ($oOrder->oxorder__oxwrapcost->value != 0) {
            $aItems[] = [
                'name' => Registry::getLang()->translateString('MOLLIE_WRAPPING'),
                'sku' => 'wrapping',
                'type' => 'surcharge',
                'quantity' => 1,
                'unitPrice' => $this->getAmountArray($oOrder->oxorder__oxwrapcost->value, $sCurrency),
                'discountAmount' => $this->getAmountArray(0, $sCurrency),
                'totalAmount' => $this->getAmountArray($oOrder->oxorder__oxwrapcost->value, $sCurrency),
                'vatRate' => $oOrder->oxorder__oxwrapvat->value,
                'vatAmount' => $this->getAmountArray($oOrder->getOrderWrappingPrice()->getVatValue(), $sCurrency),
            ];
        }

        if ($oOrder->oxorder__oxgiftcardcost->value != 0) {
            $aItems[] = [
                'name' => Registry::getLang()->translateString('MOLLIE_GIFTCARD').': '.$oOrder->getGiftCard()->oxwrapping__oxname->value,
                'sku' => 'giftcard',
                'type' => 'surcharge',
                'quantity' => 1,
                'unitPrice' => $this->getAmountArray($oOrder->oxorder__oxgiftcardcost->value, $sCurrency),
                'discountAmount' => $this->getAmountArray(0, $sCurrency),
                'totalAmount' => $this->getAmountArray($oOrder->oxorder__oxgiftcardcost->value, $sCurrency),
                'vatRate' => $oOrder->oxorder__oxgiftcardvat->value,
                'vatAmount' => $this->getAmountArray($oOrder->getOrderGiftCardPrice()->getVatValue(), $sCurrency),
            ];
        }

        if ($oOrder->oxorder__oxvoucherdiscount->value != 0) {
            $oVoucherDiscount = oxNew(\OxidEsales\Eshop\Core\Price::class);
            $oVoucherDiscount->setBruttoPriceMode();
            $oVoucherDiscount->setPrice($oOrder->oxorder__oxvoucherdiscount->value, $oOrder->oxorder__oxartvat1->value);

            $aItems[] = [
                'name' => Registry::getLang()->translateString('MOLLIE_VOUCHER'),
                'sku' => 'voucher',
                'type' => 'gift_card',
                'quantity' => 1,
                'unitPrice' => $this->getAmountArray($oOrder->oxorder__oxvoucherdiscount->value, $sCurrency),
                'discountAmount' => $this->getAmountArray($oOrder->oxorder__oxvoucherdiscount->value, $sCurrency),
                'totalAmount' => $this->getAmountArray($oOrder->oxorder__oxvoucherdiscount->value, $sCurrency),
                'vatRate' => $oOrder->oxorder__oxartvat1->value,
                'vatAmount' => $this->getAmountArray($oVoucherDiscount->getVatValue(), $sCurrency),
            ];
        }

        if ($oOrder->oxorder__oxdiscount->value != 0) {
            $oDiscount = oxNew(\OxidEsales\Eshop\Core\Price::class);
            $oDiscount->setBruttoPriceMode();
            $oDiscount->setPrice($oOrder->oxorder__oxdiscount->value, $oOrder->oxorder__oxartvat1->value);

            $aItems[] = [
                'name' => Registry::getLang()->translateString('MOLLIE_DISCOUNT'),
                'sku' => 'discount',
                'type' => 'discount',
                'quantity' => 1,
                'unitPrice' => $this->getAmountArray($oOrder->oxorder__oxdiscount->value, $sCurrency),
                'discountAmount' => $this->getAmountArray($oOrder->oxorder__oxdiscount->value, $sCurrency),
                'totalAmount' => $this->getAmountArray($oOrder->oxorder__oxdiscount->value, $sCurrency),
                'vatRate' => $oOrder->oxorder__oxartvat1->value,
                'vatAmount' => $this->getAmountArray($oDiscount->getVatValue(), $sCurrency),
            ];
        }

        return $aItems;
    }

    /**
     * Add needed parameters to the API request
     *
     * @param CoreOrder $oOrder
     * @param double $dAmount
     * @param string $sReturnUrl
     * @return void
     */
    protected function addRequestParameters(CoreOrder $oOrder, $dAmount, $sReturnUrl)
    {
        parent::addRequestParameters($oOrder, $dAmount, $sReturnUrl);

        $this->addParameter('orderNumber', (string)$oOrder->oxorder__oxordernr->value);
        $this->addParameter('lines', $this->getBasketItems($oOrder));

        if ($oOrder->getUser()->oxuser__oxbirthday->value != '0000-00-00') {
            $this->addParameter('consumerDateOfBirth', $oOrder->getUser()->oxuser__oxbirthday->value);
        }
    }
}
