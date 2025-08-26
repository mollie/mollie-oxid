[{if $module_var == 'sMollieStatusPending' || $module_var == 'sMollieStatusProcessing' || $module_var == 'sMollieStatusCancelled'}]
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
            <input type="password"  class="txt" style="width: 250px;" name="confstrs[[{$module_var}]]" value="[{$confstrs.$module_var}]" [{$readonly}]>
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
    [{if $module_var == 'sMollieTestToken'}]
        <dl>
            <dt></dt>
            <dd>[{oxmultilang ident="MOLLIE_CONNECTION_DATA"}] <a href="https://www.mollie.com/admin" target="_blank">https://www.mollie.com/admin</a></dd>
            <div class="spacer"></div>
        </dl>
        [{if $oView->mollieHasApiKeys()}]
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
    [{/if}]
[{elseif $module_var == 'sMolliePPEButtonPreviewPaceholder'}]
    <dl>
        <dd>
            [{oxmultilang ident="SHOP_MODULE_sMolliePPEButtonPreviewPaceholder"}]<br>
            <select id="sMolliePPEButtonLanguage">
                <option value="en">[{oxmultilang ident="SHOP_MODULE_sMolliePPEButtonLangEn"}]</option>
                <option value="de">[{oxmultilang ident="SHOP_MODULE_sMolliePPEButtonLangDe"}]</option>
                <option value="nl">[{oxmultilang ident="SHOP_MODULE_sMolliePPEButtonLangNl"}]</option>
                <option value="fr">[{oxmultilang ident="SHOP_MODULE_sMolliePPEButtonLangFr"}]</option>
                <option value="pl">[{oxmultilang ident="SHOP_MODULE_sMolliePPEButtonLangPl"}]</option>
            </select>[{oxinputhelp ident="HELP_SHOP_MODULE_sMolliePPEButtonLang"}]<br>
            <script type="text/javascript">
                function mollieAddChangeEvents() {
                    document.getElementsByName("confselects[sMolliePPEButtonShape]")[0].addEventListener("change", updatePreviewImgUrl);
                    document.getElementsByName("confselects[sMolliePPEButtonType]")[0].addEventListener("change", updatePreviewImgUrl);
                    document.getElementsByName("confselects[sMolliePPEButtonColor]")[0].addEventListener("change", updatePreviewImgUrl);
                    document.getElementById("sMolliePPEButtonLanguage").addEventListener("change", updatePreviewImgUrl);
                }
                function updatePreviewImgUrl() {
                    let shopBaseUrl = window.location.href.split("/admin/")[0] + "/admin/";

                    var data = new FormData();
                    data.append('cl', 'module_config');
                    data.append('oxid', 'molliepayment');
                    data.append('fnc', 'mollieGetPayPalExpressImageUrl');
                    data.append('stoken', window.moduleConfiguration.stoken.value);
                    data.append('admin_sid', window.moduleConfiguration.admin_sid.value);

                    data.append('shape', document.getElementsByName("confselects[sMolliePPEButtonShape]")[0].value);
                    data.append('type', document.getElementsByName("confselects[sMolliePPEButtonType]")[0].value);
                    data.append('color', document.getElementsByName("confselects[sMolliePPEButtonColor]")[0].value);
                    data.append('language', document.getElementById("sMolliePPEButtonLanguage").value);

                    let xhr = new XMLHttpRequest()
                    xhr.open('POST', shopBaseUrl, true)
                    xhr.send(data);
                    xhr.onload = function () {
                        if(xhr.status === 200 && xhr.responseText) {
                            showPreviewImg(xhr.responseText);
                        }
                    }

                }
                function showPreviewImg(url) {
                    let imgElementId = "sMolliePPEButtonPreviewImg";
                    let img = document.getElementById(imgElementId);
                    if (!img) {
                        img = document.createElement("img");
                        img.id = imgElementId;
                        img.style.marginTop = "1em";
                        document.getElementById("sMolliePPEButtonPreview").appendChild(img)
                    }
                    img.src = url;
                }
                mollieAddChangeEvents();
                updatePreviewImgUrl();
            </script>
            <div id="sMolliePPEButtonPreview"></div>
        </dd>
    </dl>
