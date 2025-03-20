[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

<script type="text/javascript">
    <!--
    function toggleRefundType(oElem)
    {
        var quantityDisplay = oElem.value === 'quantity' ? '' : 'none';
        var amountDisplay = oElem.value === 'amount' ? '' : 'none';

        setDisplayStyleForClassName('refundQuantity', quantityDisplay);
        setDisplayStyleForClassName('refundAmount', amountDisplay);
    }

    function setDisplayStyleForClassName(className, displayStyle)
    {
        var aElements = document.getElementsByClassName(className);
        for (i = 0; i < aElements.length; i++) {
            aElements[i].style.display = displayStyle;
        }
    }

    function copyRefundDescription(oElem)
    {
        var aFormElements = document.getElementsByClassName("refund_description");
        if (typeof aFormElements !== undefined && aFormElements.length > 0) {
            for (var i = 0; i < aFormElements.length; i++) {
                aFormElements[i].value = oElem.value;
            }
        }
    }

    //-->
</script>
<style>
    .refundTable TD {
        padding-top: 10px;
        padding-bottom: 10px;
    }
    TD.borderTop {
        border-top: 1px solid black!important;
    }
    FIELDSET {
        border-radius: 15px;
        margin-bottom: 20px;
        padding: 10px;
    }
    FIELDSET.fullRefund SPAN{
        margin-left: 2px;
    }
    FIELDSET .refundSubmit {
        margin-top: 15px;
    }
    .typeSelect {
        margin-bottom: 10px;
    }
    FIELDSET.refundError {
        background-color: #FF8282;
        color: black;
        border: 3px solid #F00000;
    }
    FIELDSET.refundNotice {
        background-color: #ffeeb5;
        border: 3px solid #FFE385;
    }
    FIELDSET.refundSuccess {
        background-color: #7aff9e;
        border: 3px solid #00b02f;
    }
    FIELDSET.message STRONG {
        display: block;
        margin-bottom: 10px;
    }
</style>

[{if $readonly}]
    [{assign var="readonly" value="readonly disabled"}]
[{else}]
    [{assign var="readonly" value=""}]
[{/if}]

<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{$oxid}]">
    <input type="hidden" name="cl" value="mollie_order_refund">
</form>

