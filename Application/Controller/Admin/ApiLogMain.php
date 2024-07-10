<?php

namespace Mollie\Payment\Application\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use Mollie\Payment\Application\Model\RequestLog;

class ApiLogMain extends AdminDetailsController
{
    /**
     * Current class template name
     *
     * @var string
     */
    protected $_sThisTemplate = 'mollie_apilog_main.tpl';

    /**
     * @var RequestLog
     */
    protected $_oRequestLog = null;

    /**
     * Parameter for $this->getObjectData($sData) for EditObject
     */
    protected const EDIT = 'edit';

    /**
     * Parameter for $this->getObjectData($sData) for ResponseData
     */
    protected const RESPONSE = 'response';

    /**
     * Parameter for $this->getObjectData($sData) for RequestData
     */
    protected const REQUEST = 'request';

    /**
     * @return string
     */
    public function render()
    {
        $sOxid = $this->getEditObjectId();
        if ($sOxid != '-1') {
            $this->_oRequestLog = oxNew(RequestLog::class);
            $this->_oRequestLog->load($sOxid);
        }

        return parent::render();
    }

    /**
     * Getter for TPL
     *
     * @return string $this->_oRequestLog->decodeData($this->_oRequestLog->mollierequestlog__request->rawValue)
     * @return false
     */
    public function getRequest()
    {
        return $this->getObjectData(self::REQUEST);
    }

    /**
     * Getter for TPL
     *
     * @return string $this->_oRequestLog->decodeData($this->_oRequestLog->mollierequestlog__response->rawValuel)
     * @return false
     */
    public function getResponse()
    {
        return $this->getObjectData(self::RESPONSE);
    }

    /**
     * Getter for TPL
     *
     * @return RequestLog $this->_oRequestLog
     * @return false
     */
    public function getEdit()
    {
        return $this->getObjectData(self::EDIT);
    }

    /**
     * Function for the TPL-Getter
     *
     * @param $sData
     * @return false
     */
    private function getObjectData($sData)
    {
        if ($this->_oRequestLog) {
            if ($sData == 'edit') {
                return $this->_oRequestLog;
            } elseif ($sData == 'request') {
                return $this->_oRequestLog->decodeData($this->_oRequestLog->mollierequestlog__request->rawValue);
            } elseif ($sData == 'response') {
                return $this->_oRequestLog->decodeData($this->_oRequestLog->mollierequestlog__response->rawValue);
            }
        }
        return false;
    }
}