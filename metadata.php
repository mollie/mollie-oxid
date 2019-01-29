<?php

/**
 * Metadata version
 */
$sMetadataVersion = '2.1';

/**
 * Module information
 */
$aModule = [
    'id'            => 'molliepayment',
    'title'         => [
        'de' => 'Mollie Payment',
        'en' => 'Mollie Payment'
    ],
    'description'   => [
        'de' => '',
        'en' => '',
    ],
    #'thumbnail'    => 'INSERT_PIC_HERE.png',
    'version'       => '1.0.0',
    'author'        => 'TODO!',
    'email'         => 'TODO!',
    'url'          => 'https://www.mollie.com/en/',
    'extend'        => [
        \OxidEsales\Eshop\Application\Model\PaymentGateway::class => Mollie\Payment\extend\Application\Model\PaymentGateway::class,
        \OxidEsales\Eshop\Application\Model\Order::class => Mollie\Payment\extend\Application\Model\Order::class,
        \OxidEsales\Eshop\Application\Model\Payment::class => Mollie\Payment\extend\Application\Model\Payment::class,
        \OxidEsales\Eshop\Application\Controller\Admin\ModuleConfiguration::class => Mollie\Payment\extend\Application\Controller\Admin\ModuleConfiguration::class,
        \OxidEsales\Eshop\Application\Controller\Admin\PaymentMain::class => Mollie\Payment\extend\Application\Controller\Admin\PaymentMain::class,
        \OxidEsales\Eshop\Application\Controller\PaymentController::class => Mollie\Payment\extend\Application\Controller\PaymentController::class,
        \OxidEsales\Eshop\Application\Controller\OrderController::class => Mollie\Payment\extend\Application\Controller\OrderController::class,
        \OxidEsales\Eshop\Core\ViewConfig::class => Mollie\Payment\extend\Core\ViewConfig::class,
    ],
    'files'         => [
        'MollieWebhook' => 'mollie/molliepayment/Application/Controller/MollieWebhook.php',
    ],
    'controllers'   => [
        'MollieWebhook' => Mollie\Payment\Application\Controller\MollieWebhook::class,
    ],
    'templates'     => [
        'molliewebhook.tpl' => 'mollie/molliepayment/Application/views/hook/tpl/molliewebhook.tpl',
        'mollie_config_banktransfer.tpl' => 'mollie/molliepayment/Application/views/admin/tpl/paymentconfig/mollie_config_banktransfer.tpl',
        'mollie_config_giftcard.tpl' => 'mollie/molliepayment/Application/views/admin/tpl/paymentconfig/mollie_config_giftcard.tpl',
        'mollie_config_ideal.tpl' => 'mollie/molliepayment/Application/views/admin/tpl/paymentconfig/mollie_config_ideal.tpl',
        'mollie_issuers.tpl' => 'mollie/molliepayment/Application/views/frontend/tpl/mollie_issuers.tpl',
        'mollie_issuers_dropdown.tpl' => 'mollie/molliepayment/Application/views/frontend/tpl/mollie_issuers_dropdown.tpl',
        'mollie_issuers_radio.tpl' => 'mollie/molliepayment/Application/views/frontend/tpl/mollie_issuers_radio.tpl',
        'mollie_payment_showicons.tpl' => 'mollie/molliepayment/Application/views/frontend/tpl/mollie_payment_showicons.tpl',
        'mollieideal.tpl' => 'mollie/molliepayment/Application/views/frontend/tpl/mollieideal.tpl',
        'molliegiftcard.tpl' => 'mollie/molliepayment/Application/views/frontend/tpl/molliegiftcard.tpl',
    ],
    'events'        => [
        'onActivate' => \Mollie\Payment\Core\Events::class.'::onActivate',
        'onDeactivate' => \Mollie\Payment\Core\Events::class.'::onDeactivate',
    ],
    'blocks'        => [
        ['template' => 'module_config.tpl',                     'block' => 'admin_module_config_var',   'file' => 'mollie_module_config_var.tpl'],
        ['template' => 'payment_main.tpl',                      'block' => 'admin_payment_main_form',   'file' => 'mollie_admin_payment_main_form.tpl'],
        ['template' => 'page/checkout/inc/payment_other.tpl',   'block' => 'checkout_payment_longdesc', 'file' => 'mollie_checkout_payment_longdesc.tpl'],
        ['template' => 'mollie_payment_showicons.tpl',          'block' => 'checkout_payment_longdesc', 'file' => 'mollie_checkout_payment_longdesc.tpl'],
        ['template' => 'page/checkout/payment.tpl',             'block' => 'select_payment',            'file' => 'mollie_select_payment.tpl'],
        ['template' => 'page/checkout/payment.tpl',             'block' => 'checkout_payment_errors',   'file' => 'mollie_checkout_payment_errors.tpl'],
    ],
    'settings'      => [
        ['group' => 'MOLLIE_GENERAL',   'name' => 'sMollieMode',                        'type' => 'select',     'value' => 'test',      'position' => 10, 'constrains' => 'live|test'],
        ['group' => 'MOLLIE_GENERAL',   'name' => 'sMollieTestToken',                   'type' => 'str',        'value' => '',          'position' => 20],
        ['group' => 'MOLLIE_GENERAL',   'name' => 'sMollieLiveToken',                   'type' => 'str',        'value' => '',          'position' => 30],
        ['group' => 'MOLLIE_GENERAL',   'name' => 'blMollieLogTransactionInfo',         'type' => 'bool',       'value' => '1',         'position' => 33],
        ['group' => 'MOLLIE_GENERAL',   'name' => 'blMollieRemoveDeactivatedMethods',   'type' => 'bool',       'value' => '1',         'position' => 35],
        ['group' => 'MOLLIE_GENERAL',   'name' => 'blMollieShowIcons',                  'type' => 'bool',       'value' => '1',         'position' => 40],
        ['group' => 'MOLLIE_GENERAL',   'name' => 'sMollieStatusPending',               'type' => 'select',     'value' => '',          'position' => 50],
        ['group' => 'MOLLIE_GENERAL',   'name' => 'sMollieStatusProcessing',            'type' => 'select',     'value' => '',          'position' => 60],
    ]
];
