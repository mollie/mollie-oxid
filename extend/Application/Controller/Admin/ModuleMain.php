<?php

namespace Mollie\Payment\extend\Application\Controller\Admin;

use Mollie\Payment\Application\Helper\Database;
use Mollie\Payment\Application\Helper\Payment;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;

class ModuleMain extends ModuleMain_parent
{
    protected $_sMollieNewestVersion = null;

    /**
     * Collects currently newest release version number from github
     *
     * @return string|false
     */
    public function mollieGetNewestReleaseVersion()
    {
        if ($this->_sMollieNewestVersion === null) {
            $this->_sMollieNewestVersion = false;

            $sComposerJson = file_get_contents("https://raw.githubusercontent.com/mollie/mollie-oxid/master/composer.json");
            if (!empty($sComposerJson)) {
                $aComposerJson = json_decode($sComposerJson, true);
                if (!empty($aComposerJson['version'])) {
                    $this->_sMollieNewestVersion = $aComposerJson['version'];
                }
            }
        }
        return $this->_sMollieNewestVersion;
    }

    /**
     * Returns current version of mollie module
     *
     * @return string|false
     */
    public function mollieGetUsedVersionNumber()
    {
        $sModuleId = $this->mollieGetCurrentModuleId();
        if ($sModuleId) {
            $oModule = oxNew(\OxidEsales\Eshop\Core\Module\Module::class);
            if ($oModule->load($sModuleId)) {
                return $oModule->getInfo('version');
            }
        }
        return false;
    }

    /**
     * Checks if old version warning has to be shown
     *
     * @return bool
     */
    public function mollieShowOldVersionWarning()
    {
        $sNewestVersion = $this->mollieGetNewestReleaseVersion();
        // $sNewestVersion = false means that version could not be retrieved correctly, so we cant determine if version is old or not, so no output
        if ($sNewestVersion !== false && version_compare($sNewestVersion, $this->mollieGetUsedVersionNumber(), '>')) {
            return true;
        }
        return false;
    }

    /**
     * Returns currently loaded module id
     *
     * @return string
     */
    protected function mollieGetCurrentModuleId()
    {
        if (\OxidEsales\Eshop\Core\Registry::getConfig()->getRequestParameter("moduleId")) {
            $sModuleId = \OxidEsales\Eshop\Core\Registry::getConfig()->getRequestParameter("moduleId");
        } else {
            $sModuleId = $this->getEditObjectId();
        }
        return $sModuleId;
    }

    /**
     * Executes parent method parent::render(),
     * passes data to Smarty engine and returns name of template file "module_main.tpl".
     *
     * Extension: Return Mollie template if Mollie module was detected
     *
     * @return string
     */
    public function render()
    {
        $sReturn = parent::render();

        if ($this->mollieGetCurrentModuleId() == "molliepayment") {
            // Return Mollie template
            return "mollie_module_main.tpl";
        }

        return $sReturn;
    }
}