[{assign var="order" value=$oView->getOrder()}]
[{assign var="paymentType" value=$order->getPaymentType()}]
[{if $oView->isMollieOrder() === true}]
    <fieldset>
        <legend>[{oxmultilang ident="MOLLIE_PAYMENT_DETAILS"}]</legend>
        <table>
            <tr>
                <td class="edittext">
                    [{oxmultilang ident="MOLLIE_PAYMENT_TYPE"}]:
                </td>
                <td class="edittext">
                    [{$paymentType->oxpayments__oxdesc->value}]
                </td>
                <td class="edittext"></td>
            </tr>
            <tr>
                <td class="edittext">
                    [{oxmultilang ident="MOLLIE_TRANSACTION_ID"}]:
                </td>
                <td class="edittext">
                    [{$order->oxorder__oxtransid->value}]
                </td>
                <td class="edittext"></td>
            </tr>
            [{if $order->oxorder__mollieexternaltransid->value != ""}]
                <tr>
                    <td class="edittext">
                        [{oxmultilang ident="MOLLIE_EXTERNAL_TRANSACTION_ID"}]:
                    </td>
                    <td class="edittext">
                        [{$order->oxorder__mollieexternaltransid->value}]
                    </td>
                    <td class="edittext"></td>
                </tr>
            [{/if}]
        </table>
    </fieldset>
    [{if $oView->isTransactionUsable() === false}]
        <fieldset class="refundNotice message">
            <strong>[{oxmultilang ident="MOLLIE_NOTICE"}]</strong>
            [{oxmultilang ident="MOLLIE_TRANSACTION_NOT_USABLE"}]
        </fieldset>
    [{else}]
        [{if $oView->getErrorMessage() != false}]
            <fieldset class="refundError message">
                <strong>Error</strong>
                [{$oView->getErrorMessage()}]
            </fieldset>
        [{/if}]
        [{if $oView->wasRefundSuccessful() == true}]
            <fieldset class="refundSuccess message">
                [{oxmultilang ident="MOLLIE_REFUND_SUCCESSFUL"}]
            </fieldset>
        [{/if}]
        [{if $oView->wasCaptureSuccessful() == true}]
            <fieldset class="refundSuccess message">
                [{oxmultilang ident="MOLLIE_CAPTURE_SUCCESSFUL"}]
            </fieldset>
        [{/if}]

        [{assign var="blIsOrderRefundable" value=$oView->isOrderRefundable()}]
        [{if $blIsOrderRefundable == false}]
            <fieldset class="refundNotice message">
                <strong>[{oxmultilang ident="MOLLIE_NOTICE"}]</strong>
                [{oxmultilang ident="MOLLIE_ORDER_NOT_REFUNDABLE"}]
            </fieldset>
        [{/if}]

        [{assign var="blIsOrderPaid" value=$order->mollieIsPaid()}]
        [{if $blIsOrderPaid == false}]
            <fieldset class="refundNotice message">
                <strong>[{oxmultilang ident="MOLLIE_NOTICE"}]</strong>
                [{oxmultilang ident="MOLLIE_NOT_YET_PAID"}]
            </fieldset>
            [{assign var="blIsOrderRefundable" value=false}]
        [{/if}]

        [{if $oView->hasOrderVoucher() == true}]
            <fieldset class="refundNotice message">
                <strong>[{oxmultilang ident="MOLLIE_NOTICE"}]</strong>
                [{oxmultilang ident="MOLLIE_VOUCHERS_EXISTING"}]
            </fieldset>
        [{/if}]
        [{if $oView->orderNeedsManualCapture()}]
            <fieldset class="capturelist">
                <legend>[{oxmultilang ident="MOLLIE_CAPTURE_TITLE"}]</legend>
                [{if !$oView->getOrderCaptures()}]
                    <form name="capturelist" id="search" action="[{$oViewConf->getSelfLink()}]" method="post">
                        [{$oViewConf->getHiddenSid()}]
                        <input type="hidden" name="cl" value="mollie_order_refund">
                        <input type="hidden" name="oxid" value="[{$oxid}]">
                        <input type="hidden" name="fnc" value="captureOrder">

                        <span><label for="refund_description">[{oxmultilang ident="MOLLIE_CAPTURE_DESCRIPTION"}]:</label></span>
                        <input type="text" name="capture_partial" value="[{$edit->oxorder__oxtotalordersum->value}]" placeholder="[{$edit->oxorder__oxtotalordersum->value}]" maxlength="140" size="120"><br>
                        <input type="submit" value="[{oxmultilang ident="MOLLIE_CAPTURE_AMOUNT"}]" class="refundSubmit">
                    </form>
                [{/if}]
                [{if $oView->getOrderCaptures()}]
                    <table cellspacing="0" cellpadding="0" border="0" width="98%" class="refundTable">
                        <tr>
                            <td class="listheader first" height="15" width="10%">[{oxmultilang ident="MOLLIE_AMOUNT"}]</td>
                            <td class="listheader" width="10%">[{oxmultilang ident="MOLLIE_TRANSACTION_ID"}]</td>
                            <td class="listheader" width="10%">[{oxmultilang ident="MOLLIE_CAPTURE_ID"}]</td>
                            <td class="listheader" width="10%">[{oxmultilang ident="SHOP_MODULE_sMollieMode"}]</td>
                            <td class="listheader" width="10%">[{oxmultilang ident="MOLLIE_CAPTURE_STATUS"}]</td>
                        </tr>
                        [{foreach from=$oView->getOrderCaptures() item=listitem name=orderCaptures}]
                            <tr id="art.[{$smarty.foreach.orderCaptures.iteration}]">
                                [{if $listitem.isOrderarticle == false && $blBorderDrawn == false}]
                                    [{assign var="class" value=" borderTop"}]
                                    [{assign var="blBorderDrawn" value=true}]
                                [{/if}]
                                [{assign var="listclass" value=listitem$blWhite}]
                                <td valign="top" class="[{$listclass}][{$class}]" height="15">[{$oView->getFormatedPrice($listitem.amount)}]  <small>[{$edit->oxorder__oxcurrency->value}]</small></a></td>
                                <td valign="top" class="[{$listclass}][{$class}]">[{$listitem.paymentId|strip_tags}]</a></td>
                                <td valign="top" class="[{$listclass}][{$class}]">[{$listitem.captureId}]</td>
                                <td valign="top" class="[{$listclass}][{$class}]">[{$listitem.mode}]</td>
                                <td valign="top" class="[{$listclass}][{$class}]">[{$listitem.status}]</td>

                                [{if $listitem.isOrderarticle == false}]
                                    [{assign var="class" value=""}]
                                [{/if}]
                            </tr>
                            [{if $blWhite == "2"}]
                                [{assign var="blWhite" value=""}]
                            [{else}]
                                [{assign var="blWhite" value="2"}]
                            [{/if}]
                        [{/foreach}]
                    </table>
                [{/if}]
            </fieldset>
        [{/if}]
        [{if $order->mollieIsEligibleForPaymentFinish()}]
            <fieldset>
                <legend>[{oxmultilang ident="MOLLIE_SUBSEQUENT_ORDER_COMPLETION"}]</legend>
                [{oxmultilang ident="MOLLIE_ORDER_PAYMENT_URL"}]: <a href="[{$order->mollieGetPaymentFinishUrl()}]" target="_blank" style="text-decoration: underline;">[{$order->mollieGetPaymentFinishUrl()}]</a><br><br>
                <form action="[{$oViewConf->getSelfLink()}]" method="post">
                    [{$oViewConf->getHiddenSid()}]
                    <input type="hidden" name="cl" value="mollie_order_refund">
                    <input type="hidden" name="oxid" value="[{$oxid}]">
                    <input type="hidden" name="fnc" value="sendSecondChanceEmail">
                    <input type="submit" value="[{oxmultilang ident="MOLLIE_SEND_SECOND_CHANCE_MAIL"}]">
                    [{if $order->oxorder__molliesecondchancemailsent->value != "0000-00-00 00:00:00"}]
                        <span style="color: crimson;">[{oxmultilang ident="MOLLIE_SECOND_CHANCE_MAIL_ALREADY_SENT"}] ( [{$order->oxorder__molliesecondchancemailsent->value}] )</span>
                    [{/if}]
                </form>
            </fieldset>
        [{/if}]

        [{if $blIsOrderRefundable == true}]
            <fieldset class="fullRefund">
                <legend>[{oxmultilang ident="MOLLIE_FULL_REFUND"}]</legend>
                <form name="search" id="search" action="[{$oViewConf->getSelfLink()}]" method="post">
                    [{$oViewConf->getHiddenSid()}]
                    <input type="hidden" name="cl" value="mollie_order_refund">
                    <input type="hidden" name="oxid" value="[{$oxid}]">
                    <input type="hidden" name="fnc" value="fullRefund">
                    [{assign var="blIsFullRefundAvailable" value=$oView->isFullRefundAvailable()}]
                    [{if $blIsFullRefundAvailable == true}]
                        <span>[{oxmultilang ident="MOLLIE_FULL_REFUND_TEXT"}]: [{$oView->getFormatedPrice($edit->oxorder__oxtotalordersum->value)}] <small>[{$edit->oxorder__oxcurrency->value}]</small></span><br><br>
                    [{else}]
                        <input type="hidden" name="refundRemaining" value="1">
                        <span>[{oxmultilang ident="MOLLIE_REFUND_REMAINING"}]: [{$oView->getFormatedPrice($oView->getRemainingRefundableAmount())}] <small>[{$edit->oxorder__oxcurrency->value}]</small></span><br><br>
                    [{/if}]
                    <span><label for="refund_description">[{oxmultilang ident="MOLLIE_REFUND_DESCRIPTION"}]:</label></span>
                    <input type="text" name="refund_description" value="" placeholder="[{oxmultilang ident="MOLLIE_REFUND_DESCRIPTION_PLACEHOLDER"}]" maxlength="140" size="120"><br>
                    <input type="submit" value="[{oxmultilang ident="MOLLIE_REFUND_SUBMIT"}]" class="refundSubmit">
                </form>
            </fieldset>
        [{/if}]
    [{/if}]
