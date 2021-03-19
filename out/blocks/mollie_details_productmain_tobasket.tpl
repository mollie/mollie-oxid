[{$smarty.block.parent}]
[{if $oViewConf->mollieShowApplePayButtonOnDetails()}]
    [{oxscript include=$oViewConf->getModuleUrl('molliepayment','out/src/js/mollie.js')}]
    [{oxstyle include=$oViewConf->getModuleUrl('molliepayment','out/src/css/mollie.css')}]

    [{assign var="price" value=$oDetailsProduct->getPrice()}]
    [{assign var="price" value=$oDetailsProduct->getBasketPrice(1, null, $oxcmp_basket)}]

    [{oxid_include_dynamic file="mollieapplepaybutton.tpl" type="mollie" position="Details" payment_price=$price->getBruttoPrice() details_product_id=$oDetailsProduct->getId() shipping_id=$oxcmp_basket->getShippingId()}]
[{/if}]