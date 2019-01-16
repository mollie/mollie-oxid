<tr>
    <td class="edittext" width="100">
        [{oxmultilang ident="MOLLIE_DUE_DATE"}]
    </td>
    <td class="edittext">
        <input type="text" class="editinput" size="25" maxlength="10" name="mollie[due_days]" value="[{$paymentModel->getConfigParam('due_days')}]" [{$readonly}]>
    </td>
</tr>
