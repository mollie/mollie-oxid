[{if $oViewConf->mollieCanShowPayPalExpressButton()}]
    <a id="molliePayPalExpressButton[{$_mollie_position}]" class="molliePayPalExpress[{$_mollie_position}]" href="#" title="PayPal Express">
        <img src="[{$oViewConf->getMolliePayPalExpressButtonImageUrl()}]" alt="PayPal Express" />
    </a><br>
    [{capture name="molliePayPalExpressAjaxSpinner"}]
        addSpinnerDiv();
    [{/capture}]
    [{oxscript add=$smarty.capture.molliePayPalExpressAjaxSpinner}]
    [{oxscript add="var shopBaseUrl = '"|cat:$oViewConf->mollieGetShopUrl()|cat:"';"}]
    [{oxscript add="mollieAddPayPalExpressClickEvent('molliePayPalExpressButton"|cat:$_mollie_position|cat:"', '"|cat:$_mollie_position|cat:"')"}]
[{/if}]