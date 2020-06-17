[{$smarty.block.parent}]

[{oxscript include=$oViewConf->getModuleUrl('molliepayment','out/src/js/mollie.js')}]
[{oxstyle include=$oViewConf->getModuleUrl('molliepayment','out/src/css/mollie.css')}]

[{oxid_include_dynamic file="mollieapplepaybutton.tpl" type="mollie" position="BasketTop" payment_price=$oxcmp_basket->getBruttoSum()}]