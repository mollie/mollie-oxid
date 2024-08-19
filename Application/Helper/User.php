<?php

namespace Mollie\Payment\Application\Helper;

use Mollie\Payment\Application\Helper\DeliverySet;
use Mollie\Payment\Application\Helper\User as UserHelper;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Application\Model\Country;

class User
{
    /**
     * @var User
     */
    protected static $oInstance = null;

    /**
     * Create singleton instance of order helper
     *
     * @return User
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
     * Splits single line address given by Apple Pay into 2 fields
     *
     * @param  array $aStreet
     * @return array
     */
    public function splitStreet($aStreet)
    {
        preg_match('#^([\w\ß[:punct:] ]+) ([0-9]{1,5}\s?[\w[:punct:]\-/]*)$#', $aStreet[0], $aMatch);
        unset($aStreet[0]);

        return array(
            'street' => isset($aMatch[1]) ? $aMatch[1] : $aStreet[0],
            'number' => isset($aMatch[2]) ? $aMatch[2] : '',
            'addinfo' => !empty($aStreet) ? implode(' ', $aStreet) : '',
        );
    }

    /**
     * Tries to fetch the state from the database
     * Administrative area is a free text field, so we dont know what we get here
     *
     * @param  string $sAdministrativeArea
     * @return string|false
     */
    public function getStateFromAdministrativeArea($sAdministrativeArea)
    {
        if(empty($sAdministrativeArea)) {
            return false;
        }

        $sQuery = "SELECT 
                       oxid 
                   FROM 
                       oxstates 
                   WHERE 
                       oxtitle = :state OR 
                       oxtitle_1 = :state OR 
                       oxtitle_2 = :state OR 
                       oxtitle_3 = :state OR 
                       oxisoalpha2 = :state
                   LIMIT 1";
        return \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne($sQuery, [
            ':state' => $sAdministrativeArea,
        ]);
    }

    /**
     * Apple Pay does not supply a salutation but the oxid shop needs it for certain tasks
     * Trying to get a salutation by the firstname
     *
     * @param  string $sFirstname
     * @return string|false
     */
    public function getSalByFirstname($sFirstname)
    {
        $sQuery = "SELECT oxsal FROM oxuser WHERE oxfname = ? LIMIT 1";
        return \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne($sQuery, array($sFirstname));
    }

    /**
     * Generates a new dummy-user
     *
     * @return \OxidEsales\Eshop\Application\Model\User
     */
    protected function getApplePayDummyUser()
    {
        $oUser = oxNew(\OxidEsales\Eshop\Application\Model\User::class);

        // setting object id as it is requested later while processing user object
        $oUser->setId(\OxidEsales\Eshop\Core\UtilsObject::getInstance()->generateUID());

        $this->setInitialDeliveryData(
            $oUser,
            Registry::getRequest()->getRequestEscapedParameter('countryCode'),
            Registry::getRequest()->getRequestEscapedParameter('city'),
            Registry::getRequest()->getRequestEscapedParameter('zip')
        );

        $aShippingContact = Registry::getRequest()->getRequestEscapedParameter('shippingContact');
        if (!empty($aShippingContact)) {
            $oUser->oxuser__oxusername = new Field($aShippingContact['emailAddress']);
            $this->updateUserFromApplePayData($oUser);
            $oUser->save();
        }

        $oUser->mollieSetAutoGroups();

        return $oUser;
    }

    /**
     * Generates a new dummy-user
     *
     * @param  \stdClass $
     * @return \OxidEsales\Eshop\Application\Model\User
     */
    protected function getMollieSessionDummyUser($oMollieSessionAddress)
    {
        $oUser = oxNew(\OxidEsales\Eshop\Application\Model\User::class);

        // setting object id as it is requested later while processing user object
        $oUser->setId(\OxidEsales\Eshop\Core\UtilsObject::getInstance()->generateUID());

        $this->setInitialDeliveryData(
            $oUser,
            $oMollieSessionAddress->country,
            $oMollieSessionAddress->city,
            $oMollieSessionAddress->postalCode
        );

        $oUser->oxuser__oxusername = new Field($oMollieSessionAddress->email);
        $this->updateUserFromMollieSessionData($oUser, $oMollieSessionAddress);
        $oUser->save();

        $oUser->mollieSetAutoGroups();

        return $oUser;
    }