[{elseif $module_var == 'sMolliePaymentLogosPlaceholder'}]
    <link rel="stylesheet" href="[{$oViewConf->getModuleUrl('molliepayment','out/src/css/mollie.css')}]">
    <input type="hidden" name="mollieDeleteAltLogo" value="">
    <script type="text/javascript">
        <!--
        document.module_configuration.enctype = "multipart/form-data";

        function deleteAltLog(sConfVar) {
            document.module_configuration.fnc.value = "deleteMollieAltLogo";
            document.module_configuration.mollieDeleteAltLogo.value = sConfVar;
        }
        -->
    </script>
    [{if $oView->mollieHasUploadError()}]
        <dl class="mollieAltLogoError">
            <dt>
                <fieldset class="refundError message">[{$oView->mollieGetUploadError()}]</fieldset>
            </dt>
        </dl>
    [{/if}]
    [{foreach from=$oView->molliePaymentMethods() key=sPaymentId item=sPaymentTitle}]
        [{assign var="sMollieAltLogoVarName" value="sMollie"|cat:$sPaymentId|cat:"AltLogo"}]
        [{assign var="sMollieAltLogoCurrentValue" value=$oView->mollieGetConfiguredAltLogoValue($sMollieAltLogoVarName)}]
        <dl class="mollieAltLogo">
            <dt>
                <input type="file" name="[{$sMollieAltLogoVarName}]">
                [{oxinputhelp ident="HELP_SHOP_MODULE_`$module_var`"}]
            </dt>
            <dd style="white-space: nowrap;">
                <div class="mollieAltLogoLabel">
                    [{oxmultilang ident="MOLLIE_ALTLOGO_LABEL"}] [{$sPaymentTitle}]
                </div>
                [{if $sMollieAltLogoCurrentValue}]
                    [{assign var="sMolliePicPath" value='out/img/'|cat:$sMollieAltLogoCurrentValue}]
                    <img class="mollie-payment-icon" src="[{$oViewConf->getModuleUrl('molliepayment', $sMolliePicPath)}]">
                    <div class="mollieAltLogoValue">[{oxmultilang ident="MOLLIE_ALTLOGO_FILENAME"}]: [{$sMollieAltLogoCurrentValue}]</div>
                    <button onclick="deleteAltLog('[{$sMollieAltLogoVarName}]')">[{oxmultilang ident="MOLLIE_ALTLOGO_DELETE"}]</button>
                [{/if}]
            </dd>
            <div class="spacer"></div>
        </dl>
    [{/foreach}]
    [{elseif $module_var == 'iMollieCronSecondChanceTimeDiff'}]
    <dl>
        <dt>
            <select class="select" name="confselects[[{$module_var}]]" [{ $readonly }]>
                [{foreach from=$oView->mollieDayDiffs(1, 14) item=iDayDiff}]
                <option value="[{$iDayDiff}]" [{if $confselects.$module_var == $iDayDiff}]selected[{/if}]>[{$iDayDiff}]&nbsp;[{if $iDayDiff == 1}][{oxmultilang ident="MOLLIE_DAY"}][{else}][{oxmultilang ident="MOLLIE_DAYS"}][{/if}]</option>
                [{/foreach}]
            </select>
            [{oxinputhelp ident="HELP_SHOP_MODULE_`$module_var`"}]
        </dt>
        <dd>
            [{oxmultilang ident="SHOP_MODULE_`$module_var`"}]
        </dd>
    </dl>
    [{elseif $module_var == 'iMollieCronFinishOrdersDays'}]
    <dl>
        <dt>
            <select class="select" name="confselects[[{$module_var}]]" [{ $readonly }]>
                [{foreach from=$oView->mollieDayDiffs(1, 28) item=iDayDiff}]
                <option value="[{$iDayDiff}]" [{if $confselects.$module_var == $iDayDiff}]selected[{/if}]>[{$iDayDiff}]&nbsp;[{if $iDayDiff == 1}][{oxmultilang ident="MOLLIE_DAY"}][{else}][{oxmultilang ident="MOLLIE_DAYS"}][{/if}]</option>
                [{/foreach}]
            </select>
            [{oxinputhelp ident="HELP_SHOP_MODULE_`$module_var`"}]
        </dt>
        <dd>
            [{oxmultilang ident="SHOP_MODULE_`$module_var`"}]
        </dd>
    </dl>
[{else}]
    [{$smarty.block.parent}]
[{/if}]
