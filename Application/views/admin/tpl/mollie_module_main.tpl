[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign box="box"}]

[{if $updatenav}]
    [{oxscript add="top.oxid.admin.reloadNavigation('`$shopid`');" priority=10}]
[{/if}]

<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{$oxid}]">
    <input type="hidden" name="cl" value="module_main">
    <input type="hidden" name="editlanguage" value="[{$editlanguage}]">
</form>

[{oxscript include="js/libs/jquery.min.js"}]
[{oxscript include="js/libs/jquery-ui.min.js"}]

[{if $oModule}]
    <table cellspacing="10" width="98%">
        <tr>
            <td width="245" valign="top">
                [{if $oModule->getInfo('thumbnail')}]
                    <img src="[{$oViewConf->getBaseDir()}]/modules/[{$oModule->getModulePath()}]/[{$oModule->getInfo('thumbnail')}]" hspace="20" vspace="10"></td>
                [{else}]
                    <img src="[{$oViewConf->getResourceUrl()}]bg/module.png" hspace="20" vspace="10">
                [{/if}]
            </td>
            <td width="" valign="top">
                <h1 style="color:#000;font-size:25px;">[{$oModule->getTitle()}]</h1>
                <p>[{$oModule->getDescription()}]</p>
                <hr>

                <dl class="moduleDesc clear">
                    <dt>[{oxmultilang ident="MODULE_VERSION"}]</dt>
                    <dd>[{$oModule->getInfo('version')|default:'-'}] [{if $oView->mollieShowOldVersionWarning()}] <span style="color:red;margin-left: 1rem;"><strong>[{oxmultilang ident="MOLLIE_MODULE_VERSION_OUTDATED"}] [{$oView->mollieGetNewestReleaseVersion()}]</strong></span>[{/if}]</dd>

                    <dt>[{oxmultilang ident="MODULE_AUTHOR"}]</dt>
                    <dd>[{$oModule->getInfo('author')|default:'-'}]</dd>

                    <dt>[{oxmultilang ident="GENERAL_EMAIL"}]</dt>
                    <dd>
                        [{if $oModule->getInfo('email')}]
                            <a href="mailto:[{$oModule->getInfo('email')}]">[{$oModule->getInfo('email')}]</a>
                        [{else}]
                            -
                        [{/if}]
                    </dd>

                    <dt>[{oxmultilang ident="GENERAL_URL"}]</dt>
                    <dd>
                        [{if $oModule->getInfo('url')}]
                            <a href="[{$oModule->getInfo('url')}]" target="_blank">[{$oModule->getInfo('url')}]</a>
                        [{else}]
                            -
                        [{/if}]
                    </dd>
                </dl>

                <br>
                <h3>[{oxmultilang ident="MOLLIE_SUPPORT_HEADER"}]</h3>

                [{if $oView->mollieMailHasBeenSent() == true}]
                    <span style="color:green;">[{oxmultilang ident="MOLLIE_SUPPORT_EMAIL_SENT"}]</span>
                [{else}]
                    <script>
                        function mollieIsSubmitFormComplete() {
                            if (document.getElementById('mollie_support_name').value.trim() == "") {
                                return false;
                            }
                            if (document.getElementById('mollie_support_email').value.trim() == "") {
                                return false;
                            }
                            if (document.getElementById('mollie_support_subject').value.trim() == "") {
                                return false;
                            }
                            if (document.getElementById('mollie_support_enquiry').value.trim() == "") {
                                return false;
                            }
                            return true;
                        }

                        function mollieCheckSubmitForm() {
                            var formIsComplete = mollieIsSubmitFormComplete();
                            document.getElementById("mollie_required_warning").style.display = "none";
                            if (formIsComplete === false) {
                                //alert('NOPE');
                                document.getElementById("mollie_required_warning").style.display = "";
                                ///@TODO: Show warning box
                            }
                            return formIsComplete;
                        }
                    </script>
                    <form name="myedit" id="myedit" action="[{$oViewConf->getSelfLink()}]" method="post" onsubmit="return mollieCheckSubmitForm();">
                        [{$oViewConf->getHiddenSid()}]
                        <input type="hidden" name="cl" value="module_main">
                        <input type="hidden" name="updatelist" value="1">
                        <input type="hidden" name="oxid" value="[{$oModule->getId()}]">
                        <input type="hidden" name="fnc" value="mollieSendSupportEnquiry">
                        <table cellspacing="0" cellpadding="0" border="0">
                            <tr id="mollie_required_warning" style="display: none;">
                                <td colspan="2"><div style="color: #D8000C;background-color: #FFD2D2;padding: 10px;margin-bottom: 10px;">[{oxmultilang ident="MOLLIE_SUPPORT_REQUIRED_FIELDS"}]</div></td>
                            </tr>
                            <tr>
                                <td class="edittext" width="80"><span style="color:red;">*</span>&nbsp;[{oxmultilang ident="MOLLIE_SUPPORT_FORM_NAME"}]</td>
                                <td class="edittext" width="250"><input id="mollie_support_name" type="text" placeholder="[{oxmultilang ident="MOLLIE_SUPPORT_FORM_NAME"}]" class="editinput" size="100" maxlength="255" name="support[name]" value=""></td>
                            </tr>
                            <tr>
                                <td class="edittext" width="80"><span style="color:red;">*</span>&nbsp;[{oxmultilang ident="MOLLIE_SUPPORT_FORM_EMAIL"}]</td>
                                <td class="edittext" width="250"><input id="mollie_support_email" type="text" class="editinput" size="100" maxlength="255" name="support[email]" value=""></td>
                            </tr>
                            <tr>
                                <td class="edittext" width="80"><span style="color:red;">*</span>&nbsp;[{oxmultilang ident="MOLLIE_SUPPORT_FORM_SUBJECT"}]</td>
                                <td class="edittext" width="250"><input id="mollie_support_subject" type="text" placeholder="[{oxmultilang ident="MOLLIE_SUPPORT_FORM_SUBJECT"}]" class="editinput" size="100" maxlength="255" name="support[subject]" value=""></td>
                            </tr>
                            <tr>
                                <td class="edittext" width="80"><span style="color:red;">*</span>&nbsp;[{oxmultilang ident="MOLLIE_SUPPORT_FORM_ENQUIRY"}]</td>
                                <td class="edittext" width="250"><textarea id="mollie_support_enquiry" placeholder="[{oxmultilang ident="MOLLIE_SUPPORT_FORM_ENQUIRY_PLACEHOLDER"}]" name="support[enquiry]" rows="9" cols="101" style="border: 1px solid #CCCCCC;border-radius: 4px;padding: 3px;"></textarea></td>
                            </tr>
                        </table>
                        <input type="submit" id="submit_support" class="saveButton" value="[{oxmultilang ident="MOLLIE_SUPPORT_FORM_SUBMIT"}]">
                    </form>
                [{/if}]
            </td>

            <td width="25" style="border-right: 1px solid #ddd;">

            </td>
            <td width="260" valign="top">
                [{if !$oModule->hasMetadata() && !$oModule->isRegistered()}]
                    <div class="info">
                        [{oxmultilang ident="MODULE_ENABLEACTIVATIONTEXT"}]
                    </div>
                [{/if}]
                [{if !$_sError}]
                    [{if $oModule->hasMetadata() || $oModule->isRegistered()}]
                        <form name="myedit" id="myedit" action="[{$oViewConf->getSelfLink()}]" method="post">
                            <div>
                                [{$oViewConf->getHiddenSid()}]
                                <input type="hidden" name="cl" value="module_main">
                                <input type="hidden" name="updatelist" value="1">
                                <input type="hidden" name="oxid" value="[{$oModule->getId()}]">
                                [{if !$oView->isDemoShop()}]
                                    [{if $oModule->hasMetadata()}]
                                        [{if $oModule->isActive()}]
                                            <input type="hidden" name="fnc" value="deactivateModule">
                                            <div align="center">
                                                <input type="submit" id="module_deactivate" class="saveButton" value="[{oxmultilang ident="MODULE_DEACTIVATE"}]">
                                            </div>
                                        [{else}]
                                            <input type="hidden" name="fnc" value="activateModule">
                                            <div align="center">
                                                <input type="submit" id="module_activate" class="saveButton" value="[{oxmultilang ident="MODULE_ACTIVATE"}]">
                                            </div>
                                        [{/if}]
                                    [{/if}]
                                [{else}]
                                    [{ oxmultilang ident="MODULE_ACTIVATION_NOT_POSSIBLE_IN_DEMOMODE" }]
                                [{/if}]
                            </div>
                        </form>
                    [{/if}]
                [{/if}]
            </td>
        </tr>
    </table>
[{/if}]

[{include file="bottomnaviitem.tpl"}]
[{include file="bottomitem.tpl"}]
