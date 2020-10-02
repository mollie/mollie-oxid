<?php

namespace Mollie\Payment\extend\Application\Model;

use Mollie\Payment\Application\Helper\Payment as PaymentHelper;
use Mollie\Payment\Application\Model\RequestLog;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Application\Model\Country;
use Mollie\Payment\Application\Helper\User as UserHelper;

class Order extends Order_parent
{
    /**
     * Toggles certain behaviours in finalizeOrder for when the customer returns after the payment
     *
     * @var bool
     */
    protected $blMollieFinalizeReturnMode = false;

    /**
     * Toggles certain behaviours in finalizeOrder for when user ordered with apple pay button
     *
     * @var bool
     */
    protected $blMollieIsApplePayButtonMode = false;

    /**
     * Used to trigger the _setNumber() method before the payment-process during finalizeOrder to have the order-number there already
     *
     * @return void
     */
    public function mollieSetOrderNumber()
    {
        $this->_setNumber();
    }

    /**
     * Setter for apple pay button mode
     *
     * @param  bool $blActive
     * @return void
     */
    public function mollieSetApplePayButtonMode($blActive)
    {
        $this->blMollieIsApplePayButtonMode = $blActive;
    }

    /**
     * Generate Mollie payment model from paymentId
     *
     * @return \Mollie\Payment\Application\Model\Payment\Base
     */
    public function mollieGetPaymentModel()
    {
        return PaymentHelper::getInstance()->getMolliePaymentModel($this->oxorder__oxpaymenttype->value);
    }

    /**
     * Getter for blMollieIsApplePayButtonMode property
     *
     * @return bool
     */
    public function mollieIsApplePayButtonMode()
    {
        return $this->blMollieIsApplePayButtonMode;
    }

    /**
     * Returns if order was payed with a Mollie payment type
     *
     * @return bool
     */
    public function mollieIsMolliePaymentUsed()
    {
        if(PaymentHelper::getInstance()->isMolliePaymentMethod($this->oxorder__oxpaymenttype->value)) {
            return true;
        }
        return false;
    }

    /**
     * Marks order as shipped in Mollie API
     *
     * @return void
     */
    public function mollieMarkOrderAsShipped()
    {
        $oRequestLog = oxNew(RequestLog::class);

        try {
            $oApiEndpoint = $this->mollieGetPaymentModel()->getApiEndpoint();
            $oMollieApiOrder = $oApiEndpoint->get($this->oxorder__oxtransid->value);
            if ($oMollieApiOrder instanceof \Mollie\Api\Resources\Order) {
                $aOptions = [];
                if ($this->oxorder__oxtrackcode->value != '') {
                    $aOptions['tracking'] = ['carrier' => 'N/A', 'code' => $this->oxorder__oxtrackcode->value];
                }
                $oResponse = $oMollieApiOrder->shipAll($aOptions);
                $oRequestLog->logRequest([], $oResponse, $this->getId(), $this->getConfig()->getShopId());
            }
        } catch (\Exception $exc) {
            $oRequestLog->logExceptionResponse([], $exc->getCode(), $exc->getMessage(), 'shipAll', $this->getId(), $this->getConfig()->getShopId());
        }
    }

    /**
     * Update tracking code of shipping entity
     *
     * @param  string $sTrackingCode
     * @return void
     */
    public function mollieUpdateShippingTrackingCode($sTrackingCode)
    {
        try {
            $oApiEndpoint = $this->mollieGetPaymentModel()->getApiEndpoint();
            $oMollieApiOrder = $oApiEndpoint->get($this->oxorder__oxtransid->value);
            if ($oMollieApiOrder instanceof \Mollie\Api\Resources\Order) {
                $oResponse = $oMollieApiOrder->shipments();
                if (count($oResponse) > 0) {
                    $oResponse[0]->tracking = ['carrier' => 'N/A', 'code' => $sTrackingCode];
                    $oResponse[0]->update();
                }
            }
        } catch (\Exception $exc) {
            $oRequestLog = oxNew(RequestLog::class);
            $oRequestLog->logExceptionResponse([], $exc->getCode(), $exc->getMessage(), 'updateTracking', $this->getId(), $this->getConfig()->getShopId());
        }
    }

    /**
     * Remove cancellation of the order
     *
     * @return void
     */
    public function mollieUncancelOrder()
    {
        if ($this->oxorder__oxstorno->value == 1) {
            $this->oxorder__oxstorno = new \OxidEsales\Eshop\Core\Field(0);
            if ($this->save()) {
                // canceling ordered products
                foreach ($this->getOrderArticles() as $oOrderArticle) {
                    $oOrderArticle->mollieUncancelOrderArticle();
                }
            }
        }
    }

    /**
     * Returns if the order is marked as paid, since OXID doesnt have a proper flag
     *
     * @return bool
     */
    public function mollieIsPaid()
    {
        if ($this->oxorder__oxpaid->value != "0000-00-00 00:00:00") {
            return true;
        }
        return false;
    }

