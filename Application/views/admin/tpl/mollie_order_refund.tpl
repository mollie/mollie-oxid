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

[{if $oView->wasRefundSuccessful() == true}]
    <fieldset class="refundSuccess message">
        [{oxmultilang ident="MOLLIE_REFUND_SUCCESSFUL"}]
    </fieldset>
[{/if}]

[{if $oView->getErrorMessage() != false}]
    <fieldset class="refundError message">
        <strong>Error</strong>
        [{$oView->getErrorMessage()}]
    </fieldset>
[{/if}]

[{assign var="blIsOrderRefundable" value=$oView->isOrderRefundable()}]
[{if $blIsOrderRefundable == false}]
    <fieldset class="refundNotice message">
        <strong>[{oxmultilang ident="MOLLIE_NOTICE"}]</strong>
        [{oxmultilang ident="MOLLIE_ORDER_NOT_REFUNDABLE"}]
    </fieldset>
[{/if}]

[{if $oView->hasOrderVoucher() == true}]
    <fieldset class="refundNotice message">
        <strong>[{oxmultilang ident="MOLLIE_NOTICE"}]</strong>
        [{oxmultilang ident="MOLLIE_VOUCHERS_EXISTING"}]
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
                <span>[{oxmultilang ident="MOLLIE_REFUND_REMAINING"}]:: [{$oView->getFormatedPrice($oView->getRemainingRefundableAmount())}] <small>[{$edit->oxorder__oxcurrency->value}]</small></span><br><br>
            [{/if}]
            <span><label for="refund_description">[{oxmultilang ident="MOLLIE_REFUND_DESCRIPTION"}]:</label></span>
            <input type="text" name="refund_description" value="" placeholder="[{oxmultilang ident="MOLLIE_REFUND_DESCRIPTION_PLACEHOLDER"}]" maxlength="140" size="120"><br>
            <input type="submit" value="[{oxmultilang ident="MOLLIE_REFUND_SUBMIT"}]" class="refundSubmit">
        </form>
    </fieldset>
[{/if}]

