<?php

namespace Mollie\Payment\Application\Controller\Admin;

use Mollie\Payment\Application\Model\RequestLog;
use OxidEsales\Eshop\Application\Controller\Admin\AdminListController;

class ApiLogList extends AdminListController
{
    protected $_sListClass = RequestLog::class;

    /**
     * Current class template name
     * @var string
     */
    protected $_sThisTemplate = 'mollie_apilog_list.tpl';
}