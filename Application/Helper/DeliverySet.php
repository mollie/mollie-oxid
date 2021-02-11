<?php

namespace Mollie\Payment\Application\Helper;

use OxidEsales\Eshop\Core\Registry;

class DeliverySet
{
    /**
     * @var DeliverySet
     */
    protected static $oInstance = null;

    /**
     * Create singleton instance of order helper
     *
     * @return DeliverySet
     */
    public static function getInstance()
    {
        if (self::$oInstance === null) {
            self::$oInstance = oxNew(self::class);
        }
        return self::$oInstance;
    }

    /**
     * Resets singleton class
     * Needed for unit testing
     *
     * @return void
     */
    public static function destroyInstance()
    {
        self::$oInstance = null;
    }

    /**
     * Returns object of the configured home country
     *
     * @return bool|object
     */
    public function getHomeCountry()
    {
        $aHomeCountries = Registry::getConfig()->getConfigParam('aHomeCountry');
        if (!empty($aHomeCountries)) {
            $sCountryId = current($aHomeCountries); // get first element

            $oCountry = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
            if ($oCountry->load($sCountryId)) {
                return $oCountry;
            }
        }
        return false;
    }

    /**
     * Calculates the price for a given deliveryset
     *
     * @param  string $sDeliverySetId
     * @param  \OxidEsales\Eshop\Application\Model\User $oUser
     * @param  \OxidEsales\Eshop\Application\Model\Basket $oBasket
     * @return float
     */
    protected function calcDeliveryPrice($sDeliverySetId, $oUser, $oBasket)
    {
        $myConfig = Registry::getConfig();
        $oDeliveryPrice = oxNew(\OxidEsales\Eshop\Core\Price::class);

        if ($myConfig->getConfigParam('blDeliveryVatOnTop')) {
            $oDeliveryPrice->setNettoPriceMode();
        } else {
            $oDeliveryPrice->setBruttoPriceMode();
        }

        $fDelVATPercent = $oBasket->getAdditionalServicesVatPercent();
        $oDeliveryPrice->setVat($fDelVATPercent);

        // list of active delivery costs
        $aDeliveryList = \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Application\Model\DeliveryList::class)->getDeliveryList(
            $oBasket,
            $oUser,
            $oUser->oxuser__oxcountryid->value,
            $sDeliverySetId
        );

        if (count($aDeliveryList) > 0) {
            foreach ($aDeliveryList as $oDelivery) {
                $oDeliveryPrice->addPrice($oDelivery->getDeliveryPrice($fDelVATPercent));
            }
        }

        return $oDeliveryPrice->getBruttoPrice();
    }

    /**
     * Checks if Apple Pay is enabled for given deliveryset
     *
     * @param  string $sShipSetId
     * @param  \OxidEsales\Eshop\Application\Model\Basket $oBasket
     * @param  \OxidEsales\Eshop\Application\Model\User $oUser
     * @return bool
     */
    public function isDeliverySetAvailableWithPaymentType($sShipSetId, $oBasket, $oUser)
    {
        $aPaymentList = \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Application\Model\PaymentList::class)->getPaymentList($sShipSetId, $oBasket->getPriceForPayment(), $oUser);
        if (array_key_exists('mollieapplepay', $aPaymentList)) {
            return true;
        }
        return false;
    }

    /**
     * Returns array of all applying delivery methods
     *
     * @param  \OxidEsales\Eshop\Application\Model\User $oUser
     * @param  \OxidEsales\Eshop\Application\Model\Basket $oBasket
     * @return array
     */
    public function getDeliveryMethods($oUser, $oBasket)
    {
        $sDetailsProductId = Registry::getRequest()->getRequestEscapedParameter('detailsProductId');
        if (!empty($sDetailsProductId)) { // applies when Apple Pay button on details page is pressed, since product is not in basket yet
            $oBasketItem = $oBasket->addToBasket($sDetailsProductId, 1);
            $oBasket->calculateBasket();
        }

        // load sets, active set, and active set payment list
        list($aAllSets, $sActShipSet, $aPaymentList) =
            \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Application\Model\DeliverySetList::class)->getDeliverySetData(null, $oUser, $oBasket);

        $aDelMethods = array();
        foreach ($aAllSets as $oDelSet) {
            if ($this->isDeliverySetAvailableWithPaymentType($oDelSet->oxdeliveryset__oxid->value, $oBasket, $oUser) === true) {
                $aDelMethods[] = array(
                    'label' => $oDelSet->oxdeliveryset__oxtitle->value,
                    'detail' => '',
                    'amount' => $this->calcDeliveryPrice($oDelSet->oxdeliveryset__oxid->value, $oUser, $oBasket),
                    'identifier' => $oDelSet->oxdeliveryset__oxid->value,
                );
            }
        }

        if (!empty($sDetailsProductId)) { // remove details product from basket again
            $oBasket->removeItem($oBasketItem->getBasketItemKey());
        }

        return $aDelMethods;
    }
}
