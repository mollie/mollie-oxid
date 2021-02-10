<?php

namespace Mollie\Payment\Application\Model\Request;

use Mollie\Payment\Application\Model\RequestLog;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Order as CoreOrder;
use OxidEsales\Eshop\Application\Model\OrderArticle;
use OxidEsales\Eshop\Core\Registry;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Payment\Application\Helper\Payment as PaymentHelper;

abstract class Base
{
    /**
     * Array or request parameters
     *
     * @var array
     */
    protected $aParameters = [];

    /**
     * Determines if the extended address is needed in the params
     *
     * @var bool
     */
    protected $blNeedsExtendedAddress = false;

    /**
     * Returns collected request parameters
     *
     * @return array
     */
    protected function getParameters()
    {
        return $this->aParameters;
    }

    /**
     * Add parameter to request
     *
     * @param string $sKey
     * @param string|array $mValue
     * @return void
     */
    public function addParameter($sKey, $mValue)
    {
        $this->aParameters[$sKey] = $mValue;
    }

    /**
     * Get amount array
     *
     * @param CoreOrder $oOrder
     * @param double $dAmount
     * @return array
     */
    protected function getAmountParameters(CoreOrder $oOrder, $dAmount)
    {
        return [
            'currency' => $oOrder->oxorder__oxcurrency->value,
            'value' => number_format($dAmount, 2, '.', ''),
        ];
    }

    /**
     * Loads country object and return country iso code
     *
     * @param string $sCountryId
     * @return string
     */
    protected function getCountryCode($sCountryId)
    {
        $oCountry = oxNew('oxcountry');
        $oCountry->load($sCountryId);
        return $oCountry->oxcountry__oxisoalpha2->value;
    }

    /**
     * Convert region id into region title
     *
     * @param string $sRegionId
     * @return string
     */
    protected function getRegionTitle($sRegionId)
    {
        $oState = oxNew('oxState');
        return $oState->getTitleById($sRegionId);
    }

    /**
     * Return billing address parameters
     *
     * @param CoreOrder $oOrder
     * @return array
     */
    protected function getBillingAddressParameters(CoreOrder $oOrder)
    {
        $aReturn = [
            'streetAndNumber' => trim($oOrder->oxorder__oxbillstreet->value.' '.$oOrder->oxorder__oxbillstreetnr->value),
            'postalCode' => $oOrder->oxorder__oxbillzip->value,
            'city' => $oOrder->oxorder__oxbillcity->value,
            'country' => $this->getCountryCode($oOrder->oxorder__oxbillcountryid->value),
        ];
        if (!empty((string)$oOrder->oxorder__oxbillstateid->value)) {
            $aReturn['region'] = $this->getRegionTitle($oOrder->oxorder__oxbillstateid->value);
        }
        if ($this->blNeedsExtendedAddress === true) {
            $aReturn['title'] = Registry::getLang()->translateString($oOrder->oxorder__oxbillsal->value);
            $aReturn['givenName'] = $oOrder->oxorder__oxbillfname->value;
            $aReturn['familyName'] = $oOrder->oxorder__oxbilllname->value;
            $aReturn['email'] = $oOrder->oxorder__oxbillemail->value;
        }
        return $aReturn;
    }

    /**
     * Return shipping address parameters
     *
     * @param CoreOrder $oOrder
     * @return array
     */
    protected function getShippingAddressParameters(CoreOrder $oOrder)
    {
        $aReturn = [
            'streetAndNumber' => trim($oOrder->oxorder__oxdelstreet->value.' '.$oOrder->oxorder__oxdelstreetnr->value),
            'postalCode' => $oOrder->oxorder__oxdelzip->value,
            'city' => $oOrder->oxorder__oxdelcity->value,
            'country' => $this->getCountryCode($oOrder->oxorder__oxdelcountryid->value),
        ];
        if (!empty((string)$oOrder->oxorder__oxbillstateid->value)) {
            $aReturn['region'] = $this->getRegionTitle($oOrder->oxorder__oxdelstateid->value);
        }
        if ($this->blNeedsExtendedAddress === true) {
            $aReturn['title'] = Registry::getLang()->translateString($oOrder->oxorder__oxdelsal->value);
            $aReturn['givenName'] = $oOrder->oxorder__oxdelfname->value;
            $aReturn['familyName'] = $oOrder->oxorder__oxdellname->value;
            $aReturn['email'] = $oOrder->oxorder__oxbillemail->value; // there is no explicit delivery email address
        }
        return $aReturn;
    }

    /**
     * Return metadata parameters
     *
     * @param CoreOrder $oOrder
     * @return array
     */
    protected function getMetadataParameters(CoreOrder $oOrder)
    {
        return [
            'order_id' => $oOrder->getId(),
            'store_id' => $oOrder->getShopId(),
            #'payment_token' => uniqid(), // which role does this field play?
        ];
    }

