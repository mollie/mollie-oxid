<?php

namespace Mollie\Payment\extend\Application\Controller\Admin;

use Mollie\Payment\Application\Helper\Payment;
use OxidEsales\Eshop\Core\Registry;

class ModuleConfiguration extends ModuleConfiguration_parent
{
    /**
     * Return order status array
     *
     * @return array
     */
    public function mollieGetOrderFolders()
    {
        return Registry::getConfig()->getConfigParam('aOrderfolder');
    }

    /**
     * Check if test- or api-key is configured
     *
     * @return bool
     */
    public function mollieHasApiKeys()
    {
        if (!empty(Registry::getConfig()->getShopConfVar('sMollieLiveToken'))) {
            return true;
        }
        if (!empty(Registry::getConfig()->getShopConfVar('sMollieTestToken'))) {
            return true;
        }
        return false;
    }

    /**
     * Check if connection can be established for the api key
     *
     * @param  string $sConfVar
     * @return bool
     */
    public function mollieIsApiKeyUsable($sConfVar)
    {
        return Payment::getInstance()->isConnectionWithTokenSuccessful($sConfVar);
    }
}
