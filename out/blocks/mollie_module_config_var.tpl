[{if $module_var == 'sMollieStatusPending' || $module_var == 'sMollieStatusProcessing'}]
    <dl>
        <dt>
            <select class="select" name="confselects[[{$module_var}]]" [{ $readonly }]>
                [{foreach from=$oView->mollieGetOrderFolders() key=sFolder item=sColor}]
                    <option value="[{$sFolder}]" [{if $confselects.$module_var == $sFolder}]selected[{/if}]>[{ oxmultilang ident=$sFolder noerror=true }]</option>
                [{/foreach}]
            </select>
        </dt>
        <dd>
            [{oxmultilang ident="SHOP_MODULE_`$module_var`"}]
        </dd>
    </dl>
[{else}]
    [{$smarty.block.parent}]
[{/if}]
