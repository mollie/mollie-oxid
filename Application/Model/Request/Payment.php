<?php

namespace Mollie\Payment\Application\Model\Request;

use Mollie\Payment\Application\Helper\Payment as PaymentHelper;
use OxidEsales\Eshop\Application\Model\Order as CoreOrder;
use OxidEsales\Eshop\Core\Registry;

class Payment extends Base
{
    /**
     * Default text for payment description
     *
     * @var string
     */
    protected $sDefaultDescriptionTest = 'OrderNr: {orderNumber}';

    /**
     * Returns description text with variables being replaced with appropriate values
     *
     * @param  CoreOrder $oOrder
     * @return string
     */
    protected function getFilledDescriptionText($oOrder)
    {
        $oPaymentModel = $oOrder->mollieGetPaymentModel();

        $sDescriptionText = $oPaymentModel->getConfigParam('payment_description');
        if (empty($sDescriptionText)) {
            $sDescriptionText = $this->sDefaultDescriptionTest;
        }

        $aSubstitutionArray = [
            '{orderId}' => $oOrder->getId(),
            '{orderNumber}' => $oOrder->oxorder__oxordernr->value,
            '{storeName}' => Registry::getConfig()->getActiveShop()->oxshops__oxname->value,
            '{customer.firstname}' => $oOrder->oxorder__oxbillfname->value,
            '{customer.lastname}' => $oOrder->oxorder__oxbilllname->value,
            '{customer.company}' => $oOrder->oxorder__oxbillcompany->value,
        ];

        return str_replace(array_keys($aSubstitutionArray), array_values($aSubstitutionArray), $sDescriptionText);
    }

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

        $this->addParameter('description', $this->getFilledDescriptionText($oOrder));
    }
}
