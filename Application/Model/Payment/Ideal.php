<?php

namespace Mollie\Payment\Application\Model\Payment;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;

class Ideal extends Base
{
    /**
     * Payment id in the oxid shop
     *
     * @var string
     */
    protected $sOxidPaymentId = 'mollieideal';

    /**
     * Method code used for API request
     *
     * @var string
     */
    protected $sMolliePaymentCode = 'ideal';

    /**
     * Determines custom config template if existing, otherwise false
     *
     * @var string|bool
     */
    protected $sCustomConfigTemplate = 'mollie_config_ideal.tpl';

    /**
     * Determines custom frontend template if existing, otherwise false
     *
     * @var string|bool
     */
    protected $sCustomFrontendTemplate = 'mollieideal.tpl';

    /**
     * Return parameters specific to the given payment type, if existing
     *
     * @param Order $oOrder
     * @return array
     */
    public function getPaymentSpecificParameters(Order $oOrder)
    {
        $sIssuer = $this->getDynValueParameter('mollie_ideal_issuer');
        if (!empty($sIssuer) && $sIssuer != 'qr') {
            return ['issuer' => $sIssuer];
        }
        return parent::getPaymentSpecificParameters($oOrder);
    }

    /**
     * Gather issuer info from Mollie API
     *
     * @param array $aDynValue
     * @param string $sInputName
     * @return array
     */
    public function getIssuers($aDynValue, $sInputName)
    {
        $aReturn = parent::getIssuers($aDynValue, $sInputName);
        if ((bool)$this->getConfigParam('add_qr') === true) {
            $aReturn['qr'] = [
                'title' => Registry::getLang()->translateString('MOLLIE_QR_CODE'),
                'pic' => Registry::getConfig()->getActiveView()->getViewConfig()->getModuleUrl('molliepayment', 'out/img/qr-select.png'),
            ];
        }
        return $aReturn;
    }
}
