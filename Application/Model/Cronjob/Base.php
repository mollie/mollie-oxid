<?php

namespace Mollie\Payment\Application\Model\Cronjob;

use Mollie\Payment\Application\Model\Cronjob;
use OxidEsales\Eshop\Core\Registry;

abstract class Base
{
    /**
     * Id of current cronjob
     *
     * @var string
     */
    protected $sCronjobId = null;

    /**
     * Default cronjob interval in minutes
     *
     * @var int
     */
    protected $iDefaultMinuteInterval = null;

    /**
     * Logfile name
     *
     * @var string
     */
    protected $sLogFileName = 'MollieCronjobErrors.log';

    /**
     * Data from cronjob table
     *
     * @var array
     */
    protected $aDbData = null;

    /**
     * Base constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $oCronjob = Cronjob::getInstance();
        if ($oCronjob->isCronjobAlreadyExisting($this->getCronjobId()) === false) {
            $oCronjob->addNewCronjob($this->getCronjobId(), $this->getDefaultMinuteInterval());
        }
        $this->loadDbData();
    }

    /**
     * Adds data of cronjob to property
     *
     * @return void
     */
    protected function loadDbData()
    {
        $this->aDbData = Cronjob::getInstance()->getCronjobData($this->getCronjobId());
    }

    /**
     * Return cronjob id
     *
     * @return string
     */
    public function getCronjobId()
    {
        return $this->sCronjobId;
    }

    /**
     * Return default interval in minutes
     *
     * @return int
     */
    public function getDefaultMinuteInterval()
    {
        return $this->iDefaultMinuteInterval;
    }

    /**
     * Returns datetime of last run of the cronjob
     *
     * @return string
     */
    public function getLastRunDateTime()
    {
        return $this->aDbData['LAST_RUN'];
    }

    /**
     * Returns configured minute interval for cronjob
     *
     * @return int
     */
    public function getMinuteInterval()
    {
        return $this->aDbData['MINUTE_INTERVAL'];
    }

    /**
     * Converts cronjob id to activity conf var name
     *
     * @return string
     */
    protected function getActivityConfVarName()
    {
        $sConfVarName = $this->getCronjobId();
        $sConfVarName = str_ireplace('mollie_', 'mollie_cron_', $sConfVarName);
        $sConfVarName = str_replace('_', ' ', $sConfVarName);
        $sConfVarName = ucwords(($sConfVarName));
        $sConfVarName = str_replace(' ', '', $sConfVarName);
        $sConfVarName = 's'.$sConfVarName.'Active';
        return $sConfVarName;
    }

    /**
     * Checks if cronjob is activated in config
     *
     * @return bool
     */
    public function isCronjobActivated()
    {
        if ((bool)Registry::getConfig()->getShopConfVar($this->getActivityConfVarName()) === true) {
            return true;
        }
        return false;
    }

    /**
     * Main method for cronjobs
     * Hook to be overloaded by child classes
     * Return true if successful
     * Return false if not successful
     *
     * @return bool
     */
    protected function handleCronjob()
    {
        return false;
    }

    /**
     * Finished cronjob
     *
     * @param bool         $blResult
     * @param string|false $sError
     * @return void
     */
    protected function finishCronjob($blResult, $sError = false)
    {
        Cronjob::getInstance()->markCronjobAsFinished($this->getCronjobId());
        if ($blResult === false) {
            error_log(date('Y-m-d H:i:s - ').'Cron "'.$this->getCronjobId().'" failed'.($sError !== false ? " (Error: ".$sError.")" : "")."\n", 3, getShopBasePath().'/log/'.$this->sLogFileName);
        }
    }

    /**
     * Starts cronjob
     *
     * @return bool
     */
    public function startCronjob()
    {
        echo "\nStart cronjob '".$this->getCronjobId()."'\n";

        $sError = false;
        $blResult = false;
        try {
            $blResult = $this->handleCronjob();
        } catch (\Exception $exc) {
            $sError = $exc->getMessage();
        }
        $this->finishCronjob($blResult, $sError);

        echo "Finished cronjob '".$this->getCronjobId()."' - Status: ".($blResult === false ? 'NOT' : '')." successful\n";
        if ($sError !== false) {
            echo "Error-Message: ".$sError."\n";
        }

        return $blResult;
    }
}