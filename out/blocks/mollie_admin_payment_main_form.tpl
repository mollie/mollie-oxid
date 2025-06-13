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
    [{elseif $paymentModel->isMolliePaymentActiveInGeneral() === false}]
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
            <script type="text/javascript">
                <!--
                function mollieCustomApiChange()
                {
                    /* Can be redefined with custom functionality in a paymentconfig template */
                }

                function mollieHandleApiChange(oSelect)
                {
                    var aElements = document.getElementsByClassName("mollieApiHint");
                    if (typeof aElements !== undefined && aElements.length > 0) {
                        for (var i = 0; i < aElements.length; i++) {
                            if (aElements[i].id != "mollie_apihint_" + oSelect.value) {
                                aElements[i].style.display = "none";
                            } else {
                                aElements[i].style.display = "";
                            }
                        }
                    }

                    if (oSelect.value === 'payment') {
                        mollieToggleDisplayByClass('mollieOnlyPaymentApi', '');
                        mollieToggleDisplayByClass('mollieOnlyOrderApi', 'none');
                    } else {
                        mollieToggleDisplayByClass('mollieOnlyPaymentApi', 'none');
                        mollieToggleDisplayByClass('mollieOnlyOrderApi', '');
                    }

                    mollieCustomApiChange(oSelect.value);
                }

                function mollieToggleDisplayByClass(className, display)
                {
                    var aElements = document.getElementsByClassName(className);
                    if (typeof aElements !== undefined && aElements.length > 0) {
                        for (var i = 0; i < aElements.length; i++) {
                            aElements[i].style.display = display;
                        }
                    }
                }
                -->
            </script>
            <select name="mollie[api]" onchange="mollieHandleApiChange(this)" [{$readonly}]>
                <option value="payment" [{if $paymentModel->getApiMethod() == 'payment'}]selected[{/if}]>Payment API</option>
                <option value="order" [{if $paymentModel->getApiMethod() == 'order'}]selected[{/if}]>Order API</option>
            </select>
            <span id="mollie_apihint_payment" class="mollieApiHint" [{if $paymentModel->getApiMethod() != 'payment'}]style="display:none;"[{/if}]>
                [{oxmultilang ident="MOLLIE_PAYMENT_API_LINK_1"}] <a href="https://docs.mollie.com/docs/accepting-payments" target=”_blank” style="text-decoration: underline;">[{oxmultilang ident="MOLLIE_PAYMENT_API_LINK_2"}]</a>
            </span>
            <span id="mollie_apihint_order" class="mollieApiHint" [{if $paymentModel->getApiMethod() != 'order'}]style="display:none;"[{/if}]>
                [{oxmultilang ident="MOLLIE_ORDER_API_LINK_1"}] <a href="https://docs.mollie.com/orders/overview" target=”_blank” style="text-decoration: underline;">[{oxmultilang ident="MOLLIE_ORDER_API_LINK_2"}]</a>
            </span>
        </td>
    </tr>
    <tr class="mollieOnlyPaymentApi" [{if $paymentModel->getApiMethod() != 'payment'}]style="display:none;"[{/if}]>
        <td class="edittext" width="70">
            [{oxmultilang ident="MOLLIE_PAYMENT_DESCRIPTION"}]
        </td>
        <td class="edittext">
            <input type="text" class="editinput" size="25" name="mollie[payment_description]" value="[{$paymentModel->getConfigParam('payment_description')}]" [{$readonly}]>
            [{oxinputhelp ident="MOLLIE_PAYMENT_DESCRIPTION_HELP"}]
        </td>
    </tr>
    [{/if}]
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
        <tr class="mollieOnlyPaymentApi" [{if $paymentModel->getApiMethod() != 'payment'}]style="display:none;"[{/if}]>
            <td class="edittext" width="70">
                [{oxmultilang ident="MOLLIE_CAPTURE_METHOD"}]
            </td>
            <td  class="edittext" width="150">
                <script type="text/javascript">
                    <!--
                    function mollieHandleCaptureMethodChange(oSelect, sApiMethod)
                    {
                        [{if 'automatic_capture'|in_array:$paymentModel->getAvailableCaptureMethods()}]
                            let oDaysRow = document.getElementById('mollieAutomaticCaptureDays');
                            let sDisplay = 'none';
                            if (sApiMethod !== "order" && oSelect.value === 'automatic_capture') {
                                sDisplay = '';
                            }
                            oDaysRow.style.display = sDisplay;
                        [{/if}]
                    }

                    function mollieCustomApiChange(sApiMethod)
                    {
                        let oSelect = document.getElementById('mollieSelectCaptureMethod');
                        mollieHandleCaptureMethodChange(oSelect, sApiMethod);
                    }
                    -->
                </script>
                <select id="mollieSelectCaptureMethod" name="mollie[capture_method]" style="width:177px;" onchange="mollieHandleCaptureMethodChange(this, '')" [{$readonly}]>
                    [{foreach from=$paymentModel->getAvailableCaptureMethods() item=captureMode}]
                        <option value="[{$captureMode}]" [{if $paymentModel->getConfigParam('capture_method') == $captureMode}]selected[{/if}]>[{oxmultilang ident="MOLLIE_"|cat:$captureMode|upper}]</option>
                    [{/foreach}]
                </select>
                [{oxinputhelp ident="MOLLIE_CAPTURE_METHOD_HELP"}]
            </td>

        </tr>
        [{if 'automatic_capture'|in_array:$paymentModel->getAvailableCaptureMethods()}]
            <tr id="mollieAutomaticCaptureDays" class="mollieOnlyPaymentApi" [{if $paymentModel->getApiMethod() != 'payment' || $paymentModel->getConfigParam('capture_method') != 'automatic_capture' }]style="display:none;"[{/if}]>
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
