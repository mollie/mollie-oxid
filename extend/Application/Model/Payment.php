<?php

namespace Mollie\Payment\extend\Application\Model;

use Mollie\Payment\Application\Helper\Payment as PaymentHelper;

class Payment extends Payment_parent
{
    /**
     * Check if given payment method is a Mollie method
     *
     * @return bool
     */
    public function isMolliePaymentMethod()
    {
        return PaymentHelper::getInstance()->isMolliePaymentMethod($this->getId());
    }

    /**
     * Return Mollie payment model
     *
     * @return \Mollie\Payment\Application\Model\Payment\Base
     */
    public function getMolliePaymentModel()
    {
        if ($this->isMolliePaymentMethod()) {
            return PaymentHelper::getInstance()->getMolliePaymentModel($this->getId());
        }
        return null;
    }
}
