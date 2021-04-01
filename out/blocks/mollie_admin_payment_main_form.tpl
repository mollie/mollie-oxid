[{if $edit !== null && $edit->isMolliePaymentMethod() == 1}]
    [{assign var="paymentModel" value=$edit->getMolliePaymentModel() }]
    <tr>
        <td class="edittext" colspan="2">
            <b>[{oxmultilang ident="MOLLIE_IS_MOLLIE"}]</b>
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
    [{elseif $paymentModel->isMolliePaymentActive() === false}]
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
                }
                -->
            </script>
            <select name="mollie[api]" onchange="mollieHandleApiChange(this)" [{$readonly}]>
                <option value="payment" [{if $paymentModel->getApiMethod() == 'payment'}]selected[{/if}]>Payment API</option>
                <option value="order" [{if $paymentModel->getApiMethod() == 'order'}]selected[{/if}]>Order API</option>
            </select>
            <span id="mollie_apihint_payment" class="mollieApiHint" [{if $paymentModel->getApiMethod() != 'payment'}]style="display:none;"[{/if}]>
                [{oxmultilang ident="MOLLIE_PAYMENT_API_LINK_1"}] <a href="https://docs.mollie.com/payments/overview" target=”_blank” style="text-decoration: underline;">[{oxmultilang ident="MOLLIE_PAYMENT_API_LINK_2"}]</a>
            </span>
            <span id="mollie_apihint_order" class="mollieApiHint" [{if $paymentModel->getApiMethod() != 'order'}]style="display:none;"[{/if}]>
                [{oxmultilang ident="MOLLIE_ORDER_API_LINK_1"}] <a href="https://docs.mollie.com/orders/overview" target=”_blank” style="text-decoration: underline;">[{oxmultilang ident="MOLLIE_ORDER_API_LINK_2"}]</a>
            </span>
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
    [{if $paymentModel->getCustomConfigTemplate() !== false}]
        [{include file=$paymentModel->getCustomConfigTemplate()}]
    [{/if}]
    <tr><td class="edittext" colspan="2">&nbsp;</td></tr>
[{/if}]
[{$smarty.block.parent}]
