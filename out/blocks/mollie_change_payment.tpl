[{if method_exists($oViewConf, 'isMolliePayPalExpressCheckout') && $oViewConf->isMolliePayPalExpressCheckout() === true}]
    <div class="panel panel-default">
        <div class="card">
            <div class="panel-heading">
                <h3 class="panel-title">[{oxmultilang ident="MOLLIE_PAYPAL_EXPRESS"}]</h3>
            </div>
            <div class="panel-body">
                <div class="pull-left">
                    [{oxmultilang ident="MOLLIE_PAYPAL_EXPRESS_INFO"}]
                </div>
                <div class="pull-right">
                    <a class="btn btn-default" href="[{$oView->getMolliePayPaylExpressCheckoutCancelUrl()}]">[{oxmultilang ident="MOLLIE_PAYPAL_EXPRESS_UNLINK"}]</a>
                </div>
            </div>
        </div>
    </div>
    [{assign var="mollieSelectPaymentFirst" value=true }][{* used in mollie_select_payment.tpl *}]
    [{capture name="mollie_hide_paymentlist"}]
        $(function () {
            $('#payment > .panel.panel-default:first').hide();
        });
    [{/capture}]
    [{oxscript add=$smarty.capture.mollie_hide_paymentlist}]
[{/if}]
[{$smarty.block.parent}]