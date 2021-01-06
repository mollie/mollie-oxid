<?php

namespace Mollie\Payment\Application\Model\Payment;

use OxidEsales\Eshop\Application\Model\Order;

class Banktransfer extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'molliebanktransfer';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'banktransfer';

    /**
     * Determines custom config template if existing, otherwise false
     *
     * @var string|bool
     */
    protected $sCustomConfigTemplate = 'mollie_config_banktransfer.tpl';

    /**
     * Generate due date
     *
     * @return string
     */
    protected function getDueDate()
    {
        $iDueDays = $this->getConfigParam('due_days');
        if (is_numeric($iDueDays)) {
            return date('Y-m-d', time() + (60 * 60 * 24 * $iDueDays));
        }
        return '';
    }

    /**
     * Return parameters specific to the given payment type, if existing
     *
     * @param Order $oOrder
     * @return array
     */
    public function getPaymentSpecificParameters(Order $oOrder)
    {
        $aParams = [
            'billingEmail' => $oOrder->oxorder__oxbillemail->value,
            'dueDate' => $this->getDueDate(),
        ];
        if ($this->getApiMethod() == 'order') {
            $aParams = []; // existance of billingEmail param in OrderAPI triggers error
        }
        return $aParams;
    }
}
