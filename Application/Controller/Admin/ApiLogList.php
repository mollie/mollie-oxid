<?php

namespace Mollie\Payment\Application\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminListController;

class ApiLogList extends AdminListController
{
    /**
     * Current class template name
     * @var string
     */
    protected $_sThisTemplate = 'mollie_apilog_list.tpl';

    /**
     * Name of chosen object class (default null).
     *
     * @var string
     */
    protected $_sListClass = ''; //ManuTest TODO ModelClass bei Bedarf integrieren

    public function render() {
        parent::render();

        return $this->_sThisTemplate;
    }

}