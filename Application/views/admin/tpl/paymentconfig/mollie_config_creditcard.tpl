<tr>
    <td class="edittext" width="70">
        [{oxmultilang ident="MOLLIE_CREDITCARD_DATA_INPUT"}]
    </td>
    <td  class="edittext" width="150">
        <select name="mollie[creditcard_data_input]" style="width:177px;" [{$readonly}]>
            <option value="checkout_integration" [{if $paymentModel->getConfigParam('creditcard_data_input') == 'checkout_integration'}]selected[{/if}]>[{oxmultilang ident="MOLLIE_CC_CHECKOUT_INTEGRATION"}]</option>
            <option value="hosted_checkout"      [{if $paymentModel->getConfigParam('creditcard_data_input') == 'hosted_checkout'     }]selected[{/if}]>[{oxmultilang ident="MOLLIE_CC_HOSTED_CHECKOUT"}]</option>
        </select>
        [{oxinputhelp ident="MOLLIE_CREDITCARD_DATA_INPUT_HELP"}]
    </td>

</tr>
<tr class="mollieOnlyPaymentApi" [{if $paymentModel->getApiMethod() != 'payment'}]style="display:none;"[{/if}]>
    <td class="edittext" width="70">
        [{oxmultilang ident="MOLLIE_CREDITCARD_CAPTURE"}]
    </td>
    <td  class="edittext" width="150">
        <select name="mollie[creditcard_capture_method]" style="width:177px;" [{$readonly}]>
            <option value="creditcard_authorize_capture" [{if $paymentModel->getConfigParam('creditcard_capture_method') == 'creditcard_authorize_capture' }]selected[{/if}]>[{oxmultilang ident="MOLLIE_CC_CAPTURE_AUTH"}]</option>
            <option value="creditcard_direct_capture"    [{if $paymentModel->getConfigParam('creditcard_capture_method') == 'creditcard_direct_capture'    }]selected[{/if}]>[{oxmultilang ident="MOLLIE_CC_CAPTURE_DIRECT"}]</option>
            <option value="creditcard_automatic_capture" [{if $paymentModel->getConfigParam('creditcard_capture_method') == 'creditcard_automatic_capture' }]selected[{/if}]>[{oxmultilang ident="MOLLIE_CC_CAPTURE_AUTOMATIC"}]</option>
        </select>
        [{oxinputhelp ident="MOLLIE_CREDITCARD_DATA_INPUT_HELP"}]
    </td>

</tr>
<tr class="mollieOnlyPaymentApi" [{if $paymentModel->getApiMethod() != 'payment'}]style="display:none;"[{/if}]>
    <td class="edittext" width="70">
        [{oxmultilang ident="MOLLIE_CAPTURE_DAYS"}]
    </td>
    <td class="edittext">
        <select name="mollie[captureDays]" [{$readonly}]>
            [{foreach from=$oView->mollieGetAutomaticCaptureDays() item=title key=days}]
                <option value="[{$days}]" [{if $paymentModel->getCaptureDays() == $days}]selected[{/if}]>[{$title}]</option>
            [{/foreach}]
        </select>
        [{oxinputhelp ident="MOLLIE_CAPTURE_DAYS_HELP"}]
    </td>
</tr>
<tr>
    <td class="edittext" width="70">
        [{oxmultilang ident="MOLLIE_SINGLE_CLICK"}]
    </td>
    <td class="edittext" width="150">
        <input type="hidden" name="mollie[single_click_enabled]" value="0">
        <input type="checkbox" name="mollie[single_click_enabled]" value="1" [{if $paymentModel->getConfigParam('single_click_enabled') == 1}]checked[{/if}] [{$readonly}]>
        [{oxinputhelp ident="MOLLIE_SINGLE_CLICK_HELP"}]
    </td>
</tr>
