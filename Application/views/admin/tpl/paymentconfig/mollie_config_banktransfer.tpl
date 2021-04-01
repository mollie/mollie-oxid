<tr>
    <td class="edittext" width="100">
        [{oxmultilang ident="MOLLIE_BANKTRANSFER_PENDING"}]
    </td>
    <td class="edittext">
        <select name="mollie[pending_status]" [{$readonly}]>
            [{foreach from=$oView->mollieGetOrderFolders() key=sFolder item=sColor}]
                <option value="[{$sFolder}]" [{if $paymentModel->getConfigParam('pending_status') == $sFolder}]selected[{/if}]>[{ oxmultilang ident=$sFolder noerror=true }]</option>
            [{/foreach}]
        </select>
    </td>
</tr>
<tr>
    <td class="edittext" width="100">
        [{oxmultilang ident="MOLLIE_DUE_DATE"}]
    </td>
    <td class="edittext">
        <input type="text" class="editinput" size="25" maxlength="10" name="mollie[due_days]" value="[{$paymentModel->getConfigParam('due_days')}]" [{$readonly}]>
    </td>
</tr>