    /**
     * Sets delivery information with zip, city and country given by apple pay initially
     *
     * @param  \OxidEsales\Eshop\Application\Model\User $oUser
     * @return void
     */
    public function setInitialDeliveryData(&$oUser, $sCountryCode, $sCity, $sZip)
    {
        if (!empty($sCountryCode)) {
            $oUser->oxuser__oxcity = new Field($sCity);
            $oUser->oxuser__oxzip = new Field($sZip);

            $country = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
            $countryId = $country->getIdByCode($sCountryCode);
            $oUser->oxuser__oxcountryid = new Field($countryId);
        } else {
            $oHomeCountry = DeliverySet::getInstance()->getHomeCountry();
            if ($oHomeCountry !== false) {
                $oUser->oxuser__oxcountryid = new Field($oHomeCountry->getId());
            }
        }
    }

    /**
     * Updates user object with address data from apple pay
     *
     * @param  \OxidEsales\Eshop\Application\Model\User $oUser
     * @return void
     */
    protected function updateUserFromApplePayData(&$oUser)
    {
        $aBillingContact = Registry::getRequest()->getRequestEscapedParameter('billingContact');
        if (!empty($aBillingContact)) {
            // bill address
            $oUser->oxuser__oxfname = new Field($aBillingContact['givenName']);
            $oUser->oxuser__oxlname = new Field($aBillingContact['familyName']);

            $aBillingStreetSplitInfo = UserHelper::getInstance()->splitStreet($aBillingContact['addressLines']);
            $oUser->oxuser__oxstreet = new Field($aBillingStreetSplitInfo['street']);
            $oUser->oxuser__oxstreetnr = new Field($aBillingStreetSplitInfo['number']);
            $oUser->oxuser__oxaddinfo = new Field($aBillingStreetSplitInfo['addinfo']);
            $oUser->oxuser__oxcity = new Field($aBillingContact['locality']);
            $oUser->oxuser__oxcountryid = new Field(oxNew(Country::class)->getIdByCode($aBillingContact['countryCode']));
            $oUser->oxuser__oxstateid = new Field(UserHelper::getInstance()->getStateFromAdministrativeArea($aBillingContact['administrativeArea']));
            $oUser->oxuser__oxzip = new Field($aBillingContact['postalCode']);
            $oUser->oxuser__oxsal = new Field(UserHelper::getInstance()->getSalByFirstname($aBillingContact['givenName']));
        }
    }

    /**
     * Updates user object with address data from apple pay
     *
     * @param  \OxidEsales\Eshop\Application\Model\User $oUser
     * @return void
     */
    protected function updateUserFromMollieSessionData(&$oUser, $oMollieSessionAddress)
    {
        // bill address
        $oUser->oxuser__oxfname = new Field($oMollieSessionAddress->givenName);
        $oUser->oxuser__oxlname = new Field($oMollieSessionAddress->familyName);

        $aBillingStreetSplitInfo = UserHelper::getInstance()->splitStreet([$oMollieSessionAddress->streetAndNumber]);
        $oUser->oxuser__oxstreet = new Field($aBillingStreetSplitInfo['street']);
        $oUser->oxuser__oxstreetnr = new Field($aBillingStreetSplitInfo['number']);
        $oUser->oxuser__oxaddinfo = new Field($aBillingStreetSplitInfo['addinfo']);
        $oUser->oxuser__oxcity = new Field($oMollieSessionAddress->city);
        $oUser->oxuser__oxcountryid = new Field(oxNew(Country::class)->getIdByCode($oMollieSessionAddress->country));
        $oUser->oxuser__oxzip = new Field($oMollieSessionAddress->postalCode);
        $oUser->oxuser__oxsal = new Field(UserHelper::getInstance()->getSalByFirstname($oMollieSessionAddress->givenName));
    }

