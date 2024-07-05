[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

[{if $readonly}]
    [{assign var="readonly" value="readonly disabled"}]
[{else}]
    [{assign var="readonly" value=""}]
[{/if}]

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
        var oField = top.oxid.admin.getLockTarget();
        oField.onchange = oField.onkeyup = oField.onmouseout = top.oxid.admin.unlockSave;
    }
</script>

<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{$oxid}]">
    <input type="hidden" name="oxidCopy" value="[{$oxid}]">
    <input type="hidden" name="cl" value="mollie_apilog_main">
    <input type="hidden" name="editlanguage" value="[{$editlanguage}]">
</form>

<form name="myedit" id="myedit" action="[{$oViewConf->getSelfLink()}]" method="post" style="padding: 0px;margin: 0px;height:0px;">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="cl" value="mollie_apilog_main">
    <input type="hidden" name="fnc" value="">
    <input type="hidden" name="oxid" value="[{$oxid}]">
    <table cellspacing="0" cellpadding="0" border="0" style="width:98%;">
        <tr>
            <td class="edittext">
                <b>Request</b>
            </td>
            <td class="edittext">
                <textarea id="editval_mollierequestlog__request]" name="editval[mollierequestlog__request]" style="width:100%; height:300px;" disabled>
                    [{foreach from=$edit->mollierequestlog__request->value|json_decode:true item=mollierequestelement}]
                        <pre>
                        [{$mollierequestelement->method}]
                        </pre>
                    [{/foreach}]
                    [{foreach from=$edit->mollierequestlog__request->value|json_decode item=mollierequestelement}]
                        <pre>
                        [{$mollierequestelement->method}]
                        </pre>
                    [{/foreach}]
                    ManuTestJson: [{$edit->mollierequestlog__request->value}]
                </textarea>
            </td>
        </tr>
        <tr>
            <td class="edittext">
                <b>Response</b>
            </td>
            <td class="edittext">
                <textarea id="editval_mollierequestlog__response]" name="editval[mollierequestlog__response]" style="width:100%; height:300px;" disabled>
                    [{$edit->mollierequestlog__response->value}]
                </textarea>
            </td>
        </tr>
    </table>
</form>

[{include file="bottomnaviitem.tpl"}]

[{include file="bottomitem.tpl"}]