    /**
     * Mark order as paid
     *
     * @return void
     */
    public function mollieMarkAsPaid()
    {
        $sDate = date('Y-m-d H:i:s');

        $sQuery = "UPDATE oxorder SET oxpaid = ? WHERE oxid = ?";
        DatabaseProvider::getDb()->Execute($sQuery, array($sDate, $this->getId()));

        $this->oxorder__oxpaid = new Field($sDate);
    }

    /**
     * Set order folder
     *
     * @param string $sFolder
     * @return void
     */
    public function mollieSetFolder($sFolder)
    {
        $sQuery = "UPDATE oxorder SET oxfolder = ? WHERE oxid = ?";
        DatabaseProvider::getDb()->Execute($sQuery, array($sFolder, $this->getId()));

        $this->oxorder__oxfolder = new Field($sFolder);
    }

    /**
     * Save transaction id in order object
     *
     * @param  string $sTransactionId
     * @return void
     */
    public function mollieSetTransactionId($sTransactionId)
    {
        $oDb = DatabaseProvider::getDb();
        $oDb->execute('UPDATE oxorder SET oxtransid = '.$oDb->quote($sTransactionId).' WHERE oxid = '.$oDb->quote($this->getId()));

        $this->oxorder__oxtransid = new Field($sTransactionId);
    }

    /**
     * Determines if the current call is a return from a redirect payment
     *
     * @return bool
     */
    protected function mollieIsReturnAfterPayment()
    {
        if (Registry::getRequest()->getRequestEscapedParameter('fnc') == 'handleMollieReturn') {
            return true;
        }
        return false;
    }

    /**
     * Extension: Return false in return mode
     *
     * @param string $sOxId order ID
     * @return bool
     */
    protected function _checkOrderExist($sOxId = null)
    {
        if ($this->blMollieFinalizeReturnMode === false) {
            return parent::_checkOrderExist($sOxId);
        }
        return false; // In finalize return situation the order will already exist, but thats ok
    }

    /**
     * Extension: In return mode load order from DB instead of generation from basket because it already exists
     *
     * @param \OxidEsales\EshopCommunity\Application\Model\Basket $oBasket Shopping basket object
     */
    protected function _loadFromBasket(\OxidEsales\Eshop\Application\Model\Basket $oBasket)
    {
        if ($this->blMollieFinalizeReturnMode === false) {
            return parent::_loadFromBasket($oBasket);
        }
        $this->load(Registry::getSession()->getVariable('sess_challenge'));
    }

    /**
     * Extension: In return mode load existing userpayment instead of creating a new one
     *
     * @param string $sPaymentid used payment id
     * @return \OxidEsales\Eshop\Application\Model\UserPayment
     */
    protected function _setPayment($sPaymentid)
    {
        if ($this->blMollieFinalizeReturnMode === false) {
            $mParentReturn = parent::_setPayment($sPaymentid);

            if ($this->mollieIsMolliePaymentUsed()) {
                $this->oxorder__molliemode = new Field(PaymentHelper::getInstance()->getMollieMode());
                $this->oxorder__mollieapi = new Field($this->mollieGetPaymentModel()->getApiMethod());
            }
            return $mParentReturn;
        }
        $oUserpayment = oxNew(\OxidEsales\Eshop\Application\Model\UserPayment::class);
        $oUserpayment->load($this->oxorder__oxpaymentid->value);
        return $oUserpayment;
    }

    /**
     * Extension: Return true in return mode since this was done in the first step
     *
     * @param \OxidEsales\EshopCommunity\Application\Model\Basket $oBasket      basket object
     * @param object                                              $oUserpayment user payment object
     * @return  integer 2 or an error code
     */
    protected function _executePayment(\OxidEsales\Eshop\Application\Model\Basket $oBasket, $oUserpayment)
    {
        if ($this->blMollieFinalizeReturnMode === false) {
            return parent::_executePayment($oBasket, $oUserpayment);
        }
        return true;
    }

    /**
     * Extension: Set pending folder for Mollie orders
     *
     * @return void
     */
    protected function _setFolder()
    {
        if (PaymentHelper::getInstance()->isMolliePaymentMethod(Registry::getSession()->getBasket()->getPaymentId()) === false) {
            return parent::_setFolder();
        }

        if ($this->blMollieFinalizeReturnMode === false) { // Mollie module has it's own folder management, so order should not be set to status NEW by oxid core
            $this->oxorder__oxfolder = new Field(Registry::getConfig()->getShopConfVar('sMollieStatusPending'), Field::T_RAW);
        }
    }

    /**
     * Extension: Order already existing because order was created before the user was redirected to mollie,
     * therefore no stock validation needed. Otherwise an exception would be thrown on return when last product in stock was bought
     *
     * @param object $oBasket basket object
     */
    public function validateStock($oBasket)
    {
        if ($this->blMollieFinalizeReturnMode === false) {
            return parent::validateStock($oBasket);
        }
    }

