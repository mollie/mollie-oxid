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
        if($sOxId != '-1') {
            $oRequestLog = oxNew(RequestLog::class);
            $oRequestLog->load($sOxId);

            $this->_aViewData['edit'] = $oRequestLog;
            $this->_aViewData['request'] = $oRequestLog->decodeData($oRequestLog->mollierequestlog__request->rawValue);
            $this->_aViewData['response'] = $oRequestLog->decodeData($oRequestLog->mollierequestlog__response->rawValue);
        }

        return parent::render();
    }
}