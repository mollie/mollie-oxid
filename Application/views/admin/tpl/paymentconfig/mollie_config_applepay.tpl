<tr>
    <td class="edittext" width="70">
        [{oxmultilang ident="MOLLIE_APPLE_PAY_IGNORE_DELIVERY_ADDRESS"}]
    </td>
    <td class="edittext" width="150">
        <input type="hidden" name="mollie[ignore_apple_pay_delivery_address]" value="0">
        <input type="checkbox" name="mollie[ignore_apple_pay_delivery_address]" value="1" [{if $paymentModel->getConfigParam('ignore_apple_pay_delivery_address') == 1}]checked[{/if}] [{$readonly}]>
        [{oxinputhelp ident="MOLLIE_APPLE_PAY_IGNORE_DELIVERY_ADDRESS_HELP"}]
    </td>
</tr>
<tr>
    <td class="edittext" colspan="2">
        [{oxmultilang ident="MOLLIE_APPLE_PAY_BUTTON_ONLY_LIVE_MODE"}]
    </td>
</tr>
