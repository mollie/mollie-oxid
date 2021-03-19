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
        $aDeliveryList = oxNew(\OxidEsales\Eshop\Application\Model\DeliveryList::class)->getDeliveryList(
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
     * Sorts the array to have the current shipping id first
     *
     * @param  array $aDelMethods
     * @param  string $sShippingId
     * @return array
     */
    protected function getSortedDeliveryMethods($aDelMethods, $sShippingId)
    {
        for ($i = 0; $i < count($aDelMethods); $i++) {
            if ($aDelMethods[$i]['identifier'] == $sShippingId) {
                $aSelectedDeliveryMethod = $aDelMethods[$i];
                unset($aDelMethods[$i]);
                $aDelMethods = array_merge([$aSelectedDeliveryMethod], $aDelMethods);
                break;
            }
        }
        return $aDelMethods;
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
        // load sets, active set, and active set payment list
        list($aAllSets, $sActShipSet, $aPaymentList) =
            \OxidEsales\Eshop\Core\Registry::get(\OxidEsales\Eshop\Application\Model\DeliverySetList::class)->getDeliverySetData(null, $oUser, $oBasket);

        $sShippingIdPre = \OxidEsales\Eshop\Core\Registry::getSession()->getVariable('sShipSet');

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

        // ShipSet may be changed inside of  OxidCore function getDeliveryList used in calcDeliveryPrice
        // this would have undesired sideeffects together with the ApplePay integration
        \OxidEsales\Eshop\Core\Registry::getSession()->setVariable('sShipSet', $sShippingIdPre);

        if ($oBasket->getShippingId()) {
            $aDelMethods = $this->getSortedDeliveryMethods($aDelMethods, $oBasket->getShippingId());
        }

        return $aDelMethods;
    }
}
