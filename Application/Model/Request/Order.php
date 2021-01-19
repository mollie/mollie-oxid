<?php

namespace Mollie\Payment\Application\Model\Request;

use OxidEsales\Eshop\Application\Model\Order as CoreOrder;

class Order extends Base
{
    /**
     * Determines if the extended address is needed in the params
     *
     * @var bool
     */
    protected $blNeedsExtendedAddress = true;

    /**
     * Add needed parameters to the API request
     *
     * @param CoreOrder $oOrder
     * @param double $dAmount
     * @param string $sReturnUrl
     * @return void
     */
    protected function addRequestParameters(CoreOrder $oOrder, $dAmount, $sReturnUrl)
    {
        parent::addRequestParameters($oOrder, $dAmount, $sReturnUrl);

        $this->addParameter('orderNumber', (string)$oOrder->oxorder__oxordernr->value);
        $this->addParameter('lines', $this->getBasketItems($oOrder));

        $oUser = $oOrder->getUser();
        if ($oUser && $oUser->oxuser__oxbirthday->value != '0000-00-00') {
            $this->addParameter('consumerDateOfBirth', $oUser->oxuser__oxbirthday->value);
        }
    }
}
