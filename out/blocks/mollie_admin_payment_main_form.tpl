[{if $edit !== null && $edit->isMolliePaymentMethod() == 1}]
    [{assign var="paymentModel" value=$edit->getMolliePaymentModel() }]
    <tr>
        <td class="edittext" colspan="2">
            <b>[{oxmultilang ident="MOLLIE_IS_MOLLIE"}]</b>
            [{if $paymentModel->isOnlyOrderApiSupported() === true}]
                <input type="hidden" name="mollie[api]" value="order">
            [{/if}]
        </td>
    </tr>
    [{if method_exists($oView, 'mollieIsTokenConfigured') && $oView->mollieIsTokenConfigured() === false }]
        <tr>
            <td class="edittext" colspan="2">
                <b style="color: red;">[{oxmultilang ident="MOLLIE_TOKEN_NOT_CONFIGURED"}]</b>
            </td>
        </tr>
    [{elseif $paymentModel->isMolliePaymentActive() === false}]
        <tr>
            <td class="edittext" colspan="2">
                <b style="color: red;">[{oxmultilang ident="MOLLIE_IS_METHOD_ACTIVATED"}]</b>
            </td>
        </tr>
    [{/if}]
    [{if $paymentModel->isOnlyOrderApiSupported() === false}]
        <tr>
            <td class="edittext" width="70">
                [{oxmultilang ident="MOLLIE_CONFIG_METHOD"}]
            </td>
            <td class="edittext">
                <select name="mollie[api]">
                    <option value="payment" [{if $paymentModel->getApiMethod() == 'payment'}]selected[{/if}]>Payment API</option>
                    <option value="order" [{if $paymentModel->getApiMethod() == 'order'}]selected[{/if}]>Order API</option>
                </select>
            </td>
        </tr>
    [{/if}]
    [{if $paymentModel->getCustomConfigTemplate() !== false}]
        [{include file=$paymentModel->getCustomConfigTemplate()}]
    [{/if}]
    <tr><td class="edittext" colspan="2">&nbsp;</td></tr>
[{/if}]
[{$smarty.block.parent}]
