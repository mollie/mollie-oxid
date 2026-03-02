[{if $edit !== null && $edit->isMolliePaymentMethod() == 1}]
    [{assign var="paymentModel" value=$edit->getMolliePaymentModel() }]
    <tr>
        <td class="edittext" colspan="2">
            [{if $paymentModel->isMethodDeprecated() === false}]
                <b>[{oxmultilang ident="MOLLIE_IS_MOLLIE"}]</b>
            [{else}]
                <script type="text/javascript">
                    function handleDeprecatedMethod() {
                        let checkboxes = document.getElementsByName("editval[oxpayments__oxactive]");
                        if (checkboxes.length > 0) {
                            checkboxes[0].checked = false;
                            checkboxes[0].disabled = true;
                        }
                    }
                    setTimeout(handleDeprecatedMethod, 100);
                </script>
                <b style="color: red;">[{oxmultilang ident="MOLLIE_PAYMENT_DISABLED_ACTIVATION"}]</b>
            [{/if}]
        </td>
    </tr>
    [{if method_exists($oView, 'mollieIsTokenConfigured') && $oView->mollieIsTokenConfigured() === false }]
        <tr>
            <td class="edittext" colspan="2">
                <b style="color: red;">[{oxmultilang ident="MOLLIE_TOKEN_NOT_CONFIGURED"}]</b>
            </td>
        </tr>
    [{elseif $paymentModel->isMolliePaymentActiveInGeneral() === false}]
        <tr>
            <td class="edittext" colspan="2">
                <b style="color: red;">[{oxmultilang ident="MOLLIE_IS_METHOD_ACTIVATED"}]</b>
            </td>
        </tr>
    [{/if}]
    <tr>
        <td class="edittext" width="70">
            [{oxmultilang ident="MOLLIE_PAYMENT_DESCRIPTION"}]
        </td>
        <td class="edittext">
            <input type="text" class="editinput" size="25" name="mollie[payment_description]" value="[{$paymentModel->getConfigParam('payment_description')}]" [{$readonly}]>
            [{oxinputhelp ident="MOLLIE_PAYMENT_DESCRIPTION_HELP"}]
        </td>
    </tr>
    [{if $paymentModel->isOrderExpirySupported() === true}]
    <tr>
        <td class="edittext" width="70">
            [{oxmultilang ident="MOLLIE_ORDER_EXPIRY"}]
        </td>
        <td class="edittext">
            <select name="mollie[expiryDays]" [{$readonly}]>
                [{foreach from=$oView->mollieGetExpiryDayOptions() item=title key=days}]
                    <option value="[{$days}]" [{if $paymentModel->getExpiryDays() == $days}]selected[{/if}]>[{$title}]</option>
                [{/foreach}]
            </select>
            [{oxinputhelp ident="MOLLIE_ORDER_EXPIRY_HELP"}]
        </td>
    </tr>
    [{/if}]
    [{if $paymentModel->getAvailableCaptureMethods()}]
        <tr>
            <td class="edittext" width="70">
                [{oxmultilang ident="MOLLIE_CAPTURE_METHOD"}]
            </td>
            <td  class="edittext" width="150">
                <script type="text/javascript">
                    <!--
                    function mollieHandleCaptureMethodChange(oSelect)
                    {
                        [{if 'automatic_capture'|in_array:$paymentModel->getAvailableCaptureMethods()}]
                            let oDaysRow = document.getElementById('mollieAutomaticCaptureDays');
                            let sDisplay = 'none';
                            if (oSelect.value === 'automatic_capture') {
                                sDisplay = '';
                            }
                            oDaysRow.style.display = sDisplay;
                        [{/if}]
                    }
                    -->
                </script>
                <select id="mollieSelectCaptureMethod" name="mollie[capture_method]" style="width:177px;" onchange="mollieHandleCaptureMethodChange(this)" [{$readonly}]>
                    [{foreach from=$paymentModel->getAvailableCaptureMethods() item=captureMode}]
                        <option value="[{$captureMode}]" [{if $paymentModel->getConfigParam('capture_method') == $captureMode}]selected[{/if}]>[{oxmultilang ident="MOLLIE_"|cat:$captureMode|upper}]</option>
                    [{/foreach}]
                </select>
                [{oxinputhelp ident="MOLLIE_CAPTURE_METHOD_HELP"}]
            </td>

        </tr>
        [{if 'automatic_capture'|in_array:$paymentModel->getAvailableCaptureMethods()}]
            <tr id="mollieAutomaticCaptureDays" [{if $paymentModel->getConfigParam('capture_method') != 'automatic_capture' }]style="display:none;"[{/if}]>
                <td class="edittext" width="70">
                    [{oxmultilang ident="MOLLIE_CAPTURE_DAYS"}]
                </td>
                <td class="edittext">
                    <select name="mollie[captureDays]" [{$readonly}]>
                        [{foreach from=$oView->mollieGetAutomaticCaptureDays() item=title key=days}]
                        <option value="[{$days}]" [{if $paymentModel->getCaptureDays() == $days}]selected[{/if}]>[{$title}]</option>
                        [{/foreach}]
                    </select>
                    [{oxinputhelp ident="MOLLIE_CAPTURE_DAYS_HELP"}]
                </td>
            </tr>
        [{/if}]
    [{/if}]
    [{if $paymentModel->getCustomConfigTemplate() !== false}]
        [{include file=$paymentModel->getCustomConfigTemplate()}]
    [{/if}]
    <tr>
        <td class="edittext" colspan="2">
            &nbsp;<div style="display: none;" id="mollie_payment_min_max">
                [{assign var="oFrom" value=$paymentModel->getMollieFromAmount() }]
                [{assign var="oTo" value=$paymentModel->getMollieToAmount() }]
                [{if $oFrom}]<br>
                    [{oxmultilang ident="MOLLIE_PAYMENT_LIMITATION"}]:<br>
                    [{oxmultilang ident="MOLLIE_PAYMENT_LIMITATION_FROM"}] [{$oFrom->value}] [{$oFrom->currency}] [{oxmultilang ident="MOLLIE_PAYMENT_LIMITATION_TO"}]
                    [{if $oTo != false}]
                        [{$oTo->value}] [{$oTo->currency}]
                    [{else}]
                        [{oxmultilang ident="MOLLIE_PAYMENT_LIMITATION_UNLIMITED"}]
                    [{/if}]
                [{/if}]
            </div>
            <script>
                function appendMinMaxInfo() {
                    var minMaxInfo = document.getElementById("mollie_payment_min_max");
                    var clone = minMaxInfo.cloneNode(true);
                    clone.style.display = "";
                    document.getElementById("helpText_HELP_PAYMENT_MAIN_AMOUNT").parentNode.appendChild(clone);
                }
                setTimeout(appendMinMaxInfo, 100);
            </script>
        </td>
    </tr>
[{/if}]
[{$smarty.block.parent}]
