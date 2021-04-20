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
        'de' => 'Steigern Sie Ihren Umsatz mit einer stressfreien Zahlungslösung. Mollie bietet alle relevanten Zahlungsarten,<br>
einen optimierten Checkout und ein einfaches Onboarding. Ihr Nutzen? Ganz einfach: höhere Conversions, <br>
zufriedene Käufer und eine stressfreie Anbindung in Ihren OXID eShop. Online-Zahlungen müssen<br>
nicht kompliziert sein!<br><br>
Mit der integrierten Lösung von Mollie erhalten Sie Zugang zu allen wichtigen Zahlungsarten wie Klarna, Kreditkarten, <br>
SEPA Lastschrift, Apple Pay & mehr. Eine höhere Conversion erzielen Sie durch ein verbessertes Checkout-Erlebnis: <br>
markengerecht und in vertrauter Umgebung, ohne Weiterleitung.<br>
Das Onboarding ist einfach und schnell, mit dem Dashboard und der Mollie-App haben Sie alle Angaben zum Wachstum<br>
immer zur Hand. Die Auszahlungsfrequenz bestimmen Sie selbst. Mit Mollie kommen keine monatlichen Kosten oder<br>
Einrichtungsgebühren auf Sie zu. Sie zahlen nur pro Transaktion. Sie bleiben Kunde, weil Sie zufrieden sind. 
',
        'en' => "Boost your conversion with a hassle-free payment solution. Mollie offers all popular payment methods,<br>
