[{if $module_var == 'sMollieStatusPending' || $module_var == 'sMollieStatusProcessing'}]
    <dl>
        <dt>
            <select class="select" name="confselects[[{$module_var}]]" [{ $readonly }]>
                [{foreach from=$oView->mollieGetOrderFolders() key=sFolder item=sColor}]
                    <option value="[{$sFolder}]" [{if $confselects.$module_var == $sFolder}]selected[{/if}]>[{ oxmultilang ident=$sFolder noerror=true }]</option>
                [{/foreach}]
            </select>
            [{oxinputhelp ident="HELP_SHOP_MODULE_`$module_var`"}]
        </dt>
        <dd>
            [{oxmultilang ident="SHOP_MODULE_`$module_var`"}]
        </dd>
    </dl>
[{elseif $module_var == 'sMollieTestToken' || $module_var == 'sMollieLiveToken'}]
    <dl>
        <dt>
            <input type=text  class="txt" style="width: 250px;" name="confstrs[[{$module_var}]]" value="[{$confstrs.$module_var}]" [{$readonly}]>
            [{oxinputhelp ident="HELP_SHOP_MODULE_`$module_var`"}]
        </dt>
        <dd style="white-space: nowrap;">
            <span style="float:left;">[{oxmultilang ident="SHOP_MODULE_`$module_var`"}]</span>
            [{if $oView->mollieIsApiKeyUsable($module_var)}]
                <span id="[{$module_var}]_status" style="display:none;float:left;margin-left: 1em;color: green">[{oxmultilang ident="MOLLIE_APIKEY_CONNECTED"}]</span>
            [{else}]
                <span id="[{$module_var}]_status" style="display:none;float:left;margin-left: 1em;color: crimson">[{oxmultilang ident="MOLLIE_APIKEY_DISCONNECTED"}]</span>
            [{/if}]
        </dd>
        <div class="spacer"></div>
    </dl>
    [{if $module_var == 'sMollieLiveToken' && $oView->mollieHasApiKeys()}]
        <script type="text/javascript">
            <!--
                function mollieShowApiKeyStatus()
                {
                    document.getElementById('sMollieTestToken_status').style.display = 'block';
                    document.getElementById('sMollieLiveToken_status').style.display = 'block';
                }
            -->
        </script>
        <dl>
            <dt>
                <button onclick="mollieShowApiKeyStatus();return false;">Test API keys</button>
            </dt>
            <dd></dd>
            <div class="spacer"></div>
        </dl>
    [{/if}]
[{else}]
    [{$smarty.block.parent}]
[{/if}]
