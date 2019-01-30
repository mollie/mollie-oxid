<?php

namespace Mollie\Payment\extend\Application\Model;

use Mollie\Payment\Application\Helper\Payment as PaymentHelper;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;

class Order extends Order_parent
{
    /**
     * Toggles certain behaviours in finalizeOrder for when the customer returns after the payment
     *
     * @var bool
     */
    protected $blMollieFinalizeReturnMode = false;

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
     * Generate Mollie payment model from paymentId
     *
     * @return \Mollie\Payment\Application\Model\Payment\Base
     */
    public function mollieGetPaymentModel()
    {
        return PaymentHelper::getInstance()->getMolliePaymentModel($this->oxorder__oxpaymenttype->value);
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
            return parent::_setPayment($sPaymentid);
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
        if (PaymentHelper::getInstance()->isMolliePaymentMethod(Registry::getSession()->getBasket()->getPaymentId()) === false || $this->blMollieFinalizeReturnMode === true) {
            return parent::_setFolder();
        }

        $this->oxorder__oxfolder = new Field(Registry::getConfig()->getShopConfVar('sMollieStatusPending'), Field::T_RAW);
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
}