plus optimized checkout and super easy onboarding. How does it benefit you? The answer is simple: higher<br>
conversion rates, satisfied customers and a simple integration into your OXID webshop. Online payments don't <br>
have to be complicated.<br><br>
With an integrated solution from Mollie, you gain access to all the most important payment methods, including Klarna,<br>
credit cards, SEPA direct debit, Apple Pay and more. You’ll boost your conversion with an improved checkout experience—all<br>
inside a familiar, branded environment, with no redirecting. <br>
The onboarding process is quick and easy, and the Mollie dashboard and app let you keep track of all your transactions at<br>
a glance. You can choose for yourself how often you want to receive your payouts. With Mollie, you’ll never pay any monthly<br>
fees or setup costs. You’ll only pay for successful transactions. And you’ll stay because you're happy with our service,<br>
not because a contract forces you! 
",
    ],
    'thumbnail'    => 'mollie_logo.png',
    'version'       => '1.0.22',
    'author'        => 'Fatchip GmbH',
    'email'         => 'info@mollie.com',
    'url'          => 'https://www.mollie.com/',
    'extend'        => [
        \OxidEsales\Eshop\Application\Model\PaymentGateway::class => Mollie\Payment\extend\Application\Model\PaymentGateway::class,
        \OxidEsales\Eshop\Application\Model\Order::class => Mollie\Payment\extend\Application\Model\Order::class,
        \OxidEsales\Eshop\Application\Model\OrderArticle::class => Mollie\Payment\extend\Application\Model\OrderArticle::class,
        \OxidEsales\Eshop\Application\Model\Payment::class => Mollie\Payment\extend\Application\Model\Payment::class,
        \OxidEsales\Eshop\Application\Model\User::class => Mollie\Payment\extend\Application\Model\User::class,
        \OxidEsales\Eshop\Application\Controller\Admin\ModuleConfiguration::class => Mollie\Payment\extend\Application\Controller\Admin\ModuleConfiguration::class,
        \OxidEsales\Eshop\Application\Controller\Admin\PaymentMain::class => Mollie\Payment\extend\Application\Controller\Admin\PaymentMain::class,
        \OxidEsales\Eshop\Application\Controller\Admin\OrderMain::class => Mollie\Payment\extend\Application\Controller\Admin\OrderMain::class,
        \OxidEsales\Eshop\Application\Controller\Admin\OrderOverview::class => Mollie\Payment\extend\Application\Controller\Admin\OrderOverview::class,
        \OxidEsales\Eshop\Application\Controller\PaymentController::class => Mollie\Payment\extend\Application\Controller\PaymentController::class,
        \OxidEsales\Eshop\Application\Controller\OrderController::class => Mollie\Payment\extend\Application\Controller\OrderController::class,
        \OxidEsales\Eshop\Core\ViewConfig::class => Mollie\Payment\extend\Core\ViewConfig::class,
        \OxidEsales\Eshop\Core\Email::class => Mollie\Payment\extend\Core\Email::class,
        \OxidEsales\Eshop\Core\Session::class => Mollie\Payment\extend\Core\Session::class,
    ],
    'controllers'   => [
        'MollieWebhook' => Mollie\Payment\Application\Controller\MollieWebhook::class,
        'MollieApplePay' => Mollie\Payment\Application\Controller\MollieApplePay::class,
        'MollieFinishPayment' => Mollie\Payment\Application\Controller\MollieFinishPayment::class,
        'mollie_order_refund' => Mollie\Payment\Application\Controller\Admin\OrderRefund::class,
    ],
    'templates'     => [
        'molliewebhook.tpl' => 'mollie/molliepayment/Application/views/hook/tpl/molliewebhook.tpl',
        'mollie_config_banktransfer.tpl' => 'mollie/molliepayment/Application/views/admin/tpl/paymentconfig/mollie_config_banktransfer.tpl',
        'mollie_config_giftcard.tpl' => 'mollie/molliepayment/Application/views/admin/tpl/paymentconfig/mollie_config_giftcard.tpl',
        'mollie_config_ideal.tpl' => 'mollie/molliepayment/Application/views/admin/tpl/paymentconfig/mollie_config_ideal.tpl',
        'mollie_config_creditcard.tpl' => 'mollie/molliepayment/Application/views/admin/tpl/paymentconfig/mollie_config_creditcard.tpl',
        'mollie_config_applepay.tpl' => 'mollie/molliepayment/Application/views/admin/tpl/paymentconfig/mollie_config_applepay.tpl',
        'mollie_issuers.tpl' => 'mollie/molliepayment/Application/views/frontend/tpl/mollie_issuers.tpl',
        'mollie_issuers_dropdown.tpl' => 'mollie/molliepayment/Application/views/frontend/tpl/mollie_issuers_dropdown.tpl',
        'mollie_issuers_radio.tpl' => 'mollie/molliepayment/Application/views/frontend/tpl/mollie_issuers_radio.tpl',
        'mollie_payment_showicons.tpl' => 'mollie/molliepayment/Application/views/frontend/tpl/mollie_payment_showicons.tpl',
        'mollieideal.tpl' => 'mollie/molliepayment/Application/views/frontend/tpl/mollieideal.tpl',
        'molliegiftcard.tpl' => 'mollie/molliepayment/Application/views/frontend/tpl/molliegiftcard.tpl',
        'mollieapplepay.tpl' => 'mollie/molliepayment/Application/views/frontend/tpl/mollieapplepay.tpl',
        'molliecreditcard.tpl' => 'mollie/molliepayment/Application/views/frontend/tpl/molliecreditcard.tpl',
        'mollieapplepaybutton.tpl' => 'mollie/molliepayment/Application/views/frontend/tpl/mollieapplepaybutton.tpl',
        'mollie_order_refund.tpl' => 'mollie/molliepayment/Application/views/admin/tpl/mollie_order_refund.tpl',
        'mollie_second_chance.tpl' => 'mollie/molliepayment/Application/views/email/tpl/mollie_second_chance.tpl',
    ],
    'events'        => [
        'onActivate' => \Mollie\Payment\Core\Events::class.'::onActivate',
        'onDeactivate' => \Mollie\Payment\Core\Events::class.'::onDeactivate',
    ],
    'blocks'        => [
        ['template' => 'module_config.tpl',                     'block' => 'admin_module_config_var',       'file' => 'mollie_module_config_var.tpl'],
        ['template' => 'payment_main.tpl',                      'block' => 'admin_payment_main_form',       'file' => 'mollie_admin_payment_main_form.tpl'],
        ['template' => 'page/checkout/inc/payment_other.tpl',   'block' => 'checkout_payment_longdesc',     'file' => 'mollie_checkout_payment_longdesc.tpl'],
        ['template' => 'mollie_payment_showicons.tpl',          'block' => 'checkout_payment_longdesc',     'file' => 'mollie_checkout_payment_longdesc.tpl'],
        ['template' => 'page/checkout/payment.tpl',             'block' => 'select_payment',                'file' => 'mollie_select_payment.tpl'],
        ['template' => 'page/checkout/payment.tpl',             'block' => 'checkout_payment_errors',       'file' => 'mollie_checkout_payment_errors.tpl'],
        ['template' => 'page/details/inc/productmain.tpl',      'block' => 'details_productmain_tobasket',  'file' => 'mollie_details_productmain_tobasket.tpl'],
        ['template' => 'page/checkout/basket.tpl',              'block' => 'basket_btn_next_top',           'file' => 'mollie_basket_btn_next_top.tpl'],
        ['template' => 'page/checkout/basket.tpl',              'block' => 'basket_btn_next_bottom',        'file' => 'mollie_basket_btn_next_bottom.tpl'],
    ],
    'settings'      => [
        ['group' => 'MOLLIE_GENERAL',           'name' => 'sMollieMode',                        'type' => 'select',     'value' => 'test',      'position' => 10, 'constraints' => 'live|test'],
        ['group' => 'MOLLIE_GENERAL',           'name' => 'sMollieTestToken',                   'type' => 'str',        'value' => '',          'position' => 20],
        ['group' => 'MOLLIE_GENERAL',           'name' => 'sMollieLiveToken',                   'type' => 'str',        'value' => '',          'position' => 30],
        ['group' => 'MOLLIE_GENERAL',           'name' => 'blMollieLogTransactionInfo',         'type' => 'bool',       'value' => '1',         'position' => 33],
        ['group' => 'MOLLIE_GENERAL',           'name' => 'blMollieRemoveDeactivatedMethods',   'type' => 'bool',       'value' => '1',         'position' => 35],
        ['group' => 'MOLLIE_GENERAL',           'name' => 'blMollieShowIcons',                  'type' => 'bool',       'value' => '1',         'position' => 40],
        ['group' => 'MOLLIE_STATUS_MAPPING',    'name' => 'sMollieStatusPending',               'type' => 'select',     'value' => '',          'position' => 50],
        ['group' => 'MOLLIE_STATUS_MAPPING',    'name' => 'sMollieStatusProcessing',            'type' => 'select',     'value' => '',          'position' => 60],
        ['group' => 'MOLLIE_STATUS_MAPPING',    'name' => 'sMollieStatusCancelled',             'type' => 'select',     'value' => '',          'position' => 70],
        ['group' => 'MOLLIE_CRONJOBS',          'name' => 'sMollieCronFinishOrdersActive',      'type' => 'bool',       'value' => '0',         'position' => 80],
        ['group' => 'MOLLIE_CRONJOBS',          'name' => 'sMollieCronOrderExpiryActive',       'type' => 'bool',       'value' => '0',         'position' => 85],
        ['group' => 'MOLLIE_CRONJOBS',          'name' => 'sMollieCronSecondChanceActive',      'type' => 'bool',       'value' => '0',         'position' => 90],
        ['group' => 'MOLLIE_CRONJOBS',          'name' => 'iMollieCronSecondChanceTimeDiff',    'type' => 'select',     'value' => '1',         'position' => 100],
        ['group' => 'MOLLIE_CRONJOBS',          'name' => 'sMollieCronOrderShipmentActive',     'type' => 'bool',       'value' => '0',         'position' => 110],
        ['group' => 'MOLLIE_APPLEPAY',          'name' => 'blMollieApplePayButtonOnBasket',     'type' => 'bool',       'value' => '1',         'position' => 200],
        ['group' => 'MOLLIE_APPLEPAY',          'name' => 'blMollieApplePayButtonOnDetails',    'type' => 'bool',       'value' => '1',         'position' => 210],
        ['group' => 'MOLLIE_PAYMENTLOGOS',      'name' => 'sMolliePaymentLogosPlaceholder',     'type' => 'str',        'value' => '',          'position' => 500],
    ]
];
