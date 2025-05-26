<?php

namespace Mollie\Payment\Application\Model\Cronjob;

use Mollie\Payment\Application\Helper\Logger;
use Mollie\Payment\Application\Model\Cronjob;
use OxidEsales\Eshop\Core\Registry;

class Base
{
    const LOGLEVEL_STANDARD = "standard";
    const LOGLEVEL_EXTENDED = "extended";
    const LOGLEVEL_NONE = "none";

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
    protected static $sLogFileName = 'MollieCronjobLog.log';

    /**
     * Logfile name
     *
     * @var string
     */
    protected static $sErrorLogFileName = 'MollieCronjobErrors.log';

    /**
     * Data from cronjob table
     *
     * @var array
     */
    protected $aDbData = null;

    /**
     * ShopId used for cronjob, false means no shopId restriction
     *
     * @var int|false
     */
    protected $iShopId = false;

    /**
     * Base constructor.
     *
     * @param int|false $iShopId
     * @return void
     */
    public function __construct($iShopId = false)
    {
        $this->iShopId = $iShopId;

        $oCronjob = Cronjob::getInstance();
        if ($this->getCronjobId() !== null && $oCronjob->isCronjobAlreadyExisting($this->getCronjobId(), $this->getShopId()) === false) {
            $oCronjob->addNewCronjob($this->getCronjobId(), $this->getDefaultMinuteInterval(), $this->getShopId());
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
        $this->aDbData = Cronjob::getInstance()->getCronjobData($this->getCronjobId(), $this->getShopId());
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
     * Returns shop id set by cronjob call
     *
     * @return int|false
     */
    public function getShopId()
    {
        return $this->iShopId;
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
     * @return string
     */
    public static function getConfiguredLogLevel()
    {
        $sLogLevel = Registry::getConfig()->getShopConfVar('sMollieCronjobLogLevel');
        if (empty($sLogLevel)) {
            return self::LOGLEVEL_STANDARD; // default
        }
        return $sLogLevel;
    }

    /**
     * Determines if message has to be logged
     *
     * @param  string $sMessageLogLevel
     * @return bool
     */
    public static function hasMessageToBeLogged($sMessageLogLevel = self::LOGLEVEL_STANDARD)
    {
        $sConfiguredLogLevel = self::getConfiguredLogLevel();
        if ($sConfiguredLogLevel === self::LOGLEVEL_NONE) {
            return false;
        }

        if ($sConfiguredLogLevel === self::LOGLEVEL_STANDARD && $sMessageLogLevel === self::LOGLEVEL_EXTENDED) {
            return false;
        }

        return true; // remaining cases are 'configured level and message level are standard' or 'configured level is extended' - log in both cases
    }

    /**
     * Log info message if configured log level is matching the messages log level
     *
     * @param  string $sMessage
     * @param  string $sMessageLogLevel
     * @return void
     */
    public static function logInfo($sMessage, $sMessageLogLevel = self::LOGLEVEL_STANDARD)
    {
        if (self::hasMessageToBeLogged($sMessageLogLevel) === false) {
            return;
        }

        Logger::logMessage($sMessage, getShopBasePath().'/log/'.self::$sLogFileName);
    }

    /**
     * Echoes given information
     *
     * @param  string $sMessage
     * @return void
     */
    public static function outputInfo($sMessage, $sMessageLogLevel = self::LOGLEVEL_STANDARD)
    {
        echo date('Y-m-d H:i:s - ').$sMessage."\n";

        self::logInfo($sMessage, $sMessageLogLevel);
    }

    /**
     * @param string $sMessage
     * @param string $sOrderId
     * @return string
     */
    protected static function prependOrderId($sMessage, $sOrderId)
    {
        if ($sOrderId !== false) {
            $sMessage = "Order ID ".$sOrderId." - ".$sMessage;
        }
        return $sMessage;
    }

    /**
     * @param string $sMessage
     * @param string $sOrderId
     * @return void
     */
    public static function outputStandardInfo($sMessage, $sOrderId = false)
    {
        self::outputInfo(self::prependOrderId($sMessage, $sOrderId), self::LOGLEVEL_STANDARD);
    }

    /**
     * @param string $sMessage
     * @param string $sOrderId
     * @return void
     */
    public static function outputExtendedInfo($sMessage, $sOrderId = false)
    {
        self::outputInfo(self::prependOrderId($sMessage, $sOrderId), self::LOGLEVEL_EXTENDED);
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
        Cronjob::getInstance()->markCronjobAsFinished($this->getCronjobId(), $this->getShopId());
        if ($blResult === false) {
            Logger::logMessage('Cron "'.$this->getCronjobId().'" failed'.($sError !== false ? " (Error: ".$sError.")" : ""), getShopBasePath().'/log/'.self::$sErrorLogFileName);
        }
    }

    /**
     * Starts cronjob
     *
     * @return bool
     */
    public function startCronjob()
    {
        self::outputInfo("Start cronjob '".$this->getCronjobId()."'");

        $sError = false;
        $blResult = false;
        try {
            $blResult = $this->handleCronjob();
        } catch (\Exception $exc) {
            $sError = $exc->getMessage();
        }
        $this->finishCronjob($blResult, $sError);

        self::outputInfo("Finished cronjob '".$this->getCronjobId()."' - Status: ".($blResult === false ? 'NOT' : '')." successful");
        if ($sError !== false) {
            self::outputInfo("Error-Message: ".$sError);
        }

        return $blResult;
    }
}
