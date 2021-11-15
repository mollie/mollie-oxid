<?php

namespace Mollie\Payment\extend\Application\Controller\Admin;

use Mollie\Payment\Application\Helper\Database;
use Mollie\Payment\Application\Helper\Payment;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;

class ModuleMain extends ModuleMain_parent
{
    protected $_sMollieNewestVersion = null;

    protected $_blMailHasBeenSent = false;

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
     * Check if mollie module is active
     *
     * @return bool
     */
    public function mollieisModuleActive()
    {
        $sModuleId = $this->mollieGetCurrentModuleId();
        if ($sModuleId) {
            $oModule = oxNew(\OxidEsales\Eshop\Core\Module\Module::class);
            if ($oModule->load($sModuleId)) {
                return $oModule->isActive();
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
     * Returns representation of log entry for log file
     *
     * @param  array $aRow
     * @return string|true
     */
    protected function getLogRepresentation($aRow)
    {
        if (!empty($aRow['REQUEST'])) {
            $aRow['REQUEST'] = json_decode($aRow['REQUEST'], true);
        }
        if (!empty($aRow['RESPONSE'])) {
            $aRow['RESPONSE'] = json_decode($aRow['RESPONSE'], true);
        }
        $sArrayPrint = print_r($aRow, true);
        $sArrayPrint .= "###########################################################################################################################################################################\n";
        return $sArrayPrint;
    }

    /**
     * Generates log file and returns its path or false if no matching log entries are in the database
     *
     * @return string|false
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    protected function createLogAttachmentFile()
    {
        $sQuery = "SELECT * FROM mollierequestlog WHERE timestamp > DATE_SUB(CURDATE(), INTERVAL 7 DAY) LIMIT 700";
        $aResult = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sQuery);
        if (!empty($aResult)) {
            $sPath = dirname(__FILE__).DIRECTORY_SEPARATOR."mollie_logfile_".time().".log";
            $oFile = fopen($sPath, "w");
            foreach ($aResult as $aRow) {
                fwrite($oFile, $this->getLogRepresentation($aRow));
            }
            fclose($oFile);
            return $sPath;
        }
        return false;
    }

    /**
     * Sends email to Mollie support
     *
     * @return void
     */
    public function mollieSendSupportEnquiry()
    {
        $aSupport = \OxidEsales\Eshop\Core\Registry::getConfig()->getRequestParameter("support");
        if (!empty($aSupport)) {
            $sMollieLogFilePath = $this->createLogAttachmentFile();

            $oEmail = oxNew(\OxidEsales\Eshop\Core\Email::class);
            $oEmail->mollieSendSupportEmail($aSupport['name'], $aSupport['email'], $aSupport['subject'], $aSupport['enquiry'], $this->mollieGetUsedVersionNumber(), $sMollieLogFilePath);

            if ($sMollieLogFilePath !== false) {
                unlink($sMollieLogFilePath);
            }

            $this->_blMailHasBeenSent = true;
        }
    }

    /**
     * Returns if support email was sent successfully
     *
     * @return bool
     */
    public function mollieMailHasBeenSent()
    {
        return $this->_blMailHasBeenSent;
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

        if ($this->mollieGetCurrentModuleId() == "molliepayment" && $this->mollieisModuleActive()) {
            // Return Mollie template
            return "mollie_module_main.tpl";
        }

        return $sReturn;
    }
}
