<?php

namespace Mollie\Payment\extend\Application\Controller\Admin;

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
}