[{/if}]

[{if $blIsOrderPaid == true}]
    <fieldset>
        <legend>[{oxmultilang ident="MOLLIE_PARTIAL_REFUND"}]</legend>
        [{if $oView->isMollieOrder() === false}]
            [{oxmultilang ident="MOLLIE_NO_MOLLIE_PAYMENT"}]
        [{else}]
            [{if $blIsOrderRefundable == true}]
                <label for="refund_description">[{oxmultilang ident="MOLLIE_REFUND_DESCRIPTION"}]:</label>
                <input type="text" name="refund_description" value="" placeholder="[{oxmultilang ident="MOLLIE_REFUND_DESCRIPTION_PLACEHOLDER"}]" maxlength="140" size="120" onkeyup="copyRefundDescription(this)"><br><br>
            [{/if}]
            <table cellspacing="0" cellpadding="0" border="0" width="98%" class="refundTable">
                <tr>
                    <td class="listheader first" height="15" width="10%">[{oxmultilang ident="GENERAL_ITEMNR"}]</td>
                    <td class="listheader" width="10%">[{oxmultilang ident="GENERAL_TITLE"}]</td>
                    <td class="listheader" width="10%">[{oxmultilang ident="MOLLIE_HEADER_SINGLE_PRICE"}]</td>
                    <td class="listheader" width="10%">[{oxmultilang ident="GENERAL_ATALL"}]</td>
                    <td class="listheader" width="10%">[{oxmultilang ident="ORDER_ARTICLE_MWST"}]</td>
                    <td class="listheader" width="10%">[{oxmultilang ident="MOLLIE_HEADER_ORDERED"}]</td>
                    <td class="listheader" width="20%">[{oxmultilang ident="MOLLIE_HEADER_REFUNDED"}]</td>
                    [{if $blIsOrderRefundable == true}]
                        [{if $oView->getRefundType() == 'quantity'}]
                            <td class="listheader" width="5%">[{oxmultilang ident="MOLLIE_REFUND_QUANTITY"}]</td>
                        [{else}]
                            <td class="listheader" width="5%">[{oxmultilang ident="MOLLIE_REFUND_AMOUNT"}]</td>
                        [{/if}]
                    [{/if}]
                </tr>
                [{assign var="blWhite" value=""}]
                [{assign var="class" value=""}]
                [{assign var="blBorderDrawn" value=false}]
                [{foreach from=$oView->getRefundItems() item=listitem name=orderArticles}]
                    <tr id="art.[{$smarty.foreach.orderArticles.iteration}]">
                        [{if $listitem.isOrderarticle == false && $blBorderDrawn == false}]
                            [{assign var="class" value=" borderTop"}]
                            [{assign var="blBorderDrawn" value=true}]
                        [{/if}]
                        [{assign var="listclass" value=listitem$blWhite}]
                        <td valign="top" class="[{$listclass}][{$class}]" height="15">[{$listitem.artnum}]</a></td>
                        <td valign="top" class="[{$listclass}][{$class}]">[{$listitem.title|strip_tags}]</a></td>
                        <td valign="top" class="[{$listclass}][{$class}]">[{$oView->getFormatedPrice($listitem.singlePrice)}] <small>[{$edit->oxorder__oxcurrency->value}]</small></td>
                        <td valign="top" class="[{$listclass}][{$class}]">[{$oView->getFormatedPrice($listitem.totalPrice)}] <small>[{$edit->oxorder__oxcurrency->value}]</small></td>
                        <td valign="top" class="[{$listclass}][{$class}]">[{$listitem.vat}]</td>
                        <td valign="top" class="[{$listclass}][{$class}]">
                            [{if $oView->getRefundType() == 'quantity'}]
                                <span class="refundQuantity">[{$listitem.quantity}]</span>
                            [{else}]
                                <span class="refundAmount">[{$oView->getFormatedPrice($listitem.totalPrice)}] <small>[{$edit->oxorder__oxcurrency->value}]</small></span>
                            [{/if}]
                        </td>
                        <td valign="top" class="[{$listclass}][{$class}]">
                            [{if $oView->getRefundType() == 'quantity'}]
                                <span class="refundQuantity">[{$listitem.quantityRefunded}]</span>
                            [{else}]
                                <span>[{$oView->getFormatedPrice($listitem.amountRefunded)}] <small>[{$edit->oxorder__oxcurrency->value}]</small></span>
                            [{/if}]
                        </td>
                        [{if $blIsOrderRefundable == true}]
                            [{if $oView->getRefundType() == 'quantity'}]
                                <td valign="top" class="[{$listclass}][{$class}]" nowrap>
                                    [{if $listitem.isOrderarticle == true}]
                                        <span class="refundQuantity">
                                            <form action="[{$oViewConf->getSelfLink()}]" method="post">
                                                [{$oViewConf->getHiddenSid()}]
                                                <input type="hidden" name="cl" value="mollie_order_refund">
                                                <input type="hidden" name="oxid" value="[{$oxid}]">
                                                <input type="hidden" name="fnc" value="partialRefund">
                                                <input type="hidden" name="refund_description" value="" class="refund_description">
                                                <input type="text" name="aOrderArticles[[{$listitem.id}]][refund_quantity]" value="[{$listitem.refundableQuantity}]" class="listedit" [{if $listitem.refundableQuantity <= 0}]disabled[{/if}]>
                                                <input type="submit" value="[{oxmultilang ident="MOLLIE_REFUND_SUBMIT"}]" [{if $listitem.refundableQuantity <= 0}]disabled[{/if}]>
                                            </form>
                                        </span>
                                    [{/if}]
                                </td>
                            [{else}]
                                <td valign="top" class="[{$listclass}][{$class}]" nowrap>
                                    <span [{if $listitem.isOrderarticle == true && $oView->isQuantityAvailable() == true}]class="refundAmount"[{/if}]>
                                        <form action="[{$oViewConf->getSelfLink()}]" method="post">
                                            [{$oViewConf->getHiddenSid()}]
                                            <input type="hidden" name="cl" value="mollie_order_refund">
                                            <input type="hidden" name="oxid" value="[{$oxid}]">
                                            <input type="hidden" name="fnc" value="partialRefund">
                                            <input type="hidden" name="refund_description" value="" class="refund_description">
                                            <input type="text" name="aOrderArticles[[{$listitem.id}]][refund_amount]" value="[{$listitem.refundableAmount}]" class="listedit" [{if $listitem.refundableAmount <= 0 || $listitem.isPartialAllowed == false}]disabled[{/if}]>
                                            <small>[{$edit->oxorder__oxcurrency->value}]</small>
                                            <input type="submit" value="[{oxmultilang ident="MOLLIE_REFUND_SUBMIT"}]" [{if $listitem.refundableAmount <= 0 || $listitem.isPartialAllowed == false}]disabled[{/if}]>
                                        </form>
                                    </span>
                                </td>
                            [{/if}]
                        [{/if}]
                        [{if $listitem.isOrderarticle == false}]
                            [{assign var="class" value=""}]
                        [{/if}]
                    </tr>
                    [{if $blWhite == "2"}]
                        [{assign var="blWhite" value=""}]
                    [{else}]
                        [{assign var="blWhite" value="2"}]
                    [{/if}]
                [{/foreach}]
            </table><br>
            [{if $blIsOrderRefundable == true}]
                <form id="free_refund" action="[{$oViewConf->getSelfLink()}]" method="post">
                    [{$oViewConf->getHiddenSid()}]
                    <input type="hidden" name="cl" value="mollie_order_refund">
                    <input type="hidden" name="oxid" value="[{$oxid}]">
                    <input type="hidden" name="fnc" value="freeRefund">
                    <input type="hidden" name="refund_description" value="" class="refund_description">
                    <label for="refund_description">[{oxmultilang ident="MOLLIE_REFUND_FREE_AMOUNT"}]:</label>
                    <span>
                        <input type="text" name="free_amount" placeholder="0.00" value="" class="listedit">
                        <small>[{$edit->oxorder__oxcurrency->value}]</small>
                        <input type="submit" value="[{oxmultilang ident="MOLLIE_REFUND_SUBMIT"}]">
                    </span><br><br>
                    <span>[{oxmultilang ident="MOLLIE_REFUND_FREE_1"}] [{$oView->getFormatedPrice($edit->oxorder__oxtotalordersum->value)}] <small>[{$edit->oxorder__oxcurrency->value}]</small> [{oxmultilang ident="MOLLIE_REFUND_FREE_2"}] [{$oView->getAmountRefunded()}] <small>[{$edit->oxorder__oxcurrency->value}]</small> [{oxmultilang ident="MOLLIE_REFUND_FREE_3"}]: [{$oView->getAmountRemaining()}] <small>[{$edit->oxorder__oxcurrency->value}]</small></span>
                </form>
            [{/if}]
        [{/if}]
    </fieldset>
[{/if}]

[{include file="bottomnaviitem.tpl"}]
</table>
[{include file="bottomitem.tpl"}]
