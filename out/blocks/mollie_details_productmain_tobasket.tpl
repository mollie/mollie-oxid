[{$smarty.block.parent}]

[{oxscript include=$oViewConf->getModuleUrl('molliepayment','out/src/js/mollie.js')}]
[{oxstyle include=$oViewConf->getModuleUrl('molliepayment','out/src/css/mollie.css')}]

[{assign var="price" value=$oDetailsProduct->getPrice()}]

[{oxid_include_dynamic file="mollieapplepaybutton.tpl" type="mollie" position="Details" payment_price=$price->getBruttoPrice() details_product_id=$oDetailsProduct->getId()}]