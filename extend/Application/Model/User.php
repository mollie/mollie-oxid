<?php


namespace Mollie\Payment\extend\Application\Model;

use OxidEsales\Eshop\Core\Registry;

class User extends User_parent
{
    /**
     * Trigger setting auto groups when user is not logged in yet
     *
     * @return void
     */
    public function mollieSetAutoGroups()
    {
        $this->_setAutoGroups($this->oxuser__oxcountryid->value);
    }

    /**
     * Oxid core function getEncodedDeliveryAddress does not hash the delivery address but the invoice address
     * This really hashes the delivery address
     *
     * @return string
     */
    public function mollieGetEncodedDeliveryAddress()
    {
        $sDelAddress = '';

        $soxAddressId = Registry::getSession()->getVariable('deladrid');
        if ($soxAddressId) {
            $oDelAddress = oxNew(\OxidEsales\Eshop\Application\Model\Address::class);
            $oDelAddress->load($soxAddressId);

            $sDelAddress .= $oDelAddress->oxaddress__oxuserid->value;
            $sDelAddress .= $oDelAddress->oxaddress__oxfname->value;
            $sDelAddress .= $oDelAddress->oxaddress__oxlname->value;
            $sDelAddress .= $oDelAddress->oxaddress__oxstreet->value;
            $sDelAddress .= $oDelAddress->oxaddress__oxstreetnr->value;
            $sDelAddress .= $oDelAddress->oxaddress__oxaddinfo->value;
            $sDelAddress .= $oDelAddress->oxaddress__oxcity->value;
            $sDelAddress .= $oDelAddress->oxaddress__oxzip->value;
            $sDelAddress .= $oDelAddress->oxaddress__oxcountry->value;
            $sDelAddress .= $oDelAddress->oxaddress__oxcountryid->value;
        }
        return md5($sDelAddress);
    }
}