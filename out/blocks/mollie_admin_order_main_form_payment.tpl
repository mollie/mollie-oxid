[{$smarty.block.parent}]
[{if $edit !== null && $edit->mollieIsMolliePaymentUsed() == 1}]
    <tr>
        <td class="edittext">
            [{oxmultilang ident="MOLLIE_TRANSACTION_ID"}]
        </td>
        <td class="edittext">
            [{$edit->oxorder__oxtransid->value}]
        </td>
        <td class="edittext"></td>
    </tr>
    [{if $edit->oxorder__mollieexternaltransid->value != ""}]
        <tr>
            <td class="edittext">
                [{oxmultilang ident="MOLLIE_EXTERNAL_TRANSACTION_ID"}]
            </td>
            <td class="edittext">
                [{$edit->oxorder__mollieexternaltransid->value}]
            </td>
            <td class="edittext"></td>
        </tr>
    [{/if}]
[{/if}]