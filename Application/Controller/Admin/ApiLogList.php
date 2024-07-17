<?php

namespace Mollie\Payment\Application\Controller\Admin;

use Mollie\Payment\Application\Model\RequestLog;
use OxidEsales\Eshop\Application\Controller\Admin\AdminListController;

class ApiLogList extends AdminListController
{
    /**
     * Name of chosen object class (default null).
     * @var string
     */
    protected $_sListClass = RequestLog::class;

    /**
     * Enable/disable sorting by DESC (SQL) (default false - disable).
     * @var bool
     */
    protected $_blDesc = true;

    /**
     * Default SQL sorting parameter (default null).
     * @var string
     */
    protected $_sDefSortField = "timestamp";

    /**
     * Current class template name
     * @var string
     */
    protected $_sThisTemplate = 'mollie_apilog_list.tpl';
}