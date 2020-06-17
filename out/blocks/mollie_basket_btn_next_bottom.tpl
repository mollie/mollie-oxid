[{$smarty.block.parent}]

[{oxscript include=$oViewConf->getModuleUrl('molliepayment','out/src/js/mollie.js')}]
[{oxstyle include=$oViewConf->getModuleUrl('molliepayment','out/src/css/mollie.css')}]

[{oxid_include_dynamic file="mollieapplepaybutton.tpl" type="mollie" position="BasketBottom" payment_price=$oxcmp_basket->getBruttoSum()}]