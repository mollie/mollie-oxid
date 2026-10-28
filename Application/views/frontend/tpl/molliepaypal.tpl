[{if $oViewConf->molliePaypalVaultIsActive() && $paymentModel->canShowPayPalVaulting()}]
    [{assign var="hasActiveVault" value=$paymentModel->hasActivePayPalVault()}]
    <div class="form-check">
        <div class="col-lg-9 col-lg-offset-2">
            <input type="hidden" name="dynvalue[paypal_vault_accepted]" value="0">
            <input class="form-check-input" type="checkbox" name="dynvalue[paypal_vault_accepted]" value="1" id="molliePayPalVaultAccepted" [{if $hasActiveVault }]CHECKED[{/if}]>&nbsp;
            <label class="form-check-label" for="molliePayPalVaultAccepted">
                [{if $hasActiveVault }]
                    [{oxmultilang ident="MOLLIE_PP_VAULT_ACCEPTED_HAS_CUSTOMER_ID"}]
                [{else}]
                    [{oxmultilang ident="MOLLIE_PP_VAULT_ACCEPTED"}]
                [{/if}]
            </label>
            <div class="clearfix"></div>
            [{oxmultilang ident="MOLLIE_PP_VAULT_INFO"}]
        </div>
    </div>
    <div class="clearfix"></div>
[{/if}]
