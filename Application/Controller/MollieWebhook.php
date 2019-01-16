<?php

namespace Mollie\Payment\Application\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;

class MollieWebhook extends FrontendController
{
    /**
     * @var string
     */
    protected $_sThisTemplate = 'molliewebhook.tpl';

    /**
     * The render function
     */
    public function render()
    {
        // Add functionality here

        return $this->_sThisTemplate;
    }
}
