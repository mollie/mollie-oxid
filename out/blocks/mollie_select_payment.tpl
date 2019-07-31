[{if !method_exists($paymentmethod, 'isMolliePaymentMethod') || $paymentmethod->isMolliePaymentMethod() === false}]
    [{$smarty.block.parent}]
[{else}]
    [{assign var="paymentModel" value=$paymentmethod->getMolliePaymentModel()}]
    <div class="well well-sm" id="container_[{$sPaymentID}]" [{if $paymentModel->isMollieMethodHiddenInitially()}]style="display:none"[{/if}]>
        [{if !method_exists($oViewConf, 'mollieShowIcons') || $oViewConf->mollieShowIcons() === false}]
            [{include file="page/checkout/inc/payment_other.tpl"}]
        [{else}]
            [{include file="mollie_payment_showicons.tpl"}]
        [{/if}]
    </div>
[{/if}]
