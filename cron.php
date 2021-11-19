<?php

require_once dirname(__FILE__) . "/../../../bootstrap.php";

function canRunCronjob() {
    if(empty($_SERVER['REMOTE_ADDR']) && !isset($_SERVER['HTTP_USER_AGENT']) && count($_SERVER['argv']) > 0) {
        // is called by php cli
        return true;
    }

    $sSecureKey = \OxidEsales\Eshop\Core\Registry::getRequest()->getRequestEscapedParameter("secureKey");
    if (!empty($sSecureKey) && $sSecureKey == \OxidEsales\Eshop\Core\Registry::getConfig()->getShopConfVar('sMollieCronSecureKey')) {
        // is called via webserver and secureKey param is given and matches configured secure key
        return true;
    }

    return false;
}

if (canRunCronjob() === false) {
    die('Permission denied');
}

$iShopId = false;
if (isset($argv[1]) && is_numeric($argv[1])) {
    $iShopId = $argv[1];
}

$oScheduler = oxNew(\Mollie\Payment\Application\Model\Cronjob\Scheduler::class);
$oScheduler->start($iShopId);
