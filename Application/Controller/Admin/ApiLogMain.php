<?php

namespace Mollie\Payment\Application\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use Mollie\Payment\Application\Model\RequestLog;

class ApiLogMain extends AdminDetailsController
{
    /**
     * Current class template name
     * @var string
     */
    protected $_sThisTemplate = 'mollie_apilog_main.tpl';

    /**
     * @return string
     */
    public function render()
    {
        $sOxId = $this->getEditObjectId();

        $oRequestLog = oxNew(RequestLog::class);
        $oRequestLog->load($sOxId);
        $this->_aViewData['edit'] = $oRequestLog;

        return parent::render();
    }
}