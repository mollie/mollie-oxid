<tr>
    <td class="edittext" width="70">
        [{oxmultilang ident="MOLLIE_CREDITCARD_DATA_INPUT"}]
    </td>
    <td  class="edittext" width="150">
        <select name="mollie[creditcard_data_input]" style="width:177px;" [{$readonly}]>
            <option value="hosted_checkout"      [{if $paymentModel->getConfigParam('creditcard_data_input') == 'hosted_checkout'     }]selected[{/if}]>[{oxmultilang ident="MOLLIE_CC_HOSTED_CHECKOUT"}]</option>
            <option value="checkout_integration" [{if $paymentModel->getConfigParam('creditcard_data_input') == 'checkout_integration'}]selected[{/if}]>[{oxmultilang ident="MOLLIE_CC_CHECKOUT_INTEGRATION"}]</option>
        </select>
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
