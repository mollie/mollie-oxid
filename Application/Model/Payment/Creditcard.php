<?php

namespace Mollie\Payment\Application\Model\Payment;

use Mollie\Payment\Application\Helper\Payment;
use OxidEsales\Eshop\Application\Model\Order;

class Creditcard extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'molliecreditcard';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'creditcard';

    /**
     * Determines custom config template if existing, otherwise false
     *
     * @var string|bool
     */
    protected $sCustomConfigTemplate = 'mollie_config_creditcard.tpl';

    /**
     * Determines custom frontend template if existing, otherwise false
     *
     * @var string|bool
     */
    protected $sCustomFrontendTemplate = 'molliecreditcard.tpl';

    /**
     * Returns current Mollie profileId
     *
     * @return string
     * @throws \Mollie\Api\Exceptions\ApiException
     */
    public function getProfileId()
    {
        return Payment::getInstance()->getProfileId();
    }

    /**
     * Returns configured mollie mode
     *
     * @return string
     */
    public function getMollieMode()
    {
        return Payment::getInstance()->getMollieMode();
    }

    /**
     * Returns language locale
     *
     * @return string
     */
    public function getLocale()
    {
        return Payment::getInstance()->getLocale();
    }

    /**
     * Return parameters specific to the given payment type, if existing
     *
     * @param Order $oOrder
     * @return array
     */
    public function getPaymentSpecificParameters(Order $oOrder)
    {
        $sCCToken = $this->getDynValueParameter('mollieCCToken');
        if (!empty($sCCToken)) {
            if ($this->getApiMethod() == 'order') {
                return ['payment' => ['cardToken' => $sCCToken]];
            } else {
                return ['cardToken' => $sCCToken];
            }
        }
        return parent::getPaymentSpecificParameters($oOrder);
    }
}
