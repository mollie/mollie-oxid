<?php

namespace Mollie\Payment\extend\Application\Model;

use Mollie\Payment\Application\Helper\Payment as PaymentHelper;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Field;

class Order extends Order_parent
{
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
}
