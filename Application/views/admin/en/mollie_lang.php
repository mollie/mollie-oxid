<?php

$sLangName = "English";
// -------------------------------
// RESOURCE IDENTITFIER = STRING
// -------------------------------
$aLang = array(
    'charset'                                           => 'UTF-8',

    /* SETTINGS */
    'SHOP_MODULE_GROUP_MOLLIE_GENERAL'                  => 'Basic configuration',
    'SHOP_MODULE_sMollieMode'                           => 'Mode',
    'SHOP_MODULE_sMollieMode_live'                      => 'Live',
    'SHOP_MODULE_sMollieMode_test'                      => 'Test',
    'SHOP_MODULE_sMollieTestToken'                      => 'Test API Key',
    'SHOP_MODULE_sMollieLiveToken'                      => 'Live API Key',
    'SHOP_MODULE_blMollieShowIcons'                     => 'Show icons',
    'SHOP_MODULE_blMollieLogTransactionInfo'            => 'Log result of transaction handling',
    'SHOP_MODULE_blMollieRemoveDeactivatedMethods'      => 'Remove deactivated payment types',
    'SHOP_MODULE_GROUP_MOLLIE_STATUS_MAPPING'           => 'Status Mapping',
    'SHOP_MODULE_sMollieStatusPending'                  => 'Status Pending',
    'SHOP_MODULE_sMollieStatusProcessing'               => 'Status Processing',
    'SHOP_MODULE_sMollieStatusCancelled'                => 'Status Cancelled',
    'SHOP_MODULE_GROUP_MOLLIE_CRONJOBS'                 => 'Cronjobs',
    'SHOP_MODULE_sMollieCronOrderExpiryActive'          => 'Cronjob "Cancel unpaid orders automatically" active',
    'SHOP_MODULE_sMollieCronFinishOrdersActive'         => 'Cronjob "Completion of paid but unfinished orders" active',
    'SHOP_MODULE_sMollieCronSecondChanceActive'         => 'Cronjob "Dispatch of payment reminder email" aktiv',
    'SHOP_MODULE_iMollieCronSecondChanceTimeDiff'       => 'Timeframe after which payment reminder email is sent',
    'SHOP_MODULE_sMollieCronOrderShipmentActive'        => 'Cronjob "Transmission of shipping status to Mollie" active',
    'SHOP_MODULE_GROUP_MOLLIE_PAYMENTLOGOS'             => 'Alternative payment logos',
    'SHOP_MODULE_GROUP_MOLLIE_APPLEPAY'                 => 'Apple Pay',
    'SHOP_MODULE_blMollieApplePayButtonOnBasket'        => 'Show Apple Pay button on basket page',
    'SHOP_MODULE_blMollieApplePayButtonOnDetails'       => 'Show Apple Pay button on product details page',

    'HELP_SHOP_MODULE_blMollieShowIcons'                => 'Show Payment Icons on Checkout',
    'HELP_SHOP_MODULE_blMollieLogTransactionInfo'       => 'Log file to be found here: SHOPROOT/log/MollieTransactions.log',
    'HELP_SHOP_MODULE_blMollieRemoveDeactivatedMethods' => 'Removes the payment types from the frontend payment selection which are not activated in the Mollie Dashboard and thus would result in an error.',
    'HELP_SHOP_MODULE_sMollieStatusPending'             => 'Set the order status before the customer is redirected to Payment Gateway',
    'HELP_SHOP_MODULE_sMollieStatusProcessing'          => 'Set the order status for Completed Payments',
    'HELP_SHOP_MODULE_sMollieStatusCancelled'           => 'Set the order status for cancelled orders',
    'HELP_SHOP_MODULE_sMollieCronOrderExpiryActive'     => 'For this cronjob to work, in addition to this checkbox you have to ensure that the Mollie cronjob is set up properly. You can find information on how to set up the cronjob in the README.md of this module.',
    'HELP_SHOP_MODULE_sMollieCronFinishOrdersActive'    => 'This cronjob has the job to finish orders where the customer paid successfully but seemingly didnt return to the shop to complete the order process. The cronjob only finishes orders from the last 24 hours, to not change orders that were probably handled manually.<br><br>For this cronjob to work, in addition to this checkbox you have to ensure that the Mollie cronjob is set up properly. You can find information on how to set up the cronjob in the README.md of this module.',
    'HELP_SHOP_MODULE_sMollieCronSecondChanceActive'    => 'For this cronjob to work, in addition to this checkbox you have to ensure that the Mollie cronjob is set up properly. You can find information on how to set up the cronjob in the README.md of this module.',
    'HELP_SHOP_MODULE_sMollieCronOrderShipmentActive'   => 'This cronjob is only needed if the shipping status in your shop is set by an external service and NOT by the "Ship Now" button. For this cronjob to work, in addition to this checkbox you have to ensure that the Mollie cronjob is set up properly. You can find information on how to set up the cronjob in the README.md of this module.',

    'MOLLIE_YES'                                        => 'Yes',
    'MOLLIE_NO'                                         => 'No',
    'MOLLIE_DAY'                                        => 'day',
    'MOLLIE_DAYS'                                       => 'days',
    'MOLLIE_IS_MOLLIE'                                  => 'This is a Mollie payment type',
    'MOLLIE_IS_METHOD_ACTIVATED'                        => 'This payment type is not activated in your Mollie account!',
    'MOLLIE_TOKEN_NOT_CONFIGURED'                       => 'Your Mollie token was not configured yet!',
    'MOLLIE_CONFIG_METHOD'                              => 'API method',
    'MOLLIE_DUE_DATE'                                   => 'Due days',
    'MOLLIE_BANKTRANSFER_PENDING'                       => 'Status Pending',
    'MOLLIE_LIST_STYLE'                                 => 'Issuer List Style',
    'MOLLIE_LIST_STYLE_DROPDOWN'                        => 'Dropdown',
    'MOLLIE_LIST_STYLE_IMAGES'                          => 'List with images',
    'MOLLIE_LIST_STYLE_DONT_SHOW'                       => 'Don\'t show issuer list',
    'MOLLIE_ADD_QR'                                     => 'Add QR-Code option in Issuer List',
    'MOLLIE_ORDER_REFUND'                               => 'Mollie',
    'MOLLIE_REFUND_SUCCESSFUL'                          => 'Refund was successful.',
    'MOLLIE_NO_MOLLIE_PAYMENT'                          => 'This order was not payed with Mollie.',
    'MOLLIE_REFUND_QUANTITY'                            => 'Refund quantity',
    'MOLLIE_REFUND_AMOUNT'                              => 'Refund amount',
    'MOLLIE_TYPE_SELECT_LABEL'                          => 'Refund by',
    'MOLLIE_QUANTITY'                                   => 'Quantity',
    'MOLLIE_NOTICE'                                     => 'Notice',
    'MOLLIE_AMOUNT'                                     => 'Amount',
    'MOLLIE_HEADER_ORDERED'                             => 'Ordered',
    'MOLLIE_HEADER_REFUNDED'                            => 'Refunded',
    'MOLLIE_HEADER_SINGLE_PRICE'                        => 'Unitprice',
    'MOLLIE_SHIPPINGCOST'                               => "Shipping cost",
    'MOLLIE_PAYMENTTYPESURCHARGE'                       => "Payment surcharge",
    'MOLLIE_WRAPPING'                                   => "Giftwrapping",
    'MOLLIE_GIFTCARD'                                   => "Greeting card",
    'MOLLIE_VOUCHER'                                    => 'Voucher',
    'MOLLIE_DISCOUNT'                                   => 'Discount',
    'MOLLIE_REFUND_SUBMIT'                              => 'Execute refund',
    'MOLLIE_FULL_REFUND'                                => 'Full refund',
    'MOLLIE_PARTIAL_REFUND'                             => 'Partial refund',
    'MOLLIE_FULL_REFUND_TEXT'                           => 'Execute full refund with the amount of',
    'MOLLIE_FULL_REFUND_NOT_AVAILABLE'                  => 'A full refund is not available for this order anymore, there were partial refunds already.',
    'MOLLIE_REFUND_DESCRIPTION'                         => 'Refund notice',
    'MOLLIE_REFUND_DESCRIPTION_PLACEHOLDER'             => 'optional - max 140 characters',
    'MOLLIE_REFUND_FREE_AMOUNT'                         => 'Free refund amount',
    'MOLLIE_REFUND_FREE_1'                              => 'Of the total price of',
    'MOLLIE_REFUND_FREE_2'                              => ',',
    'MOLLIE_REFUND_FREE_3'                              => 'have already been refunded. Remaining refundable amount',
    'MOLLIE_ORDER_NOT_REFUNDABLE'                       => 'This order has been refunded completely already.',
    'MOLLIE_REFUND_REMAINING'                           => 'Refund remaining sum',
    'MOLLIE_VOUCHERS_EXISTING'                          => 'This order includes vouchers or discount. These cant be refunded partially, they must be handled with the full or remaining refund.',
    'MOLLIE_CREDITCARD_DATA_INPUT'                      => 'Creditcard data',
    'MOLLIE_CC_HOSTED_CHECKOUT'                         => 'Input on external Mollie website',
    'MOLLIE_CC_CHECKOUT_INTEGRATION'                    => 'Input in shop checkout with iframe form inputs',
    'MOLLIE_APPLE_PAY_BUTTON_ONLY_LIVE_MODE'            => 'Please note: Payment with the Apple Pay Button is only available in live-mode.',
    'MOLLIE_APIKEY_CONNECTED'                           => 'Connection successful',
    'MOLLIE_APIKEY_DISCONNECTED'                        => 'Connection not successful',
    'MOLLIE_ORDER_EXPIRY'                               => 'Automatic cancellation after',
    'MOLLIE_ORDER_EXPIRY_DAYS'                          => 'days',
    'MOLLIE_DEACTIVATED'                                => 'Deactivated',
    'MOLLIE_ORDER_EXPIRY_HELP'                          => 'The Mollie module has the feature to cancel orders automatically after the timeframe you configured here. This applies to orders in the "Status Pending" configured by you. The Mollie cronjob has to be set up for this to work. You can find information on how to set up the cronjob in the README.md of this module.',
    'MOLLIE_ALTLOGO_ERROR'                              => 'There has been an error during the file upload. Please check the permission of the folder SHOPROOT/source/modules/mollie/molliepayment/out/img/',
    'MOLLIE_ALTLOGO_LABEL'                              => 'Alternative Logo',
    'MOLLIE_ALTLOGO_FILENAME'                           => 'Filename',
    'MOLLIE_ALTLOGO_DELETE'                             => 'Delete logo',
    'MOLLIE_SINGLE_CLICK'                               => 'Single Click payments activated',
    'MOLLIE_SINGLE_CLICK_HELP'                          => 'Single Click payment means, that the payment data of the customer is saved on Mollies side, so that the customer does not have to enter it again the next time orders with creditcard. This has to be confirmed by the customer explicitly in the checkout. This only has effect if the creditcard data mode is set to "Input on external Mollie website"',
    'MOLLIE_PAYMENT_API_LINK_1'                         => 'For more information about the Payment-API click',
    'MOLLIE_PAYMENT_API_LINK_2'                         => 'here',
    'MOLLIE_ORDER_API_LINK_1'                           => 'For more information about the Order-API click',
    'MOLLIE_ORDER_API_LINK_2'                           => 'here',
    'MOLLIE_CONNECTION_DATA'                            => 'Access your connection data here:',
    'MOLLIE_ORDER_PAYMENT_URL'                          => 'Link to payment completion',
    'MOLLIE_SEND_SECOND_CHANCE_MAIL'                    => 'Send Second Chance Email',
    'MOLLIE_SECOND_CHANCE_MAIL_ALREADY_SENT'            => 'The email has already been sent.',
    'MOLLIE_SUBSEQUENT_ORDER_COMPLETION'                => 'Subsequent order completion',
    'MOLLIE_PAYMENT_DESCRIPTION'                        => 'Payment description',
    'MOLLIE_PAYMENT_DESCRIPTION_HELP'                   => 'This will be shown to your customer on their card or bank statement when possible.<br><br>You can use the following parameters:<br>{orderId}<br>{orderNumber}<br>{storeName}<br>{customer.firstname}<br>{customer.lastname}<br>{customer.company}',
    'MOLLIE_MODULE_VERSION_OUTDATED'                    => 'Caution! The current module version is',
    'MOLLIE_SUPPORT_HEADER'                             => 'Contact Us - Technical Support',
    'MOLLIE_SUPPORT_REQUIRED_FIELDS'                    => 'Please fill in all required fields.',
    'MOLLIE_SUPPORT_FORM_NAME'                          => 'Name',
    'MOLLIE_SUPPORT_FORM_EMAIL'                         => 'E-mail',
    'MOLLIE_SUPPORT_FORM_SUBJECT'                       => 'Subject',
    'MOLLIE_SUPPORT_FORM_ENQUIRY'                       => 'Enquiry',
    'MOLLIE_SUPPORT_FORM_ENQUIRY_PLACEHOLDER'           => 'How can we help you?',
    'MOLLIE_SUPPORT_FORM_SUBMIT'                        => 'Submit',
    'MOLLIE_SUPPORT_EMAIL_SENT'                         => 'Your support enquiry has been sent. You will receive a copy of the email.',
);
