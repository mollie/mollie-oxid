<tr>
    <td class="edittext" width="70">
        [{oxmultilang ident="MOLLIE_CREDITCARD_DATA_INPUT"}]
    </td>
    <td  class="edittext" width="150">
        <select name="mollie[creditcard_data_input]" style="width:177px;">
            <option value="hosted_checkout"      [{if $paymentModel->getConfigParam('creditcard_data_input') == 'hosted_checkout'     }]selected[{/if}]>[{oxmultilang ident="MOLLIE_CC_HOSTED_CHECKOUT"}]</option>
            <option value="checkout_integration" [{if $paymentModel->getConfigParam('creditcard_data_input') == 'checkout_integration'}]selected[{/if}]>[{oxmultilang ident="MOLLIE_CC_CHECKOUT_INTEGRATION"}]</option>
        </select>
    </td>
</tr>
