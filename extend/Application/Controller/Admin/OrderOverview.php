<?php

namespace Mollie\Payment\extend\Application\Controller\Admin;

use Mollie\Payment\Application\Helper\Payment;
use OxidEsales\Eshop\Core\Registry;
use Mollie\Payment\Application\Model\PaymentConfig;

class OrderOverview extends OrderOverview_parent
{
    /**
     * Sends order.
     */
    public function sendorder()
    {
        parent::sendorder();

        $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        if ($oOrder->load($this->getEditObjectId()) && $oOrder->mollieIsMolliePaymentUsed()) {
            $oOrder->mollieMarkOrderAsShipped();
        }
    }
}
