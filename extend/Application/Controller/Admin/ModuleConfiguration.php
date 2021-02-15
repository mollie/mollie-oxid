<?php

namespace Mollie\Payment\extend\Application\Controller\Admin;

use Mollie\Payment\Application\Helper\Database;
use Mollie\Payment\Application\Helper\Payment;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;

class ModuleConfiguration extends ModuleConfiguration_parent
{
    /**
     * Flag if request had a upload error
     *
     * @var bool
     */
    protected $blMollieUploadError = false;

    /**
     * Property for custom upload error
     *
     * @var string|bool
     */
    protected $sMollieCustomUploadError = false;

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
     * Returns array with options for iMollieCronSecondChanceTimeDiff config option
     *
     * @return array
     */
    public function mollieSecondChanceDayDiffs()
    {
        $aReturn = [];
        for ($i = 1; $i <= 14; $i++) {
            $aReturn[] = $i;
        }
        return $aReturn;
    }

    /**
     * Returns if request had a upload error
     *
     * @return bool
     */
    public function mollieHasUploadError()
    {
        return $this->blMollieUploadError;
    }

    /**
     * Returns upload error
     *
     * @return bool|string
     */
    public function mollieGetUploadError()
    {
        $sErrorLangSnippet = "MOLLIE_ALTLOGO_ERROR";
        if ($this->sMollieCustomUploadError !== false) {
            $sErrorLangSnippet = $this->sMollieCustomUploadError;
        }
        return Registry::getLang()->translateString($sErrorLangSnippet);
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

    /**
     * Returns array of all Mollie payment methods
     *
     * @return array
     */
    public function molliePaymentMethods()
    {
        return Payment::getInstance()->getMolliePaymentMethods();
    }

    /**
     * Returns alt logo value
     *
     * @param  string $sAltLogoConfVar
     * @return string
     */
    public function mollieGetConfiguredAltLogoValue($sAltLogoConfVar)
    {
        return Registry::getConfig()->getShopConfVar($sAltLogoConfVar, null, $this->_getModuleForConfigVars());
    }

    /**
     * Clean file name so that processFile routine doesnt throw an exception
     *
     * @param  string $sConfVar
     * @return void
     */
    protected function mollieCleanUploadFileName($sConfVar)
    {
        $_FILES[$sConfVar]['name'] = preg_replace('/[^\-_a-z0-9\.]/i', '', $_FILES[$sConfVar]['name']);
    }

    /**
     * Handle file upload for all payment methods
     *
     * @return bool
     */
    protected function mollieHandleFileUploads()
    {
        if (!empty($_FILES)) {
            foreach ($_FILES as $sConfVar => $aFileInfo) {
                if (!empty($aFileInfo['name']) && $aFileInfo['error'] == 0) {
                    try {
                        $this->mollieCleanUploadFileName($sConfVar);
                        $sReturn = \OxidEsales\Eshop\Core\Registry::getUtilsFile()->processFile($sConfVar, 'modules/mollie/molliepayment/out/img');
                    } catch(\Exception $exc) {
                        $this->sMollieCustomUploadError = $exc->getMessage();
                        $sReturn = false;
                    }
                    if ($sReturn === false) {
                        return false; // Upload error?
                    }

                    Registry::getConfig()->saveShopConfVar('str', $sConfVar, $sReturn, null, $this->_getModuleForConfigVars());
                }
            }
        }
        return true;
    }

    /**
     * Saves shop configuration variables
     */
    public function saveConfVars()
    {
        parent::saveConfVars();
        $sModuleId = $this->_getModuleForConfigVars();
        if ($sModuleId == "module:molliepayment") {
            $blReturn = $this->mollieHandleFileUploads();
            if ($blReturn === false) {
                $this->blMollieUploadError = true;
            }
        }
    }

    /**
     * Deletes oxconfig entry for given alt logo conf var
     *
     * @return void
     */
    public function deleteMollieAltLogo()
    {
        $sDeleteConfVar = Registry::getRequest()->getRequestEscapedParameter('mollieDeleteAltLogo');

        Database::getInstance()->deleteMollieAltLogo($sDeleteConfVar);
    }
}
