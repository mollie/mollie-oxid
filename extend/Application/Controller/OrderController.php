<?php

namespace Mollie\Payment\extend\Application\Controller;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;
use Mollie\Payment\Application\Helper\Order as OrderHelper;
use Mollie\Payment\Application\Helper\PaymentFinishLink;
use Mollie\Payment\extend\Application\Model\Order as MollieOrder;

class OrderController extends OrderController_parent
{
    /**
     * Delete sess_challenge from session to trigger the creation of a new order when needed
     */
    public function render()
    {
        $sSessChallenge = Registry::getSession()->getVariable('sess_challenge');
        $blMollieIsRedirected = Registry::getSession()->getVariable('mollieIsRedirected');
        if (!empty($sSessChallenge) && $blMollieIsRedirected === true) {
            OrderHelper::getInstance()->cancelCurrentOrder();
        }
        Registry::getSession()->deleteVariable('mollieIsRedirected');
        Registry::getSession()->deleteVariable('mollieRedirectUrl');
        Registry::getSession()->deleteVariable(PaymentFinishLink::SESSION_VAR_REINIT_ORDER_ID);
        return parent::render();
    }

    /**
     * Load previously created order
     *
     * @return Order|false
     */
    protected function mollieGetOrder()
    {
        $sOrderId = Registry::getSession()->getVariable('sess_challenge');
        if (!empty($sOrderId)) {
            $oOrder = oxNew(Order::class);
            $oOrder->load($sOrderId);
            if ($oOrder->isLoaded() === true) {
                return $oOrder;
            }
        }
        return false;
    }

    /**
     * Mollie sometimes sends multiple calls to the shop when payment was done with Apple Pay in credit card form.
     * This function recognizes these situations to prevent double processing
     *
     * @param  Order $oOrder
     * @return bool
     */
    protected function mollieIsCreditCardApplePayDoubleCall(Order $oOrder)
    {
        if ($oOrder->getFieldData("oxorder__oxpaymenttype") == "molliecreditcard" &&
            $oOrder->getFieldData("oxorder__oxtransstatus") == "OK"
        ) {
            return true;
        }
        return false;
    }

    /**
     * Writes error-status to session and redirects to payment page
     *
     * @param string $sErrorLangIdent
     * @return false
     */
    protected function redirectWithError($sErrorLangIdent)
    {
        Registry::getSession()->setVariable('payerror', -50);
        Registry::getSession()->setVariable('payerrortext', Registry::getLang()->translateString($sErrorLangIdent));
        Registry::getUtils()->redirect(Registry::getConfig()->getCurrentShopUrl().'index.php?cl=payment');
        return false; // execution ends with redirect - return used for unit tests
    }

    /**
     * When the payment was restarted via the payment finish link (MollieFinishPayment) the user of the order is used for this request.
     * The user is only taken from the order which was restarted in this session (see Order::mollieReinitializePayment)
     *
     * @return void
     */
    protected function mollieSetOrderUserForReinitializedPayment()
    {
        if (Registry::getRequest()->getRequestEscapedParameter(oxNew(Order::class)::MOLLIE_PAYMENT_REINIT_PARAM) != '1') {
            return;
        }

        $sReinitOrderId = Registry::getSession()->getVariable(PaymentFinishLink::SESSION_VAR_REINIT_ORDER_ID);
        if (empty($sReinitOrderId)) {
            return;
        }

        $oOrder = $this->mollieGetOrder();
        if ($oOrder !== false && $oOrder->getId() === $sReinitOrderId) {
            $this->setUser($oOrder->getOrderUser());
        }
    }

    /**
     *
     * @return string
     */
    public function handleMollieReturn()
    {
        $this->mollieSetOrderUserForReinitializedPayment();

        $oPayment = $this->getPayment();
        if ($oPayment && $oPayment->isMolliePaymentMethod()) {
            Registry::getSession()->deleteVariable('mollieIsRedirected');
            Registry::getSession()->deleteVariable('mollieRedirectUrl');

            $oOrder = $this->mollieGetOrder();
            if (!$oOrder) {
                return $this->redirectWithError('MOLLIE_ERROR_ORDER_NOT_FOUND');
            }

            $sTransactionId = $oOrder->oxorder__oxtransid->value;
            if (empty($sTransactionId)) {
                return $this->redirectWithError('MOLLIE_ERROR_TRANSACTIONID_NOT_FOUND');
            }

            $aResult = $oOrder->mollieGetPaymentModel()->getTransactionHandler($oOrder)->processTransaction($oOrder, 'success');

            if ($aResult['success'] === false) {
                Registry::getSession()->deleteVariable('sess_challenge');

                $sErrorIdent = 'MOLLIE_ERROR_SOMETHING_WENT_WRONG';
                if ($aResult['status'] == 'canceled') {
                    $sErrorIdent = 'MOLLIE_ERROR_ORDER_CANCELED';
                } elseif ($aResult['status'] == 'failed') {
                    $sErrorIdent = 'MOLLIE_ERROR_ORDER_FAILED';
                } elseif ($aResult['status'] == 'open' || $aResult['status'] == 'created') {
                    $sErrorIdent = 'MOLLIE_ERROR_ORDER_NOT_COMPLETED';
                }
                return $this->redirectWithError($sErrorIdent);
            }

            if ($this->mollieIsCreditCardApplePayDoubleCall($oOrder) === true) {
                // Order already finalized, return OK to prevent double processing
                return $this->_getNextStep(Order::ORDER_STATE_OK);
            }

            // else - continue to parent::execute since success must be true
        }
        return parent::execute();
    }
}