    /**
     * Returns billing country code of current basket
     *
     * @param  Basket $oBasket
     * @return string
     */
    public function getBillingCountry($oBasket)
    {
        $oUser = $oBasket->getBasketUser();

        $oCountry = oxNew(Country::class);
        $oCountry->load($oUser->oxuser__oxcountryid->value);

        return $oCountry->oxcountry__oxisoalpha2->value;
    }

    /**
     * Fills an Oxid Address object with information from Mollie session address
     *
     * @param  $oUser
     * @param  \stdClass $oMollieSessionAddress
     * @return void
     */
    protected function setShippingAddressFromMollieSession($oUser, $oMollieSessionAddress)
    {
        $sAddressId = $this->getExistingAddressId($oUser, $oMollieSessionAddress);

        $oAddress = oxNew(\OxidEsales\Eshop\Application\Model\Address::class);
        if (!empty($sAddressId)) {
            $oAddress->setId($sAddressId);
            $oAddress->load($sAddressId);
        }

        $oAddress->oxaddress__oxuserid = new \OxidEsales\Eshop\Core\Field($oUser->getId(), \OxidEsales\Eshop\Core\Field::T_RAW);
        $oAddress->oxaddress__oxfname = new Field($oMollieSessionAddress->givenName);
        $oAddress->oxaddress__oxlname = new Field($oMollieSessionAddress->familyName);

        $aBillingStreetSplitInfo = UserHelper::getInstance()->splitStreet([$oMollieSessionAddress->streetAndNumber]);
        $oAddress->oxaddress__oxstreet = new Field($aBillingStreetSplitInfo['street']);
        $oAddress->oxaddress__oxstreetnr = new Field($aBillingStreetSplitInfo['number']);
        $oAddress->oxaddress__oxaddinfo = new Field($aBillingStreetSplitInfo['addinfo']);
        $oAddress->oxaddress__oxcity = new Field($oMollieSessionAddress->city);
        $oAddress->oxaddress__oxzip = new Field($oMollieSessionAddress->postalCode);
        $oAddress->oxaddress__oxcountry = $oUser->getUserCountry();
        $oAddress->oxaddress__oxcountryid = new Field(oxNew(Country::class)->getIdByCode($oMollieSessionAddress->country));
        $oAddress->oxaddress__oxsal = new Field(UserHelper::getInstance()->getSalByFirstname($oMollieSessionAddress->givenName));
        $oAddress->save();

        // saving delivery Address for later use
        Registry::getSession()->setVariable('deladrid', $oAddress->getId());
    }

    /**
     * Check if address already exists for the user
     *
     * @param  object $oUser
     * @param  object $oMollieSessionAddress
     * @return string
     */
    protected function getExistingAddressId($oUser, $oMollieSessionAddress)
    {
        $sQuery = "SELECT 
                       oxid 
                   FROM 
                       oxaddress 
                   WHERE 
                       OXUSERID = :userid AND
                       OXFNAME = :fname AND
                       OXLNAME = :lname AND
                       OXSTREET = :street AND
                       OXSTREETNR = :streetnr AND
                       OXCITY = :city AND
                       OXZIP = :zip
                   LIMIT 1";

        $aBillingStreetSplitInfo = UserHelper::getInstance()->splitStreet([$oMollieSessionAddress->streetAndNumber]);
        return \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne($sQuery, [
            ':userid' => $oUser->getId(),
            ':fname' => $oMollieSessionAddress->givenName,
            ':lname' => $oMollieSessionAddress->familyName,
            ':street' => $aBillingStreetSplitInfo['street'],
            ':streetnr' => $aBillingStreetSplitInfo['number'],
            ':city' => $oMollieSessionAddress->city,
            ':zip' => $oMollieSessionAddress->postalCode,
        ]);
    }

