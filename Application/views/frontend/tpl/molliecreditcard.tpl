[{assign var="oPaymentModel" value=$paymentmethod->getMolliePaymentModel()}]
[{assign var="sDataStyle" value=$oPaymentModel->getConfigParam('creditcard_data_input')}]
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
        });
    [{/capture}]
    [{oxscript add=$smarty.capture.mollieComponentsLoad}]
[{/if}]