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
     * Toggles certain behaviours in finalizeOrder for when order is being finished automatically
     * because customer did not come back to shop
     *
     * @var bool
     */
    protected $blMollieFinishOrderReturnMode = false;

    /**
     * Toggles certain behaviours in finalizeOrder for when the the payment is being reinitialized at a later point in time
     *
     * @var bool
     */
    protected $blMollieReinitializePaymentMode = false;

    /**
     * Temporary field for saving the order nr
     *
     * @var int|null
     */
    protected $mollieTmpOrderNr = null;

    /**
     * State is saved to prevent order being set to transstatus OK during recalculation
     *
     * @var bool|null
     */
    protected $mollieRecalculateOrder = null;

    /**
     * Used to trigger the _setNumber() method before the payment-process during finalizeOrder to have the order-number there already
     *
     * @return void
     */
    public function mollieSetOrderNumber()
    {
        if (!$this->oxorder__oxordernr->value) {
            $this->_setNumber();
        }
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
     * Getter for blMollieIsApplePayButtonMode property
     *
     * @return bool
     */
    public function mollieIsApplePayButtonMode()
    {
        return $this->blMollieIsApplePayButtonMode;
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
        if ($this->mollieIsMolliePaymentUsed() === false) {
            return;
        }

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

                DatabaseProvider::getDb()->Execute("UPDATE oxorder SET mollieshipmenthasbeenmarked = 1 WHERE oxid = ?", array($this->getId()));
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
        if (!empty($this->oxorder__oxpaid->value) && $this->oxorder__oxpaid->value != "0000-00-00 00:00:00") {
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
     * Mark order as paid
     *
     * @return void
     */
    public function mollieMarkAsSecondChanceMailSent()
    {
        $sDate = date('Y-m-d H:i:s');

        $sQuery = "UPDATE oxorder SET molliesecondchancemailsent = ? WHERE oxid = ?";
        DatabaseProvider::getDb()->Execute($sQuery, array($sDate, $this->getId()));

        $this->oxorder__molliesecondchancemailsent = new Field($sDate);
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
        DatabaseProvider::getDb()->execute('UPDATE oxorder SET oxtransid = ? WHERE oxid = ?', array($sTransactionId, $this->getId()));

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
        if ($this->blMollieFinalizeReturnMode === false && $this->blMollieReinitializePaymentMode === false) {
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

        if ($this->blMollieReinitializePaymentMode === true) {
            // Finalize order would set a new incremented order-nr if already filled
            // Doing this to prevent this, oxordernr will be filled again in _setNumber
            $this->mollieTmpOrderNr = $this->oxorder__oxordernr->value;
            $this->oxorder__oxordernr->value = "";
        }
        return true;
    }

    /**
     * Tries to fetch and set next record number in DB. Returns true on success
     *
     * @return bool
     */
    protected function _setNumber()
    {
        if ($this->blMollieFinalizeReturnMode === false && $this->blMollieReinitializePaymentMode === false && $this->mollieTmpOrderNr === null) {
            return parent::_setNumber();
        }

        $this->oxorder__oxordernr->value = $this->mollieTmpOrderNr;

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

        if ($this->blMollieFinalizeReturnMode === false && $this->blMollieFinishOrderReturnMode === false) { // Mollie module has it's own folder management, so order should not be set to status NEW by oxid core
            $this->oxorder__oxfolder = new Field(Registry::getConfig()->getShopConfVar('sMollieStatusPending'), Field::T_RAW);
        }
    }

    /**
     * Extension: Changing the order in the backend results in da finalizeOrder call with recaltulateOrder = true
     * This sets oxtransstatus to OK, which should not happen for Mollie orders when they were not finished
     * This prevents this behaviour
     *
     * @param string $sStatus order transaction status
     */
    protected function _setOrderStatus($sStatus)
    {
        if ($this->mollieRecalculateOrder === true && $this->oxorder__oxtransstatus->value == "NOT_FINISHED" && $this->mollieIsMolliePaymentUsed()) {
            return;
        }
        parent::_setOrderStatus($sStatus);
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
     * Validates order parameters like stock, delivery and payment
     * parameters
     *
     * @param \OxidEsales\Eshop\Application\Model\Basket $oBasket basket object
     * @param \OxidEsales\Eshop\Application\Model\User   $oUser   order user
     *
     * @return null
     */
    public function validateOrder($oBasket, $oUser)
    {
        if ($this->blMollieFinishOrderReturnMode === false) {
            return parent::validateOrder($oBasket, $oUser);
        }
    }

    /**
     * Checks if payment used for current order is available and active.
     * Throws exception if not available
     *
     * @param \OxidEsales\Eshop\Application\Model\Basket    $oBasket basket object
     * @param \OxidEsales\Eshop\Application\Model\User|null $oUser   user object
     *
     * @return null
     */
    public function validatePayment($oBasket, $oUser = null)
    {
        if ($this->blMollieReinitializePaymentMode === false) {
            $oReflection = new \ReflectionMethod(\OxidEsales\Eshop\Application\Model\Order::class, 'validatePayment');
            $aParams = $oReflection->getParameters();
            if (count($aParams) == 1) {
                return parent::validatePayment($oBasket); // Oxid 6.1 didnt have the $oUser parameter yet
            }
            return parent::validatePayment($oBasket, $oUser);
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
        $this->mollieRecalculateOrder = $blRecalculatingOrder;
        if (PaymentHelper::getInstance()->isMolliePaymentMethod($oBasket->getPaymentId()) === true && $this->mollieIsReturnAfterPayment() === true) {
            $this->blMollieFinalizeReturnMode = true;
        }
        if (Registry::getSession()->getVariable('mollieReinitializePaymentMode')) {
            $this->blMollieReinitializePaymentMode = true;
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
        if ($oUser->oxuser__oxusername->value) { // in case of user already being logged in
            $this->oxorder__oxbillemail = new Field($oUser->oxuser__oxusername->value);
        }
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
        if ($this->blMollieIsApplePayButtonMode === false && $this->blMollieReinitializePaymentMode === false) {
            return parent::validateDeliveryAddress($oUser);
        }
        return 0;
    }

    /**
     * Performs order cancel process
     */
    public function cancelOrder()
    {
        parent::cancelOrder();
        if ($this->mollieIsMolliePaymentUsed() === true) {
            $sCancelledFolder = Registry::getConfig()->getShopConfVar('sMollieStatusCancelled');
            if (!empty($sCancelledFolder)) {
                $this->mollieSetFolder($sCancelledFolder);
            }

            $oApiEndpoint = $this->mollieGetPaymentModel()->getApiEndpoint();
            $oMollieApiOrder = $oApiEndpoint->get($this->oxorder__oxtransid->value);
            if ($oMollieApiOrder->isCancelable) {
                $oApiEndpoint->cancel($this->oxorder__oxtransid->value);
            }
        }
    }

    /**
     * Returns finish payment url
     *
     * @return string|bool
     */
    public function mollieGetPaymentFinishUrl()
    {
        return Registry::getConfig()->getSslShopUrl()."?cl=mollieFinishPayment&id=".$this->getId();
    }

    /**
     * Checks if Mollie order was not finished correctly
     *
     * @return bool
     */
    public function mollieIsOrderInUnfinishedState()
    {
        if ($this->oxorder__oxtransstatus->value == "NOT_FINISHED" && $this->oxorder__oxfolder->value == Registry::getConfig()->getShopConfVar('sMollieStatusProcessing')) {
            return true;
        }
        return false;
    }

    /**
     * Recreates basket from order information
     *
     * @return object
     */
    public function mollieRecreateBasket()
    {
        $oBasket = $this->_getOrderBasket();

        // add this order articles to virtual basket and recalculates basket
        $this->_addOrderArticlesToBasket($oBasket, $this->getOrderArticles(true));

        // recalculating basket
        $oBasket->calculateBasket(true);

        Registry::getSession()->setVariable('sess_challenge', $this->getId());
        Registry::getSession()->setVariable('paymentid', $this->oxorder__oxpaymenttype->value);
        Registry::getSession()->setBasket($oBasket);

        return $oBasket;
    }

    /**
     * Checks if order is elibible for finishing the payment
     *
     * @param bool $blSecondChanceEmail
     * @return bool
     */
    public function mollieIsEligibleForPaymentFinish($blSecondChanceEmail = false)
    {
        if (!$this->mollieIsMolliePaymentUsed() || $this->oxorder__oxpaid->value != '0000-00-00 00:00:00' || $this->oxorder__oxtransstatus->value != 'NOT_FINISHED') {
            return false;
        }

        $aStatus = $this->mollieGetPaymentModel()->getTransactionHandler()->processTransaction($this, 'success');

        $aStatusBlacklist = ['paid'];
        if ($blSecondChanceEmail === true) {
            $aStatusBlacklist[] = 'canceled';
        }
        if (in_array($aStatus['status'], $aStatusBlacklist)) {
            return false;
        }
        return true;
    }

    /**
     * Triggers sending Mollie second chance email
     *
     * @return void
     */
    public function mollieSendSecondChanceEmail()
    {
        $oEmail = oxNew(\OxidEsales\Eshop\Core\Email::class);
        $oEmail->mollieSendSecondChanceEmail($this, $this->mollieGetPaymentFinishUrl());

        $this->mollieMarkAsSecondChanceMailSent();
    }

    /**
     * Tries to finish an order which was paid but where the customer seemingly didnt return to the shop after payment to finish the order process
     *
     * @return integer
     */
    public function mollieFinishOrder()
    {
        $oBasket = $this->mollieRecreateBasket();

        $this->blMollieFinalizeReturnMode = true;
        $this->blMollieFinishOrderReturnMode = true;

        //finalizing order (skipping payment execution, vouchers marking and mail sending)
        return $this->finalizeOrder($oBasket, $this->getOrderUser());
    }

    /**
     * Starts a new payment with Mollie
     *
     * @return integer
     */
    public function mollieReinitializePayment()
    {
        if ($this->oxorder__oxstorno->value == 1) {
            $this->mollieUncancelOrder();
        }

        $oBasket = $this->mollieRecreateBasket();
        $oUser = $this->getUser();
        if (!$oUser) {
            $oUser = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
            $oUser->load($this->oxorder__oxuserid->value);
            $this->setUser($oUser);
            Registry::getSession()->setVariable('usr', $this->oxorder__oxuserid->value);
        }

        $this->blMollieReinitializePaymentMode = true;

        \OxidEsales\Eshop\Core\Registry::getSession()->setVariable('mollieReinitializePaymentMode', true);

        return $this->finalizeOrder($oBasket, $oUser);
    }

    /**
     * Retrieves order id connected to given transaction id and trys to load it
     * Returns if order was found and loading was a success
     *
     * @param string $sTransactionId
     * @return bool
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function mollieLoadOrderByTransactionId($sTransactionId)
    {
        $sQuery = "SELECT oxid FROM oxorder WHERE oxtransid = ?";

        $sOrderId = DatabaseProvider::getDb()->getOne($sQuery, array($sTransactionId));
        if (!empty($sOrderId)) {
            return $this->load($sOrderId);
        }
        return false;
    }

    /**
     * Returns Mollie payment transactionId for given order regardless of Payment-API or Order-API usage
     *
     * @return string|bool
     */
    public function mollieGetPaymentTransactionId()
    {
        if (stripos($this->oxorder__oxtransid->value, 'tr_') !== false) { // tr_ means it is already a transactionId
            return $this->oxorder__oxtransid->value;
        }

        $oApiEndpoint = $this->mollieGetPaymentModel()->getApiEndpoint();
        $oMollieApiOrder = $oApiEndpoint->get($this->oxorder__oxtransid->value, ["embed" => "payments"]);
        if ($oMollieApiOrder instanceof \Mollie\Api\Resources\Order && !empty($oMollieApiOrder->_embedded) && !empty($oMollieApiOrder->_embedded->payments)) {
            $oPayment = array_shift($oMollieApiOrder->_embedded->payments);
            return $oPayment->id;
        }
        return false;
    }
}
