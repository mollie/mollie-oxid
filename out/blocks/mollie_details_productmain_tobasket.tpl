[{$smarty.block.parent}]

[{if $oViewConf->mollieShowApplePayButtonOnDetails() || $oViewConf->mollieShowPayPalExpressButtonOnDetails()}]
    [{assign var="price" value=$oDetailsProduct->getPrice()}]
    [{assign var="price" value=$oDetailsProduct->getBasketPrice(1, null, $oxcmp_basket)}]

    [{oxscript include=$oViewConf->getModuleUrl('molliepayment','out/src/js/mollie.js')}]
    [{oxstyle include=$oViewConf->getModuleUrl('molliepayment','out/src/css/mollie.css')}]

    [{if $oViewConf->mollieShowApplePayButtonOnDetails()}]
        [{oxid_include_dynamic file="mollieapplepaybutton.tpl" type="mollie" position="Details" payment_price=$price->getBruttoPrice() details_product_id=$oDetailsProduct->getId() shipping_id=$oxcmp_basket->getShippingId()}]
    [{/if}]

    [{if $oViewConf->mollieShowPayPalExpressButtonOnDetails()}]
        [{oxid_include_dynamic file="molliepaypalexpress.tpl" type="mollie" position="Details"}]
    [{/if}]
[{/if}]
