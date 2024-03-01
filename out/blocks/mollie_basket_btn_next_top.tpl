[{$smarty.block.parent}]

[{if $oViewConf->mollieShowApplePayButtonOnBasket() || $oViewConf->mollieShowPayPalExpressButtonOnBasket()}]
    [{oxscript include=$oViewConf->getModuleUrl('molliepayment','out/src/js/mollie.js')}]
    [{oxstyle include=$oViewConf->getModuleUrl('molliepayment','out/src/css/mollie.css')}]

    [{if $oViewConf->mollieShowApplePayButtonOnBasket()}]
        [{oxid_include_dynamic file="mollieapplepaybutton.tpl" type="mollie" position="BasketTop" payment_price=$oViewConf->mollieGetApplePayBasketSum() delivery_costs=$oxcmp_basket->getDeliveryCosts() shipping_id=$oxcmp_basket->getShippingId()}]
    [{/if}]

    [{if $oViewConf->mollieShowPayPalExpressButtonOnBasket()}]
        [{oxid_include_dynamic file="molliepaypalexpress.tpl" type="mollie" position="BasketTop"}]
    [{/if}]
[{/if}]
