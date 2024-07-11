[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

[{if $readonly}]
    [{assign var="readonly" value="readonly disabled"}]
[{else}]
    [{assign var="readonly" value=""}]
[{/if}]

[{assign var="edit" value=$oView->getEdit()}]

<script type="text/javascript">

    function editThis( sID )
    {
        var oTransfer = top.basefrm.edit.document.getElementById( "transfer" );
        oTransfer.oxid.value = sID;
        oTransfer.cl.value = top.basefrm.list.sDefClass;

        //forcing edit frame to reload after submit
        top.forceReloadingEditFrame();

        var oSearch = top.basefrm.list.document.getElementById( "search" );
        oSearch.oxid.value = sID;
        oSearch.actedit.value = 0;
        oSearch.submit();
    }

    window.onload = function ()
    {
        top.oxid.admin.updateList('[{$oxid}]');
    }
</script>

<style>
    tr.response {
        margin-bottom: 29px;
    }
    .request, .response {
        border: 1px solid #A9A9A9;
        padding-left: 15px;
        display: block;
        width: 1600px;
        margin: 0 18px 20px 24px;
    }
</style>

<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{$oxid}]">
    <input type="hidden" name="oxidCopy" value="[{$oxid}]">
    <input type="hidden" name="cl" value="mollie_apilog_main">
    <input type="hidden" name="editlanguage" value="[{$editlanguage}]">
</form>

[{if $edit}]
    [{assign var="request" value=$oView->getRequest()}]
    [{assign var="response" value=$oView->getResponse()}]
    <table cellspacing="0" cellpadding="0" border="0" style="width:98%;border-collapse: collapse;">
        <tr class="request">
            <td id="editval_mollierequestlog__request" class="edittext">
                <h2>Request</h2>
                [{if $request}]
                    [{foreach from=$request key=requestkey item=requestvalue}]
                        [{if $requestkey == 'amount' }]
                            <b>[{$requestkey}]:</b> [{$requestvalue.value}] [{$requestvalue.currency}]<br><br>
                        [{elseif $requestkey == 'metadata'}]
                            [{foreach from=$requestvalue key=metadatakey item=metadatavalue}]
                                <b>[{$metadatakey}]:</b> [{$metadatavalue}]<br><br>
                            [{/foreach}]
                        [{elseif $requestkey == 'billingAddress' || $requestkey == 'shippingAddress'}]
                            <b>[{$requestkey}]:</b><br>
                            [{if $requestvalue.email }]
                                <u>[{$requestvalue.email}]</u>
                                <br><br>
                            [{/if}]
                            [{if $requestvalue.givenName && $requestvalue.familyName }]
                                [{$requestvalue.givenName}] [{$requestvalue.familyName}]
                                <br>
                            [{/if}]
                            [{foreach from=$requestvalue key=addresskey item=addressvalue}]
                                [{if $addresskey != 'givenName' && $addresskey != 'familyName' && $addresskey != 'email' }]
                                    [{$addressvalue}][{if $addresskey !== 'postalCode'}]<br>[{/if}]
                                [{/if}]
                            [{/foreach}]
                            <br>
                        [{elseif $requestvalue === false || $requestvalue === true}]
                            <b>[{$requestkey}]:</b> [{if $requestvalue === false }]false[{else}]true[{/if}]<br><br>
                        [{else}]
                            <b>[{$requestkey}]:</b> [{$requestvalue}]<br><br>
                        [{/if}]
                    [{/foreach}]
                [{/if}]
            </td>
        </tr>
        <tr class="response">
            <td id="editval_mollierequestlog__response" class="edittext">
                <h2>Response</h2>
                [{if $response}]
                    [{foreach from=$response key=responsekey item=responsevalue}]
                        [{if $responsekey == 'amount'
                            || $responsekey == 'settlementAmount'
                            || $responsekey == 'amountRefunded'
                            || $responsekey == 'amountRemaining'
                            || $responsekey == 'amountChargedBack'
                            || $responsekey == 'amountCaptured'}]

                            [{if isset($responsevalue.value) && $responsevalue.value != ''}]
                                <b>[{$responsekey}]:</b> [{$responsevalue.value}] [{$responsevalue.currency}]<br><br>
                            [{/if}]
                        [{elseif $responsekey == 'metadata'}]
                            [{foreach from=$responsevalue key=metadatakey item=metadatavalue}]
                                [{if isset($metadatavalue) && $metadatavalue != ''}]
                                    <b>[{$metadatakey}]:</b> [{$metadatavalue}]<br><br>
                                [{/if}]
                            [{/foreach}]
                        [{elseif $responsekey == 'details'}]
                            [{foreach from=$responsevalue key=detailskey item=detailsvalue}]
                                [{if isset($detailsvalue) && $detailsvalue != ''}]
                                    <b>[{$detailskey}]:</b> [{$detailsvalue}]<br><br>
                                [{/if}]
                            [{/foreach}]
                        [{elseif $responsekey == 'billingAddress' || $responsekey == 'shippingAddress'}]
                            <b>[{$responsekey}]:</b><br>
                            [{if $responsevalue.email }]
                                <u>[{$responsevalue.email}]</u>
                                <br><br>
                            [{/if}]
                            [{if $responsevalue.givenName && $responsevalue.familyName }]
                                [{$responsevalue.givenName}] [{$responsevalue.familyName}]
                                <br>
                            [{/if}]
                            [{foreach from=$responsevalue key=addresskey item=addressvalue}]
                                [{if $addresskey != 'givenName' && $addresskey != 'familyName' && $addresskey != 'email' }]
                                    [{$addressvalue}][{if $addresskey !== 'postalCode'}]<br>[{/if}]
                                [{/if}]
                            [{/foreach}]
                            <br>
                        [{elseif $responsevalue === false || $responsevalue === true}]
                            <b>[{$responsekey}]:</b> [{if $responsevalue === false }]false[{else}]true[{/if}]<br><br>
                        [{else}]
                            [{if isset($responsevalue) && $responsevalue != ''}]
                                <b>[{$responsekey}]:</b> [{$responsevalue}]<br><br>
                            [{/if}]
                        [{/if}]
                    [{/foreach}]
                [{/if}]
            </td>
        </tr>
    </table>
[{else}]
    [{oxmultilang ident='MOLLIE_ADMIN_API_LOGS_SELECT_ENTRY'}]
[{/if}]


[{include file="bottomnaviitem.tpl"}]

[{include file="bottomitem.tpl"}]