<?php

namespace Mollie\Payment\Application\Model\Payment;

use OxidEsales\Eshop\Application\Model\Order;

class Giftcard extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'molliegiftcard';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'giftcard';

    /**
     * If filled, the payment method will only be shown if one of the allowed currencies is active in checkout
     *
     * @var array
     */
    protected $aAllowedCurrencies = [
        'EUR'
    ];

    /**
     * Array with country-codes the payment method is restricted to
     * If property is set to false it is available to all countries
     *
     * @var array|false
     */
    protected $aBillingCountryRestrictedTo = [
        'NL'
    ];

    /**
     * Determines custom config template if existing, otherwise false
     *
     * @var string|bool
     */
    protected $sCustomConfigTemplate = 'mollie_config_giftcard.tpl';

    /**
     * Determines custom frontend template if existing, otherwise false
     *
     * @var string|bool
     */
    protected $sCustomFrontendTemplate = 'molliegiftcard.tpl';

    /**
     * Return parameters specific to the given payment type, if existing
     *
     * @param Order $oOrder
     * @return array
     */
    public function getPaymentSpecificParameters(Order $oOrder)
    {
        $aParams = parent::getPaymentSpecificParameters($oOrder);

        $sIssuer = $this->getDynValueParameter('mollie_giftcard_issuer');
        if (!empty($sIssuer)) {
            $aParams['issuer'] = $sIssuer;
        }
        return $aParams;
    }
}