    /**
     * This overloaded method sets the return mode flag so that the behaviour of some methods is changed when the customer
     * returns after successful payment from Mollie
     *
     * @param \OxidEsales\Eshop\Application\Model\Basket $oBasket              Basket object
     * @param object                                     $oUser                Current User object
     * @param bool                                       $blRecalculatingOrder Order recalculation
     * @return integer
     */
    public function finalizeOrder(\OxidEsales\Eshop\Application\Model\Basket $oBasket, $oUser, $blRecalculatingOrder = false)
    {
        if (PaymentHelper::getInstance()->isMolliePaymentMethod($oBasket->getPaymentId()) === true && $this->mollieIsReturnAfterPayment() === true) {
            $this->blMollieFinalizeReturnMode = true;
        }
        return parent::finalizeOrder($oBasket, $oUser, $blRecalculatingOrder);
    }

    /**
     * Assigns to new oxorder object customer delivery and shipping info
     *
     * @param object $oUser user object
     */
    protected function _setUser($oUser)
    {
        if ($this->blMollieIsApplePayButtonMode === false) {
            return parent::_setUser($oUser);
        }

        $oRequest = Registry::getRequest();
        $aBillingContact = $oRequest->getRequestEscapedParameter('billingContact');
        $aShippingContact = $oRequest->getRequestEscapedParameter('shippingContact');

        if (empty($aBillingContact) || empty($aShippingContact)) {
            throw new \Exception('Address information is missing');
        }

        $oCountry = oxNew(Country::class);

        $this->oxorder__oxuserid = new Field($oUser->getId());

        // bill address
        $this->oxorder__oxbillemail = new Field($aShippingContact['emailAddress']);
        $this->oxorder__oxbillfname = new Field($aBillingContact['givenName']);
        $this->oxorder__oxbilllname = new Field($aBillingContact['familyName']);

        $aBillingStreetSplitInfo = UserHelper::getInstance()->splitStreet($aBillingContact['addressLines']);
        $this->oxorder__oxbillstreet = new Field($aBillingStreetSplitInfo['street']);
        $this->oxorder__oxbillstreetnr = new Field($aBillingStreetSplitInfo['number']);
        $this->oxorder__oxbilladdinfo = new Field($aBillingStreetSplitInfo['addinfo']);
        $this->oxorder__oxbillcity = new Field($aBillingContact['locality']);
        $this->oxorder__oxbillcountryid = new Field($oCountry->getIdByCode($aBillingContact['countryCode']));
        $this->oxorder__oxbillstateid = new Field(UserHelper::getInstance()->getStateFromAdministrativeArea($aBillingContact['administrativeArea']));
        $this->oxorder__oxbillzip = new Field($aBillingContact['postalCode']);
        $this->oxorder__oxbillsal = new Field(UserHelper::getInstance()->getSalByFirstname($aBillingContact['givenName']));

        $this->oxorder__oxbillcompany = new Field("");
        $this->oxorder__oxbillustid = new Field("");
        $this->oxorder__oxbillfon = new Field("");
        $this->oxorder__oxbillfax = new Field("");

        // set delivery address
        $this->oxorder__oxdelfname = new Field($aShippingContact['givenName']);
        $this->oxorder__oxdellname = new Field($aShippingContact['familyName']);

        $aShippingStreetSplitInfo = UserHelper::getInstance()->splitStreet($aShippingContact['addressLines']);
        $this->oxorder__oxdelstreet = new Field($aShippingStreetSplitInfo['street']);
        $this->oxorder__oxdelstreetnr = new Field($aShippingStreetSplitInfo['number']);
        $this->oxorder__oxdeladdinfo = new Field($aShippingStreetSplitInfo['addinfo']);
        $this->oxorder__oxdelcity = new Field($aShippingContact['locality']);
        $this->oxorder__oxdelcountryid = new Field($oCountry->getIdByCode($aShippingContact['countryCode']));
        $this->oxorder__oxdelstateid = new Field(UserHelper::getInstance()->getStateFromAdministrativeArea($aShippingContact['administrativeArea']));
        $this->oxorder__oxdelzip = new Field($aShippingContact['postalCode']);
        $this->oxorder__oxdelsal = new Field(UserHelper::getInstance()->getSalByFirstname($aShippingContact['givenName']));

        $this->oxorder__oxdelcompany = new Field("");
        $this->oxorder__oxdelfon = new Field("");
        $this->oxorder__oxdelfax = new Field("");
    }

    /**
     * Checks if delivery address (billing or shipping) was not changed during checkout
     * Throws exception if not available
     *
     * @param \OxidEsales\Eshop\Application\Model\User $oUser user object
     *
     * @return int
     */
    public function validateDeliveryAddress($oUser)
    {
        if ($this->blMollieIsApplePayButtonMode === false) {
            return parent::validateDeliveryAddress($oUser);
        }
        return 0;
    }
}
