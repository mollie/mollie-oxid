<?php

namespace Mollie\Payment\extend\Core;

use Mollie\Payment\Application\Helper\PayPalExpress;
use OxidEsales\Eshop\Core\Registry;
use Mollie\Payment\Application\Helper\DeliverySet;
use Mollie\Payment\Application\Helper\Payment;

class ViewConfig extends ViewConfig_parent
{
    /**
     * Returns if the show icons option was enabled in admin
     *
     * @return bool
     */
    public function mollieShowIcons()
    {
        return (bool)Registry::getConfig()->getShopConfVar('blMollieShowIcons');
    }

    /**
     * Determines if Apple Pay button can be displayed
     *
     * @param  double $dPrice
     * @return bool
     */
    public function mollieCanShowApplePayButton($dPrice)
    {
        $oApplePay = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        if (Payment::getInstance()->getMollieMode() == 'live' && $oApplePay->load('mollieapplepay')) { // Apple Pay only available in live mode
            if ($oApplePay->oxpayments__oxactive->value == 1 && ($oApplePay->oxpayments__oxfromamount->value <= $dPrice && $oApplePay->oxpayments__oxtoamount->value >= $dPrice)) {
                $oMolliePayment = $oApplePay->getMolliePaymentModel();
                if ($oMolliePayment && $oMolliePayment->isMolliePaymentActive() && $oMolliePayment->mollieIsBasketSumInLimits($dPrice) === true) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Returns iso2 country code of the homecountry of the shop
     *
     * @return string|bool
     */
    public function mollieGetHomeCountryCode()
    {
        $oCountry = DeliverySet::getInstance()->getHomeCountry();
        if ($oCountry !== false) {
            return $oCountry->oxcountry__oxisoalpha2->value;
        }
        return false;
    }

    /**
     * Returns current currency
     *
     * @return string
     */
    public function mollieGetCurrentCurrency()
    {
        return Registry::getConfig()->getActShopCurrencyObject()->name;
    }

    /**
     * Returns shop url
     *
     * @return string
     */
    public function mollieGetShopUrl()
    {
        return rtrim(Registry::getConfig()->getSslShopUrl(), '/').'/';
    }

    /**
     * Returns if Apple Pay button should be shown on basket page
     *
     * @return bool
     */
    public function mollieShowApplePayButtonOnBasket()
    {
        return (bool)Registry::getConfig()->getShopConfVar('blMollieApplePayButtonOnBasket');
    }

    /**
     * Returns if Apple Pay button should be shown on product details page
     *
     * @return bool
     */
    public function mollieShowApplePayButtonOnDetails()
    {
        return (bool)Registry::getConfig()->getShopConfVar('blMollieApplePayButtonOnDetails');
    }

    /**
     * Determines if PayPal Express buttons can be shown
     *
     * @return bool
     */
    public function mollieCanShowPayPalExpressButton()
    {
        $oPayPalExpress = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        if ($oPayPalExpress->load(\Mollie\Payment\Application\Model\Payment\PayPalExpress::OXID)) {
            $oBasket = Registry::getSession()->getBasket();

            $blIsValidPayment = $oPayPalExpress->isValidPayment(
                null,
                Registry::getConfig()->getShopId(),
                $oBasket->getUser(),
                $oBasket->getPriceForPayment(),
                $oBasket->getShippingId()
            );

            if ($blIsValidPayment === true) {
                $oMolliePayment = $oPayPalExpress->getMolliePaymentModel();
                if ($oMolliePayment && $oMolliePayment->isMolliePaymentActive()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Returns if PayPal Express button should be shown on basket page
     *
     * @return bool
     */
    public function mollieShowPayPalExpressButtonOnBasket()
    {
        return (bool)Registry::getConfig()->getShopConfVar('blMolliePayPalButtonOnBasket');
    }

    /**
     * Returns if PayPal Express button should be shown on product details page
     *
     * @return bool
     */
    public function mollieShowPayPalExpressButtonOnDetails()
    {
        return (bool)Registry::getConfig()->getShopConfVar('blMolliePayPalButtonOnDetails');
    }

    /**
     * Functionality from Basket->getPriceForPayment() but without the delivery costs since they are added later
     *
     * @return double
     */
    public function mollieGetApplePayBasketSum()
    {
        $oBasket = Registry::getSession()->getBasket();

        $dPrice = $oBasket->getDiscountedProductsBruttoPrice();
        //#1905 not discounted products should be included in payment amount calculation
        if ($oPriceList = $oBasket->getNotDiscountProductsPrice()) {
            $dPrice += $oPriceList->getBruttoSum();
        }

        return $dPrice;
    }

    /**
     * Returns previously set error message from session and deletes it from session
     *
     * @return string
     */
    public function mollieGetErrorMessage()
    {
        $sErrorMessage = Registry::getSession()->getVariable('mollieErrorMessage');
        Registry::getSession()->deleteVariable('mollieErrorMessage');
        return $sErrorMessage;
    }

    /**
     * Returns if a PayPal Express session is active
     *
     * @return bool
     */
    public function isMolliePayPalExpressCheckout()
    {
        return PayPalExpress::getInstance()->isMolliePayPalExpressCheckout();
    }

    /**
     * Returns URL to configured PayPal Express button
     *
     * @return string
     */
    public function getMolliePayPalExpressButtonImageUrl()
    {
        return PayPalExpress::getInstance()->getPayPalButtonUrl();
    }

    /**
     * Checks if Basket modal should be suppressed
     *
     * @return bool
     */
    public function mollieSuppressBasketModal()
    {
        $blReturn = false;
        if ((
                Registry::getSession()->getVariable('mollie_suppress_basket_modal') === true ||
                $this->isMolliePayPalExpressCheckout() ||
                (!empty(Registry::getSession()->getVariable('mollieModalTimeout')) && Registry::getSession()->getVariable('mollieModalTimeout') > time())
            ) &&
            in_array(Registry::getRequest()->getRequestParameter("cl"), ['basket', 'order'])) {
            $blReturn = true;
        }
        Registry::getSession()->deleteVariable('mollie_suppress_basket_modal');
        return $blReturn;
    }
}
