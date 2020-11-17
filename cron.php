<?php

require_once dirname(__FILE__) . "/../../../bootstrap.php";

$oScheduler = oxNew(\Mollie\Payment\Application\Model\Cronjob\Scheduler::class);
$oScheduler->start();
