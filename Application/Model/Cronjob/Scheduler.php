<?php


namespace Mollie\Payment\Application\Model\Cronjob;


class Scheduler
{
    /**
     * List of all existing cronjobs
     *
     * @var array
     */
    protected $aCronjobs = [
        OrderExpiry::class,
        FinishOrders::class,
        SecondChance::class,
        OrderShipment::class,
    ];

    /**
     * Returns list of all cronjobs
     *
     * @return array
     */
    protected function getCronjobs()
    {
        return $this->aCronjobs;
    }

    /**
     * Check if cronjob is due again
     *
     * @param  Base $oCronjob
     * @return bool
     */
    protected function isCronjobDue(Base $oCronjob)
    {
        $iGracePeriod = 5; // Grace period timer to prevent cronjob not starting when crontab timer and minute invterval are exactly the same
        if (empty($oCronjob->getLastRunDateTime()) || (strtotime($oCronjob->getLastRunDateTime()) - $iGracePeriod) <= (time() - ($oCronjob->getMinuteInterval() * 60))) {
            return true;
        }
        return false;
    }

    /**
     * Starts all cronjobs
     *
     * @return void
     */
    public function start()
    {
        Base::outputInfo("START MOLLIE CRONJOB EXECUTION");

        foreach ($this->getCronjobs() as $sCronjobClass) {
            $oCronjob = oxNew($sCronjobClass);
            if ($oCronjob->isCronjobActivated() && $this->isCronjobDue($oCronjob)) {
                $oCronjob->startCronjob();
            }
        }

        Base::outputInfo("FINISHED MOLLIE CRONJOB EXECUTION");
    }
}
