<?php

require_once dirname(__FILE__) . "/../../../bootstrap.php";

$iShopId = false;
if (isset($argv[1]) && is_numeric($argv[1])) {
    $iShopId = $argv[1];
}

$oScheduler = oxNew(\Mollie\Payment\Application\Model\Cronjob\Scheduler::class);
$oScheduler->start($iShopId);
