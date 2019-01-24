<?php

namespace Mollie\Payment\Application\Model\Request;

use Mollie\Payment\Application\Helper\Payment as PaymentHelper;
use OxidEsales\Eshop\Application\Model\Order as CoreOrder;

class Payment extends Base
{
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

        $this->addParameter('description', 'OrderNr: '.$oOrder->oxorder__oxordernr->value);
    }
}