    /**
     * Return the Mollie webhook url
     *
     * @return string
     */
    protected function getWebhookUrl()
    {
        return Registry::getConfig()->getCurrentShopUrl().'index.php?cl=mollieWebhook';
    }

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
    public function getBasketItems(CoreOrder $oOrder)
    {
        $aItems = [];

        $sCurrency = $oOrder->oxorder__oxcurrency->value;

        $aOrderArticleList = $oOrder->getOrderArticles();
        foreach ($aOrderArticleList->getArray() as $oOrderarticle) {
            $oArticle = $oOrderarticle->getArticle();
            if ($oArticle instanceof OrderArticle) {
                $oArticle = oxNew(Article::class);
                $oArticle->load($oOrderarticle->oxorderarticles__oxartid->value);
            }
            $aItems[] = [
                'name' => $oOrderarticle->oxorderarticles__oxtitle->value,
                'sku' => $oOrderarticle->oxorderarticles__oxartnum->value,
                'type' => $oArticle->isDownloadable() ? 'digital' : 'physical',
                'quantity' => $oOrderarticle->oxorderarticles__oxamount->value,
                'unitPrice' => $this->getAmountArray($oOrderarticle->oxorderarticles__oxbprice->value, $sCurrency),
                'discountAmount' => $this->getAmountArray(0, $sCurrency),
                'totalAmount' => $this->getAmountArray($oOrderarticle->oxorderarticles__oxbrutprice->value, $sCurrency),
                'vatRate' => $oOrderarticle->oxorderarticles__oxvat->value,
                'vatAmount' => $this->getAmountArray($oOrderarticle->oxorderarticles__oxvatprice->value, $sCurrency),
                'productUrl' => $oArticle->getLink(),
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
                'vatRate' => $oOrder->oxorder__oxartvat1->value, // should be $oOrder->oxorder__oxwrapvat->value but seems to be buggy i.e. "18.951612903226"
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
                'unitPrice' => $this->getAmountArray(0, $sCurrency),
                'discountAmount' => $this->getAmountArray($oOrder->oxorder__oxvoucherdiscount->value, $sCurrency),
                'totalAmount' => $this->getAmountArray($oOrder->oxorder__oxvoucherdiscount->value * -1, $sCurrency),
                'vatRate' => $oOrder->oxorder__oxartvat1->value,
                'vatAmount' => $this->getAmountArray($oVoucherDiscount->getVatValue() * -1, $sCurrency),
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
                'unitPrice' => $this->getAmountArray(0, $sCurrency),
                'discountAmount' => $this->getAmountArray($oOrder->oxorder__oxdiscount->value, $sCurrency),
                'totalAmount' => $this->getAmountArray($oOrder->oxorder__oxdiscount->value * -1, $sCurrency),
                'vatRate' => $oOrder->oxorder__oxartvat1->value,
                'vatAmount' => $this->getAmountArray($oDiscount->getVatValue() * -1, $sCurrency),
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
        $oPaymentModel = $oOrder->mollieGetPaymentModel();

        $this->addParameter('method', $oPaymentModel->getMolliePaymentCode());
        $this->addParameter('amount', $this->getAmountParameters($oOrder, $dAmount));

        if ($oPaymentModel->isRedirectUrlNeeded($oOrder) === true) {
            $this->addParameter('redirectUrl', $sReturnUrl);
        }
        $this->addParameter('webhookUrl', $this->getWebhookUrl());

        $this->addParameter('metadata', $this->getMetadataParameters($oOrder));

        $this->addParameter('billingAddress', $this->getBillingAddressParameters($oOrder));
        if ($oOrder->oxorder__oxdellname->value != '') {
            $this->addParameter('shippingAddress', $this->getShippingAddressParameters($oOrder));
        }

        $this->addParameter('locale', PaymentHelper::getInstance()->getLocale());

        $aPaymentSpecificParameters = $oPaymentModel->getPaymentSpecificParameters($oOrder);
        if (!empty($aPaymentSpecificParameters) && $oPaymentModel->getApiMethod() == 'order') {
            $aPaymentSpecificParameters = ['payment' => $aPaymentSpecificParameters];
        }

        $this->aParameters = array_merge($this->aParameters, $aPaymentSpecificParameters);
    }

    /**
     * Execute Request to Mollie API and return Response
     *
     * @param CoreOrder $oOrder
     * @param double $dAmount
     * @param string $sReturnUrl
     * @return \Mollie\Api\Resources\Payment
     * @throws ApiException
     */
    public function sendRequest(CoreOrder $oOrder, $dAmount, $sReturnUrl)
    {
        $this->addRequestParameters($oOrder, $dAmount, $sReturnUrl);

        $oRequestLog = oxNew(RequestLog::class);
        try {
            $oResponse = $oOrder->mollieGetPaymentModel()->getApiEndpoint()->create($this->getParameters());

            $oRequestLog->logRequest($this->getParameters(), $oResponse);
        } catch (ApiException $exc) {
            $oRequestLog->logExceptionResponse($this->getParameters(), $exc->getCode(), $exc->getMessage(), $oOrder->mollieGetPaymentModel()->getApiMethod());
            throw $exc;
        }

        if (isset($oResponse->details->failureMessage)) {
            throw new ApiException($oResponse->details->failureMessage);
        } elseif (isset($oResponse->extra->failureMessage)) {
            throw new ApiException($oResponse->extra->failureMessage);
        }

        return $oResponse;
    }
}
