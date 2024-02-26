[{$smarty.block.parent}]
[{if method_exists($oViewConf, 'isMolliePayPalExpressCheckout') && $oViewConf->isMolliePayPalExpressCheckout() === true}]
    [{capture name="mollie_hide_changebuttons"}]
        $(function () {
            $('#orderAddress button').remove();
        });
    [{/capture}]
    [{oxscript add=$smarty.capture.mollie_hide_changebuttons}]
[{/if}]