<fieldset>
    <legend>[{oxmultilang ident="MOLLIE_PARTIAL_REFUND"}]</legend>
    [{if $oView->isMollieOrder() === false}]
        [{oxmultilang ident="MOLLIE_NO_MOLLIE_PAYMENT"}]
    [{else}]
        <form name="search" id="search" action="[{$oViewConf->getSelfLink()}]" method="post">
            [{$oViewConf->getHiddenSid()}]
            <input type="hidden" name="cl" value="mollie_order_refund">
            <input type="hidden" name="oxid" value="[{$oxid}]">
            <input type="hidden" name="fnc" value="[{if $blIsOrderRefundable == true}]partialRefund[{/if}]">
            [{if $oView->isQuantityAvailable() == true && $blIsOrderRefundable == true}]
                <div class="typeSelect">
                    <label for="mollie_refund_type">[{oxmultilang ident="MOLLIE_TYPE_SELECT_LABEL"}]:</label>
                    <select name="mollie_refund_type" onchange="toggleRefundType(this);">
                        <option value="amount">[{oxmultilang ident="MOLLIE_AMOUNT"}]</option>
                        <option value="quantity">[{oxmultilang ident="MOLLIE_QUANTITY"}]</option>
                    </select>
                </div>
            [{/if}]
            <table cellspacing="0" cellpadding="0" border="0" width="98%" class="refundTable">
                <tr>
                    [{block name="admin_order_article_header"}]
                        [{if $blIsOrderRefundable == true}]
                            <td class="listheader first" height="15">[{oxmultilang ident="MOLLIE_REFUND_AMOUNT"}]</td>
                            [{if $oView->isQuantityAvailable() == true}]<td class="listheader">[{oxmultilang ident="MOLLIE_REFUND_QUANTITY"}]</td>[{/if}]
                        [{/if}]
                        <td class="listheader">[{oxmultilang ident="GENERAL_ITEMNR"}]</td>
                        <td class="listheader">[{oxmultilang ident="GENERAL_TITLE"}]</td>
                        <td class="listheader">[{oxmultilang ident="MOLLIE_HEADER_SINGLE_PRICE"}]</td>
                        <td class="listheader">[{oxmultilang ident="GENERAL_ATALL"}]</td>
                        <td class="listheader">[{oxmultilang ident="ORDER_ARTICLE_MWST"}]</td>
                        <td class="listheader">[{oxmultilang ident="MOLLIE_HEADER_ORDERED"}]</td>
                        <td class="listheader">[{oxmultilang ident="MOLLIE_HEADER_REFUNDED"}]</td>
                    [{/block}]
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
                        [{block name="admin_order_article_listitem"}]
                            [{assign var="listclass" value=listitem$blWhite}]
                            [{if $blIsOrderRefundable == true}]
                                <td valign="top" class="[{$listclass}][{$class}]"><span [{if $listitem.isOrderarticle == true && $oView->isQuantityAvailable() == true}]class="refundAmount"[{/if}]><input type="text" name="aOrderArticles[[{$listitem.id}]][refund_amount]" value="[{$listitem.refundableAmount}]" class="listedit" [{if $listitem.refundableAmount <= 0 || $listitem.isPartialAllowed == false}]disabled[{/if}]></span></td>
                                [{if $oView->isQuantityAvailable() == true}]<td valign="top" class="[{$listclass}][{$class}]">[{if $listitem.isOrderarticle == true}]<span class="refundQuantity" style="display:none;"><input type="text" name="aOrderArticles[[{$listitem.id}]][refund_quantity]" value="[{$listitem.refundableQuantity}]" class="listedit" [{if $listitem.refundableQuantity <= 0}]disabled[{/if}]></span>[{/if}]</td>[{/if}]
                            [{/if}]
                            <td valign="top" class="[{$listclass}][{$class}]" height="15">[{$listitem.artnum}]</a></td>
                            <td valign="top" class="[{$listclass}][{$class}]">[{$listitem.title|strip_tags}]</a></td>
                            <td valign="top" class="[{$listclass}][{$class}]">[{$oView->getFormatedPrice($listitem.singlePrice)}] <small>[{$edit->oxorder__oxcurrency->value}]</small></td>
                            <td valign="top" class="[{$listclass}][{$class}]">[{$oView->getFormatedPrice($listitem.totalPrice)}] <small>[{$edit->oxorder__oxcurrency->value}]</small></td>
                            <td valign="top" class="[{$listclass}][{$class}]">[{$listitem.vat}]</td>
                            <td valign="top" class="[{$listclass}][{$class}]">
                                <span [{if $listitem.isOrderarticle == true && $oView->isQuantityAvailable() == true}]class="refundAmount"[{/if}]>[{$oView->getFormatedPrice($listitem.totalPrice)}] <small>[{$edit->oxorder__oxcurrency->value}]</small></span>
                                [{if $listitem.isOrderarticle == true && $oView->isQuantityAvailable() == true}]<span class="refundQuantity" style="display:none;">[{$listitem.quantity}]</span>[{/if}]
                            </td>
                            <td valign="top" class="[{$listclass}][{$class}]">
                                <span [{if $listitem.isOrderarticle == true && $oView->isQuantityAvailable() == true}]class="refundAmount"[{/if}]>[{$oView->getFormatedPrice($listitem.amountRefunded)}] <small>[{$edit->oxorder__oxcurrency->value}]</small></span>
                                [{if $listitem.isOrderarticle == true && $oView->isQuantityAvailable() == true}]<span class="refundQuantity" style="display:none;">[{$listitem.quantityRefunded}]</span>[{/if}]
                            </td>
                        [{/block}]
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
                <label for="refund_description">[{oxmultilang ident="MOLLIE_REFUND_DESCRIPTION"}]:</label>
                <input type="text" name="refund_description" value="" placeholder="[{oxmultilang ident="MOLLIE_REFUND_DESCRIPTION_PLACEHOLDER"}]" maxlength="140" size="120"><br>
                <input type="submit" value="[{oxmultilang ident="MOLLIE_REFUND_SUBMIT"}]" class="refundSubmit">
            [{/if}]
        </form>
    [{/if}]
</fieldset>

[{include file="bottomnaviitem.tpl"}]
</table>
[{include file="bottomitem.tpl"}]
