[{if !method_exists($paymentmethod, 'isMolliePaymentMethod') || $paymentmethod->isMolliePaymentMethod() === false}]
    [{$smarty.block.parent}]
[{else}]
    <div class="well well-sm">
        [{if !method_exists($oViewConf, 'mollieShowIcons') || $oViewConf->mollieShowIcons() === false}]
            [{include file="page/checkout/inc/payment_other.tpl"}]
        [{else}]
            [{include file="mollie_payment_showicons.tpl"}]
        [{/if}]
    </div>
[{/if}]
