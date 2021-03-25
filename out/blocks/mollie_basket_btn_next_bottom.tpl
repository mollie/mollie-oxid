[{$smarty.block.parent}]
[{if $oViewConf->mollieShowApplePayButtonOnBasket()}]
    [{oxscript include=$oViewConf->getModuleUrl('molliepayment','out/src/js/mollie.js')}]
    [{oxstyle include=$oViewConf->getModuleUrl('molliepayment','out/src/css/mollie.css')}]

    [{oxid_include_dynamic file="mollieapplepaybutton.tpl" type="mollie" position="BasketBottom" payment_price=$oViewConf->mollieGetApplePayBasketSum() delivery_costs=$oxcmp_basket->getDeliveryCosts() shipping_id=$oxcmp_basket->getShippingId()}]
[{/if}]