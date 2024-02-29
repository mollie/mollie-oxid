<a id="molliePayPalExpressButton[{$_mollie_position}]" class="molliePayPalExpress[{$_mollie_position}]" href="#" title="PayPal Express">
    <img src="[{$oViewConf->getMolliePayPalExpressButtonImageUrl()}]" alt="PayPal Express" />
</a><br>
[{capture name="molliePayPalExpressAjaxSpinner"}]
    addSpinnerDiv();
    $(document).on({
        ajaxStart: function(){
            $("#mollie-overlay").fadeIn(300);
        },
        ajaxStop: function(){
            $("#mollie-overlay").fadeOut(300);
        }
    });
[{/capture}]
[{oxscript add=$smarty.capture.molliePayPalExpressAjaxSpinner}]
[{oxscript add="var shopBaseUrl = '"|cat:$oViewConf->mollieGetShopUrl()|cat:"';"}]
[{oxscript add="mollieAddPayPalExpressClickEvent('molliePayPalExpressButton"|cat:$_mollie_position|cat:"', '"|cat:$_mollie_position|cat:"')"}]