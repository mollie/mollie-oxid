<?php

namespace Mollie\Payment\Application\Model\Request;

use Mollie\Payment\Application\Model\RequestLog;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\BasketItem;
use OxidEsales\Eshop\Application\Model\Order as CoreOrder;
use OxidEsales\Eshop\Application\Model\OrderArticle;
use OxidEsales\Eshop\Core\Price;
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
            $sTranslatedSalutation = Registry::getLang()->translateString($oOrder->oxorder__oxbillsal->value);
            if (!empty($sTranslatedSalutation)) {
                $aReturn['title'] = $sTranslatedSalutation;
            }
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
            $sTranslatedSalutation = Registry::getLang()->translateString($oOrder->oxorder__oxdelsal->value);
            if (!empty($sTranslatedSalutation)) {
                $aReturn['title'] = $sTranslatedSalutation;
            }
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
     * Returns vat value for given brut price
     *
     * @param double $dBrutPrice
     * @param double $dVat
     * @return double
     */
    protected function getVatValue($dBrutPrice, $dVat)
    {
        $oPrice = oxNew(Price::class);
        $oPrice->setBruttoPriceMode();
        $oPrice->setPrice($dBrutPrice);
        $oPrice->setVat($dVat);
        return $oPrice->getVatValue();
    }

    /**
     * Search basket content for discounted item with amount 1
     *
     * @param array $aItems
     * @param double $dMismatchSum
     * @return string|false
     */
    protected function getArtnumForCorrection($aItems, $dMismatchSum)
    {
        // search for product mismatch in unitPrice and totalAmount
        foreach ($aItems as $aItem) {
            if ($aItem['quantity'] > 1) {
                $dCalculatedTotalAmount = bcmul($aItem['unitPrice']['value'], $aItem['quantity'], 2);
                $dTotalMismatch = bcsub($dCalculatedTotalAmount, $aItem['totalAmount']['value'], 2);
                if ($dTotalMismatch != 0 && $dTotalMismatch == $dMismatchSum) {
                    return $aItem['sku'];
                }
            }
        }

        // search for discounted basketitem with amount = 1
        foreach (Registry::getSession()->getBasket()->getContents() as $oBasketItem) {
            if ($oBasketItem->getRegularUnitPrice()->getBruttoPrice() > $oBasketItem->getUnitPrice()->getBruttoPrice() && $oBasketItem->getAmount() == 1) { // product is discounted ?!?
                $oArticle = $oBasketItem->getArticle();
                if ($oArticle) {
                    return $oArticle->oxarticles__oxartnum->value;
                }
            }
        }
        return false;
    }

    /**
     * Fix price mismatch in items array
     *
     * @param CoreOrder $oOrder
     * @param array $aItems
     * @param double $dMismatchSum
     * @return array
     */
    protected function getFixedItemArray(CoreOrder $oOrder, $aItems, $dMismatchSum)
    {
        $blFixed = false;

        $sFixArtnum = $this->getArtnumForCorrection($aItems, $dMismatchSum);
        for($i = 0; $i < count($aItems); $i++) {
            $dCalculatedTotalAmount = bcmul($aItems[$i]['unitPrice']['value'], $aItems[$i]['quantity'], 2);
            $dTotalMismatch = bcsub($dCalculatedTotalAmount, $aItems[$i]['totalAmount']['value'], 2);
            $blChangeTotalOnly = false;
            if ($dTotalMismatch != 0 && $dTotalMismatch == $dMismatchSum) {
                $blChangeTotalOnly = true;
            }
            if (($sFixArtnum === false && $aItems[$i]['quantity'] == 1) || // No specific product to be fixed found - use one with quantity of 1
                $sFixArtnum == $aItems[$i]['sku']// getArtnumForCorrection method found a product to be fixed
            ) {
                if ($blChangeTotalOnly === false) {
                    $aItems[$i]['unitPrice']['value'] = $this->formatPrice($aItems[$i]['unitPrice']['value'] + $dMismatchSum);
                }
                $aItems[$i]['totalAmount']['value'] = $this->formatPrice($aItems[$i]['totalAmount']['value'] + $dMismatchSum);
                $aItems[$i]['vatAmount']['value'] = $this->formatPrice($this->getVatValue($aItems[$i]['totalAmount']['value'], $aItems[$i]['vatRate']));
                $blFixed = true;
                break;
            }
        }

        if ($blFixed === false) {
            $aItems[] = [
                'name' => Registry::getLang()->translateString('MOLLIE_ROUNDINGCORRECTION'),
                'sku' => 'adjustment',
                'type' => 'surcharge',
                'quantity' => 1,
                'unitPrice' => $this->getAmountArray($dMismatchSum, $oOrder->oxorder__oxcurrency->value),
                'discountAmount' => $this->getAmountArray(0, $oOrder->oxorder__oxcurrency->value),
                'totalAmount' => $this->getAmountArray($dMismatchSum, $oOrder->oxorder__oxcurrency->value),
                'vatRate' => $oOrder->oxorder__oxartvat1->value,
                'vatAmount' => $this->getAmountArray($this->getVatValue($dMismatchSum, $oOrder->oxorder__oxartvat1->value), $oOrder->oxorder__oxcurrency->value),
            ];
        }
        return $aItems;
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

        $dProductSum = 0;
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

            $dProductSum = bcadd($dProductSum, $oOrderarticle->oxorderarticles__oxbrutprice->value, 4);
        }

        $blNettoMode = Registry::getSession()->getBasket()->isCalculationModeNetto();

        $oVoucherDiscount = oxNew(\OxidEsales\Eshop\Core\Price::class);
        if ($oOrder->oxorder__oxvoucherdiscount->value != 0) {
            $oVoucherDiscount->setBruttoPriceMode();
            if ($blNettoMode === true) {
                $oVoucherDiscount->setNettoPriceMode();
            }
            $oVoucherDiscount->setPrice($oOrder->oxorder__oxvoucherdiscount->value, $oOrder->oxorder__oxartvat1->value);
            if ($blNettoMode === true) {
                $dProductSum = bcsub($dProductSum, $oVoucherDiscount->getBruttoPrice(), 4); // voucher discount is only included in oxtotalbrutsum when shop is in netto mode...
            }
        }

        $oDiscount = oxNew(\OxidEsales\Eshop\Core\Price::class);
        if ($oOrder->oxorder__oxdiscount->value != 0) {
            $oDiscount->setBruttoPriceMode();
            if ($blNettoMode === true) {
                $oDiscount->setNettoPriceMode();
            }
            $oDiscount->setPrice($oOrder->oxorder__oxdiscount->value, $oOrder->oxorder__oxartvat1->value);
            if ($blNettoMode === true) {
                $dProductSum = bcsub($dProductSum, $oDiscount->getBruttoPrice(), 4); // discount is only included in oxtotalbrutsum when shop is in netto mode...
            }
        }

        $dMismatchSum = bcsub($oOrder->oxorder__oxtotalbrutsum->value, $dProductSum, 2);
        if ($dMismatchSum != 0) {
            $aItems = $this->getFixedItemArray($oOrder, $aItems, $dMismatchSum);
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
            $iFixedVat = round($oOrder->oxorder__oxwrapvat->value, 0);  // should be $oOrder->oxorder__oxwrapvat->value but seems to be buggy i.e. "18.951612903226"
            $dWrapVatValue = $this->getVatValue($oOrder->getOrderWrappingPrice()->getBruttoPrice(), $iFixedVat);

            $aItems[] = [
                'name' => Registry::getLang()->translateString('MOLLIE_WRAPPING'),
                'sku' => 'wrapping',
                'type' => 'surcharge',
                'quantity' => 1,
                'unitPrice' => $this->getAmountArray($oOrder->oxorder__oxwrapcost->value, $sCurrency),
                'discountAmount' => $this->getAmountArray(0, $sCurrency),
                'totalAmount' => $this->getAmountArray($oOrder->oxorder__oxwrapcost->value, $sCurrency),
                'vatRate' => $iFixedVat,
                'vatAmount' => $this->getAmountArray($dWrapVatValue, $sCurrency),
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
            $aItems[] = [
                'name' => Registry::getLang()->translateString('MOLLIE_VOUCHER'),
                'sku' => 'voucher',
                'type' => 'gift_card',
                'quantity' => 1,
                'unitPrice' => $this->getAmountArray(0, $sCurrency),
                'discountAmount' => $this->getAmountArray($oVoucherDiscount->getBruttoPrice(), $sCurrency),
                'totalAmount' => $this->getAmountArray($oVoucherDiscount->getBruttoPrice() * -1, $sCurrency),
                'vatRate' => $oOrder->oxorder__oxartvat1->value,
                'vatAmount' => $this->getAmountArray($oVoucherDiscount->getVatValue() * -1, $sCurrency),
            ];
        }

        if ($oOrder->oxorder__oxdiscount->value != 0) {
            $aItems[] = [
                'name' => Registry::getLang()->translateString('MOLLIE_DISCOUNT'),
                'sku' => 'discount',
                'type' => 'discount',
                'quantity' => 1,
                'unitPrice' => $this->getAmountArray(0, $sCurrency),
                'discountAmount' => $this->getAmountArray($oDiscount->getBruttoPrice(), $sCurrency),
                'totalAmount' => $this->getAmountArray($oDiscount->getBruttoPrice() * -1, $sCurrency),
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
