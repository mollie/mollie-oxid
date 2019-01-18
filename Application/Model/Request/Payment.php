<?php

namespace Mollie\Payment\Application\Model\Request;

use Mollie\Payment\Application\Helper\Payment as PaymentHelper;
use OxidEsales\Eshop\Application\Model\Order as CoreOrder;

class Payment extends Base
{
    /**
     * Returns order API endpoint
     *
     * @return \Mollie\Api\Endpoints\EndpointAbstract
     */
    protected function getApiEndpoint()
    {
        return PaymentHelper::getInstance()->loadMollieApi()->payments;
    }

    /**
     * Add needed parameters to the API request
     *
     * @param CoreOrder $oOrder
     * @param double $dAmount
     * @return void
     */
    protected function addRequestParameters(CoreOrder $oOrder, $dAmount)
    {
        parent::addRequestParameters($oOrder, $dAmount);

        $this->addParameter('description', 'OrderNr: '.$oOrder->oxorder__oxordernr->value);
        $this->addParameter('issuer', ''); // what is this field for?
    }
}