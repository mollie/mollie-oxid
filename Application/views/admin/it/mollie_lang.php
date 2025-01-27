<?php

$sLangName = "Italian";
// -------------------------------
// RESOURCE IDENTITFIER = STRING
// -------------------------------
$aLang = array(
    'charset'                                           => 'UTF-8',

    /* SETTINGS */
    'SHOP_MODULE_GROUP_MOLLIE_GENERAL'                  => 'Configurazione base',
    'SHOP_MODULE_sMollieMode'                           => 'Modalità',
    'SHOP_MODULE_sMollieMode_live'                      => 'Live',
    'SHOP_MODULE_sMollieMode_test'                      => 'Test',
    'SHOP_MODULE_sMollieTestToken'                      => 'Chiave API di prova',
    'SHOP_MODULE_sMollieLiveToken'                      => 'Chiave API live',
    'SHOP_MODULE_blMollieShowIcons'                     => 'Mostra icone',
    'SHOP_MODULE_blMollieLogTransactionInfo'            => 'Registra risultato della gestione delle transazioni',
    'SHOP_MODULE_GROUP_MOLLIE_STATUS_MAPPING'           => 'Registrazione dello stato',
    'SHOP_MODULE_sMollieStatusPending'                  => 'Stato in sospeso',
    'SHOP_MODULE_sMollieStatusProcessing'               => 'Stato in elaborazione',
    'SHOP_MODULE_sMollieStatusCancelled'                => 'Stato annullato',
    'SHOP_MODULE_GROUP_MOLLIE_CRONJOBS'                 => 'Cronjobs',
    'SHOP_MODULE_sMollieCronOrderExpiryActive'          => 'Cronjob "Cancella automaticamente gli ordini non pagati" attivo',
    'SHOP_MODULE_sMollieCronFinishOrdersActive'         => 'Cronjob "Completamento di ordini pagati ma non conclusi" attivo',
    'SHOP_MODULE_sMollieCronSecondChanceActive'         => 'Cronjob "Invio di email di promemoria del pagamento" attivo',
    'SHOP_MODULE_iMollieCronSecondChanceTimeDiff'       => 'Termine dopo il quale viene inviata l\'email di promemoria del pagamento',
    'SHOP_MODULE_sMollieCronOrderShipmentActive'        => 'Cronjob "Trasmissione dello stato di spedizione a Mollie" attivo',
    'SHOP_MODULE_GROUP_MOLLIE_PAYMENTLOGOS'             => 'Loghi di pagamento alternativi',
    'SHOP_MODULE_GROUP_MOLLIE_APPLEPAY'                 => 'Apple Pay',
    'SHOP_MODULE_blMollieApplePayButtonOnBasket'        => 'Mostra il pulsante Apple Pay sulla pagina del carrello',
    'SHOP_MODULE_blMollieApplePayButtonOnDetails'       => 'Mostra il pulsante Apple Pay sulla pagina dei dettagli del prodotto',

    'HELP_SHOP_MODULE_blMollieShowIcons'                => 'Mostra le icone di pagamento al checkout',
    'HELP_SHOP_MODULE_blMollieLogTransactionInfo'       => 'Il file di log è disponibile qui: SHOPROOT/log/MollieTransactions.log',
    'HELP_SHOP_MODULE_sMollieStatusPending'             => 'Impostare lo stato dell\'ordine prima che il cliente venga reindirizzato al gateway di pagamento',
    'HELP_SHOP_MODULE_sMollieStatusProcessing'          => 'Impostare lo stato dell\'ordine per Pagamenti completati',
    'HELP_SHOP_MODULE_sMollieStatusCancelled'           => 'Impostare lo stato dell\'ordine per Ordini annullati',
    'HELP_SHOP_MODULE_sMollieCronOrderExpiryActive'     => 'Perché questo cronjob funzioni, oltre a questa casella di spunta è necessario assicurarsi che il cronjob di Mollie sia impostato correttamente. Potete trovare informazioni su come impostare il cronjob nel README.md di questo modulo.',
    'HELP_SHOP_MODULE_sMollieCronFinishOrdersActive'    => 'Questo cronjob ha il compito di concludere gli ordini in cui il pagamento del cliente è andato a buon fine ma apparentemente il cliente non è tornato al negozio per completare il processo dell\'ordine. Il cronjob conclude solo gli ordini delle ultime 24 ore, per non modificare gli ordini che probabilmente sono stati gestiti manualmente.<br><br>Perché questo cronjob funzioni, oltre a questa casella di spunta dovete assicurarvi che il cronjob di Mollie sia impostato correttamente. Potete trovare informazioni su come impostare il cronjob nel README.md di questo modulo.',
    'HELP_SHOP_MODULE_sMollieCronSecondChanceActive'    => 'Perché questo cronjob funzioni, oltre a questa casella di spunta è necessario assicurarsi che il cronjob di Mollie sia impostato correttamente. Potete trovare informazioni su come impostare il cronjob nel README.md di questo modulo.',
    'HELP_SHOP_MODULE_sMollieCronOrderShipmentActive'   => 'Questo cronjob è necessario solo se lo stato di spedizione nel vostro negozio è impostato da un servizio esterno e NON dal pulsante "Spedisci ora". Perché questo cronjob funzioni, oltre a questa casella di spunta è necessario assicurarsi che il cronjob di Mollie sia impostato correttamente. Potete trovare informazioni su come impostare il cronjob nel README.md di questo modulo.',

    'MOLLIE_YES'                                        => 'Sì',
    'MOLLIE_NO'                                         => 'No',
    'MOLLIE_DAY'                                        => 'giorno',
    'MOLLIE_DAYS'                                       => 'giorni',
    'MOLLIE_IS_MOLLIE'                                  => 'Questo è un tipo di pagamento Mollie',
    'MOLLIE_IS_METHOD_ACTIVATED'                        => 'Questo tipo di pagamento non è attivato nel vostro account Mollie!',
    'MOLLIE_TOKEN_NOT_CONFIGURED'                       => 'Il vostro token Mollie non è stato ancora configurato!',
    'MOLLIE_CONFIG_METHOD'                              => 'API Method',
    'MOLLIE_DUE_DATE'                                   => 'Giorni fino alla scadenza',
    'MOLLIE_BANKTRANSFER_PENDING'                       => 'Stato in sospeso',
    'MOLLIE_LIST_STYLE'                                 => 'Stile elenco degli emittenti',
    'MOLLIE_LIST_STYLE_DROPDOWN'                        => 'Dropdown',
    'MOLLIE_LIST_STYLE_IMAGES'                          => 'Elenco con immagini',
    'MOLLIE_LIST_STYLE_DONT_SHOW'                       => 'Non mostrare l’elenco degli emittenti',
    'MOLLIE_ADD_QR'                                     => 'Aggiungere l\'opzione QR Code nell\'elenco degli emittenti',
    'MOLLIE_ORDER_REFUND'                               => 'Mollie',
    'MOLLIE_ADMIN'                                      => 'Mollie',
    'MOLLIE_ADMIN_API_LOGS'                             => 'Registri API',
    'MOLLIE_ADMIN_API_LOGS_MAIN'                        => 'Registri',
    'MOLLIE_ADMIN_API_LOGS_SELECT_ENTRY'                => 'Selezionare un articolo dall\'elenco qui sopra.',
    'MOLLIE_REFUND_SUCCESSFUL'                          => 'Rimborso non riuscito.',
    'MOLLIE_NO_MOLLIE_PAYMENT'                          => 'Questo ordine non è stato pagato con Mollie.',
    'MOLLIE_REFUND_QUANTITY'                            => 'Quantità rimborso',
    'MOLLIE_REFUND_AMOUNT'                              => 'Importo rimborso',
    'MOLLIE_TYPE_SELECT_LABEL'                          => 'Rimborsato da',
    'MOLLIE_QUANTITY'                                   => 'Quantità',
    'MOLLIE_NOTICE'                                     => 'Avviso',
    'MOLLIE_AMOUNT'                                     => 'Importo',
    'MOLLIE_HEADER_ORDERED'                             => 'Ordinato',
    'MOLLIE_HEADER_REFUNDED'                            => 'Rimborsato',
    'MOLLIE_HEADER_SINGLE_PRICE'                        => 'Prezzo unitario',
    'MOLLIE_SHIPPINGCOST'                               => "Spese di spedizione",
    'MOLLIE_PAYMENTTYPESURCHARGE'                       => "Supplemento di pagamento",
    'MOLLIE_WRAPPING'                                   => "Confezione regalo",
    'MOLLIE_GIFTCARD'                                   => "Biglietto d'auguri",
    'MOLLIE_VOUCHER'                                    => 'Voucher',
    'MOLLIE_DISCOUNT'                                   => 'Sconto',
    'MOLLIE_REFUND_SUBMIT'                              => 'Esegui rimborso',
    'MOLLIE_FULL_REFUND'                                => 'Rimborso totale',
    'MOLLIE_PARTIAL_REFUND'                             => 'Rimborso parziale',
    'MOLLIE_FULL_REFUND_TEXT'                           => 'Eseguire il rimborso totale con l\'importo di',
    'MOLLIE_FULL_REFUND_NOT_AVAILABLE'                  => 'Un rimborso totale non è più disponibile per questo ordine, sono già stati effettuati rimborsi parziali.',
    'MOLLIE_REFUND_DESCRIPTION'                         => 'Notifica di rimborso',
    'MOLLIE_REFUND_DESCRIPTION_PLACEHOLDER'             => 'opzionale - max 140 caratteri',
    'MOLLIE_REFUND_FREE_AMOUNT'                         => 'Importo rimborso gratuito',
    'MOLLIE_REFUND_FREE_1'                              => 'Del prezzo totale di',
    'MOLLIE_REFUND_FREE_2'                              => ',',
    'MOLLIE_REFUND_FREE_3'                              => 'sono già stati rimborsati. Importo rimborsabile residuo',
    'MOLLIE_ORDER_NOT_REFUNDABLE'                       => 'Questo ordine è già stato rimborsato completamente.',
    'MOLLIE_NOT_YET_PAID'                               => 'Questo ordine non è ancora contrassegnato come pagato e pertanto non può ancora essere rimborsato.',
    'MOLLIE_TRANSACTION_NOT_USABLE'                     => 'Si è verificato un errore con questo ordine. Non esiste un ID di transazione Mollie valido.',
    'MOLLIE_REFUND_REMAINING'                           => 'Importo rimanente del rimborso',
    'MOLLIE_VOUCHERS_EXISTING'                          => 'Questo ordine include voucher o sconti. Questi non possono essere rimborsati parzialmente, devono essere gestiti con il rimborso totale o residuo.',
    'MOLLIE_CREDITCARD_DATA_INPUT'                      => 'Dati della carta di credito',
    'MOLLIE_CREDITCARD_DATA_INPUT_HELP'                 => 'Questa opzione definisce dove il cliente deve inserire i dati della carta di credito.<br>Il metodo consigliato è "Input nel checkout del negozio con input del modulo iframe".',
    'MOLLIE_CC_HOSTED_CHECKOUT'                         => 'Input sul sito web esterno di Mollie',
    'MOLLIE_CC_CHECKOUT_INTEGRATION'                    => 'Input nel checkout del negozio con input del modulo iframe',
    'MOLLIE_APPLE_PAY_BUTTON_ONLY_LIVE_MODE'            => 'N.B.: Il pagamento con il pulsante Apple Pay è disponibile solo in modalità live.',
    'MOLLIE_APIKEY_CONNECTED'                           => 'Collegamento riuscito',
    'MOLLIE_APIKEY_DISCONNECTED'                        => 'Collegamento non riuscito',
    'MOLLIE_ORDER_EXPIRY'                               => 'Cancellazione automatica dopo',
    'MOLLIE_ORDER_EXPIRY_DAYS'                          => 'giorni',
    'MOLLIE_DEACTIVATED'                                => 'Disattivato',
    'MOLLIE_ORDER_EXPIRY_HELP'                          => 'Il modulo Mollie ha la funzione per cancellare automaticamente gli ordini dopo il periodo di tempo da voi configurato qui. Questo vale per gli ordini in "Stato in sospeso" configurati da voi. Perché questo funzioni, il cronjob di Mollie deve essere impostato. Potete trovare informazioni su come impostare il cronjob nel README.md di questo modulo.',
    'MOLLIE_ALTLOGO_ERROR'                              => 'Si è verificato un errore durante il caricamento del file. Controlla i permessi della cartella SHOPROOT/source/modules/mollie/molliepayment/out/img/',
    'MOLLIE_ALTLOGO_LABEL'                              => 'Logo alternativo',
    'MOLLIE_ALTLOGO_FILENAME'                           => 'Nome file',
    'MOLLIE_ALTLOGO_DELETE'                             => 'Cancella logo',
    'MOLLIE_SINGLE_CLICK'                               => 'Pagamenti con un unico clic attivati',
    'MOLLIE_SINGLE_CLICK_HELP'                          => 'Il pagamento con un unico clic significa che i dati di pagamento del cliente vengono salvati presso Mollies, in modo che il cliente non debba inserirli di nuovo la prossima volta che ordina con la carta di credito. Questo deve essere confermato esplicitamente dal cliente nel checkout. Questo ha effetto solo se la modalità dei dati della carta di credito è impostata su "Input su sito web Mollie esterno"',
    'MOLLIE_PAYMENT_API_LINK_1'                         => 'Per maggiori informazioni su Payment-API clicca',
    'MOLLIE_PAYMENT_API_LINK_2'                         => 'qui',
    'MOLLIE_ORDER_API_LINK_1'                           => 'Per maggiori informazioni su Order-API clicca',
    'MOLLIE_ORDER_API_LINK_2'                           => 'qui',
    'MOLLIE_CONNECTION_DATA'                            => 'Accedi ai tuoi dati di connessione qui:',
    'MOLLIE_ORDER_PAYMENT_URL'                          => 'Link per il completamento del pagamento',
    'MOLLIE_SEND_SECOND_CHANCE_MAIL'                    => 'Invia un’e-mail Second Chance',
    'MOLLIE_SECOND_CHANCE_MAIL_ALREADY_SENT'            => 'L\'e-mail è già stata inviata.',
    'MOLLIE_SUBSEQUENT_ORDER_COMPLETION'                => 'Completamento dell\'ordine successivo',
    'MOLLIE_PAYMENT_DESCRIPTION'                        => 'Descrizione pagamento',
    'MOLLIE_PAYMENT_DESCRIPTION_HELP'                   => 'Sarà mostrata al vostro cliente sull’estratto conto bancario o della sua carta quando possibile.<br><br>Potete usare i seguenti parametri:<br>{orderId}<br>{orderNumber}<br>{storeName}<br>{customer.firstname}<br>{customer.lastname}<br>{customer.company}',
    'MOLLIE_PAYMENT_DETAILS'                            => 'Payment details',
    'MOLLIE_PAYMENT_TYPE'                               => 'Payment type',
    'MOLLIE_TRANSACTION_ID'                             => 'Mollie Transaction ID',
    'MOLLIE_EXTERNAL_TRANSACTION_ID'                    => 'External Transaction ID',
    'MOLLIE_PAYMENT_DISABLED_ACTIVATION'                => 'Questo metodo di pagamento Mollie non può più essere attivato perché sarà presto rimosso!',
    'MOLLIE_CAPTURE_TITLE'                              => 'Capture payments',
    'MOLLIE_CAPTURE_STATUS'                             => 'Status',
    'MOLLIE_CAPTURE_DESCRIPTION'                        => 'Amount to capture',
    'MOLLIE_CAPTURE_AMOUNT'                             => 'Capture amount',
    'MOLLIE_CC_CAPTURE_DIRECT'                          => 'Directly capture credit card amounts',
    'MOLLIE_CC_CAPTURE_AUTH'                            => 'Authenticate credit card amounts before capture',
    'MOLLIE_CC_CAPTURE_AUTOMATIC'                       => 'Automatically capture credit card amounts',
    'MOLLIE_CAPTURE_DAYS'                               => 'Automatically capture after',
    'MOLLIE_CAPTURE_ID'                                 => 'Mollie Capture ID',
    'MOLLIE_CAPTURE_SUCCESSFUL'                         => 'Amount captured successfully.',
    'MOLLIE_CREDITCARD_CAPTURE'                         => 'Capture Method',
    'MOLLIE_CREDITCARD_CAPTURE_METHOD_HELP'             => 'This option defines which capture method is used.<br><strong>Authenticate credit card before capture</strong>:The amount will be authorized and you have to manually capture the amount via the mollie tab within the order or via provided cron job<br><strong>Directly capture credit card amounts:</strong> The amount will be directly captured<br><strong>Automatically capture credit card amounts:</strong> The amount will be automatically captured by mollie after X days',
    'HELP_SHOP_MODULE_sMollieCronCaptureOrdersActive'   => 'This option works only if you have <strong>Authenticate credit card before capture</strong> as capture method selected. This cronjob captures orders which are fullfilled and you normally would need to capture manually.',
);
