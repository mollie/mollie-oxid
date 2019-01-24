<?php

namespace Mollie\Payment\extend\Core;

use OxidEsales\Eshop\Core\Registry;

class ViewConfig extends ViewConfig_parent
{
    /**
     * Returns if the show icons option was enabled in admin
     *
     * @return bool
     */
    public function mollieShowIcons()
    {
        return (bool)Registry::getConfig()->getShopConfVar('blMollieShowIcons');
    }
}