    /**
     * Checks request if shipping contact email is given and returns it.
     * Returns false otherwise
     *
     * @return string|false
     */
    protected function getApplePayEmailAddress()
    {
        $aShippingContact = Registry::getRequest()->getRequestEscapedParameter('shippingContact');
        if (!empty($aShippingContact) && !empty($aShippingContact['emailAddress'])) {
            return $aShippingContact['emailAddress'];
        }
        return false;
    }

    /**
     * Tries to find the user in thedatabase
     *
     * @param  string $sApplePayEmail
     * @return string|false
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    protected function getUserIdByEmail($sApplePayEmail)
    {
        $sQuery = "SELECT oxid FROM oxuser WHERE oxusername = ? LIMIT 1";
        return \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne($sQuery, array($sApplePayEmail));
    }

    /**
     * Tries to gather the user id by email and loads the user
     *
     * @param  string $sApplePayEmail
     * @return \OxidEsales\Eshop\Application\Model\User|false
     */
    protected function getExistingUser($sApplePayEmail)
    {
        $sUserId = $this->getUserIdByEmail($sApplePayEmail);
        if (!empty($sUserId)) {
            $oUser = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
            if ($oUser->load($sUserId)) {
                return $oUser;
            }
        }
        return false;
    }

    /**
     * Returns user for apple pay payment
     * 1. Trys to use currently active shop user
     * 2. Trys to load existing user by ApplePay email
     * 3. Creates dummy user by given ApplePay information
     *
     * @param  bool $blIsDeliveryMethodCall
     * @return \OxidEsales\Eshop\Application\Model\User
     */
    public function getApplePayUser($blIsDeliveryMethodCall = false)
    {
        $oUser = Registry::getConfig()->getActiveView()->getUser();
        if (!$oUser) {
            $sApplePayEmail = $this->getApplePayEmailAddress();
            if ($sApplePayEmail !== false) {
                $oUser = $this->getExistingUser($sApplePayEmail);
            }
        }

        if ($oUser === false) {
            if (!$oUser || Registry::getRequest()->getRequestEscapedParameter('countryCode')) {
                $oUser = $this->getApplePayDummyUser();
            }
        } else {
            if ($blIsDeliveryMethodCall === true) {
                $this->setInitialDeliveryData(
                    $oUser,
                    Registry::getRequest()->getRequestEscapedParameter('countryCode'),
                    Registry::getRequest()->getRequestEscapedParameter('city'),
                    Registry::getRequest()->getRequestEscapedParameter('zip')
                );
            }
            $this->updateUserFromApplePayData($oUser);
        }
        return $oUser;
    }

    /**
     * Generates a user or loads an existing user from Mollie session shipping address
     *
     * @param  \stdClass $oMollieSessionAddress
     * @return false|\OxidEsales\Eshop\Application\Model\User
     */
    public function getMollieSessionUser($oMollieSessionAddress)
    {
        $oUser = Registry::getConfig()->getActiveView()->getUser();
        if (!$oUser && !empty($oMollieSessionAddress->email)) {
            $oUser = $this->getExistingUser($oMollieSessionAddress->email);
        }

        if ($oUser === false) { // no user logged in, no user existing in shop
            $oUser = $this->getMollieSessionDummyUser($oMollieSessionAddress);
        } else { // user logged in or existing in shop
            $this->updateUserFromMollieSessionData($oUser, $oMollieSessionAddress);
            $this->setShippingAddressFromMollieSession($oUser, $oMollieSessionAddress);
        }
        return $oUser;
    }

    /**
     * Creates Mollie API user and adds customerId to user model
     *
     * @param  object $oUser
     * @return void
     */
    public function createMollieUser(&$oUser)
    {
        $oResponse = Payment::getInstance()->loadMollieApi()->customers->create([
            'name' => $oUser->oxuser__oxfname->value.' '.$oUser->oxuser__oxlname->value,
            'email' => $oUser->oxuser__oxusername->value,
        ]);

        if ($oResponse && !empty($oResponse->id)) {
            $oUser->oxuser__molliecustomerid = new Field($oResponse->id);
            $oUser->save();
        }
    }
}
