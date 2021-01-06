[{assign var="oPaymentModel" value=$paymentmethod->getMolliePaymentModel()}]
[{assign var="sDataStyle" value=$oPaymentModel->getConfigParam('creditcard_data_input')}]
[{assign var="iSingleClickEnabled" value=$oPaymentModel->getConfigParam('single_click_enabled')}]
[{if $sDataStyle == "checkout_integration"}]
    <input type="hidden" name="dynvalue[mollieCCToken]" id="mollieCCToken">
    <div id="mollieCreditcardErrorbox" class="form-group" style="display:none;">
        <div class="col-lg-3"></div>
        <div class="col-lg-9">
            <div class="form-control" style="background-color:#ff5959" id="mollieCreditcardError">Fehler</div>
        </div>
    </div>

    <div class="form-group">
        <label class="req control-label col-lg-3">[{oxmultilang ident="BANK_ACCOUNT_HOLDER"}]</label>
        <div class="col-lg-9">
            <div id="mollieCardHolder" class="form-control"></div>
        </div>
    </div>

    <div class="form-group">
        <label class="req control-label col-lg-3">[{oxmultilang ident="NUMBER"}]</label>
        <div class="col-lg-9">
            <div id="mollieCardNumber" class="form-control"></div>
        </div>
    </div>

    <div class="form-group">
        <label class="req control-label col-lg-3">[{oxmultilang ident="VALID_UNTIL"}]</label>
        <div class="col-lg-9">
            <div id="mollieExpiryDate" class="form-control mollie-valid-until"></div>
        </div>
    </div>

    <div class="form-group">
        <label class="req control-label col-lg-3">[{oxmultilang ident="CARD_SECURITY_CODE"}]</label>
        <div class="col-lg-9">
            <div id="mollieVerificationCode" class="form-control"></div>
            <span class="help-block">[{oxmultilang ident="CARD_SECURITY_CODE_DESCRIPTION"}]</span>
        </div>
    </div>
    [{oxstyle include=$oViewConf->getModuleUrl('molliepayment','out/src/css/mollie.css')}]
    [{oxscript include="https://js.mollie.com/v1/mollie.js"}]
    [{capture name="mollieComponentsLoad"}]
        var mollie = Mollie('[{$oPaymentModel->getProfileId()}]', { locale: '[{$oPaymentModel->getLocale()}]'[{if $oPaymentModel->getMollieMode() == 'test'}], testMode: true [{/if}] });

        var cardHolder = mollie.createComponent('cardHolder');
        cardHolder.mount('#mollieCardHolder');

        var cardNumber = mollie.createComponent('cardNumber');
        cardNumber.mount('#mollieCardNumber');

        var expiryDate = mollie.createComponent('expiryDate');
        expiryDate.mount('#mollieExpiryDate');

        var verificationCode = mollie.createComponent('verificationCode');
        verificationCode.mount('#mollieVerificationCode');

        var paymentForm = document.getElementById('payment');
        paymentForm.addEventListener('submit', async e => {
            if (paymentForm.elements['payment_molliecreditcard'].checked === true) {
                e.preventDefault();

                const { token, error } = await mollie.createToken();

                if(error !== undefined) {
                    document.getElementById('mollieCreditcardError').innerHTML = error.message;
                    document.getElementById('mollieCreditcardErrorbox').style.display = '';
                } else {
                    document.getElementById('mollieCreditcardError').innerHTML = '';
                    document.getElementById('mollieCreditcardErrorbox').style.display = 'none';
                    document.getElementById("mollieCCToken").value = token;

                    paymentForm.submit();
                }
            }
        });
    [{/capture}]
    [{oxscript add=$smarty.capture.mollieComponentsLoad}]
[{elseif $sDataStyle == "hosted_checkout" && $iSingleClickEnabled == 1 && $oxcmp_user->hasAccount()}]
    <div class="form-check">
        <div class="col-lg-9 col-lg-offset-2">
            <input type="hidden" name="dynvalue[single_click_accepted]" value="0">
            <input class="form-check-input" type="checkbox" name="dynvalue[single_click_accepted]" value="1" id="mollieSingleClickAccepted" [{if $oxcmp_user->oxuser__molliecustomerid->value != ""}]CHECKED[{/if}]>&nbsp;
            <label class="form-check-label" for="mollieSingleClickAccepted">
                [{if $oxcmp_user->oxuser__molliecustomerid->value != ""}]
                    [{oxmultilang ident="MOLLIE_SINGLE_CLICK_ACCEPTED_HAS_CUSTOMER_ID"}]
                [{else}]
                    [{oxmultilang ident="MOLLIE_SINGLE_CLICK_ACCEPTED"}]
                [{/if}]
            </label>
            <div class="clearfix"></div>
            [{oxmultilang ident="MOLLIE_SINGLE_CLICK_INFO"}]
        </div>
    </div>
    <div class="clearfix"></div>
[{/if}]