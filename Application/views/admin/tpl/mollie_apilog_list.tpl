[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign box="list"}]
[{assign var="where" value=$oView->getListFilter()}]

[{if $readonly}]
    [{assign var="readonly" value="readonly disabled"}]
[{else}]
    [{assign var="readonly" value=""}]
[{/if}]

<script type="text/javascript">
    <!--
    window.onload = function ()
    {
        top.reloadEditFrame();
        [{if $updatelist == 1}]
            top.oxid.admin.updateList('[{$oxid}]');
        [{/if}]
    }
    //-->
</script>

<div id="liste">
    <form name="search" id="search" action="[{$oViewConf->getSelfLink()}]" method="post">
        [{include file="_formparams.tpl" cl="mollie_apilog_list" lstrt=$lstrt actedit=$actedit oxid=$oxid fnc="" language=$actlang editlanguage=$actlang}]
        <table cellspacing="0" cellpadding="0" border="0" width="100%">
            <colgroup>
                <col width="25%">
                <col width="25%">
                <col width="10%">
                <col width="20%">
                <col width="20%">
            </colgroup>

            <tr class="listitem">
                <td valign="top" class="listfilter" align="left">
                    <div class="r1">
                        <div class="b1">
                            <input class="listedit" type="text" size="15" name="where[mollierequestlog][timestamp]" value="[{$where.mollierequestlog.timestamp}]">
                        </div>
                    </div>
                </td>
                <td valign="top" class="listfilter" align="left">
                    <div class="r1">
                        <div class="b1">
                            <input class="listedit" type="text" size="31" name="where[mollierequestlog][orderid]" value="[{$where.mollierequestlog.orderid}]">
                        </div>
                    </div>
                </td>
                <td valign="top" class="listfilter" align="left">
                    <div class="r1">
                        <div class="b1">
                            <input class="listedit" type="text" size="2" name="where[mollierequestlog][storeid]" value="[{$where.mollierequestlog.storeid}]">
                        </div>
                    </div>
                </td>
                <td valign="top" class="listfilter" align="left">
                    <div class="r1">
                        <div class="b1">
                            <input class="listedit" type="text" size="15" name="where[mollierequestlog][requesttype]" value="[{$where.mollierequestlog.requesttype}]">
                        </div>
                    </div>
                </td>
                <td valign="top" class="listfilter" colspan="2" nowrap>
                    <div class="r1">
                        <div class="b1">
                            <div class="find">
                                <input class="listedit" type="submit" name="submitit" value="[{oxmultilang ident="GENERAL_SEARCH"}]" onClick="Javascript:document.search.lstrt.value=0;">
                            </div>
                            <input class="listedit" type="text" size="9" name="where[mollierequestlog][responsestatus]" value="[{$where.mollierequestlog.responsestatus}]">
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td class="listheader first" height="15" width="30"><a href="Javascript:top.oxid.admin.setSorting( document.search, 'mollierequestlog', 'timestamp', 'asc');document.search.submit();" class="listheader">Timestamp</a></td>
                <td class="listheader" height="15"><a href="Javascript:top.oxid.admin.setSorting( document.search, 'mollierequestlog', 'orderid', 'asc');document.search.submit();" class="listheader">OrderID</a></td>
                <td class="listheader" height="15"><a href="Javascript:top.oxid.admin.setSorting( document.search, 'mollierequestlog', 'storeid', 'asc');document.search.submit();" class="listheader">StoreID</a></td>
                <td class="listheader" height="15"><a href="Javascript:top.oxid.admin.setSorting( document.search, 'mollierequestlog', 'requesttype', 'asc');document.search.submit();" class="listheader">Requesttype</a></td>
                <td class="listheader" height="15" colspan="2"><a href="Javascript:top.oxid.admin.setSorting( document.search, 'mollierequestlog', 'responsestatus', 'asc');document.search.submit();" class="listheader">Responsestatus</a></td>
            </tr>

            [{assign var="blWhite" value=""}]
            [{assign var="_cnt" value=0}]
            [{foreach from=$mylist item=listitem}]
                [{assign var="_cnt" value=$_cnt+1}]
                <tr id="row.[{$_cnt}]">
                    [{block name="mollie_requestlog_list_item"}]
                        [{if $listitem->blacklist == 1}]
                            [{assign var="listclass" value=listitem3}]
                        [{else}]
                            [{assign var="listclass" value=listitem$blWhite}]
                        [{/if}]
                        [{if $listitem->getId() == $oxid}]
                            [{assign var="listclass" value=listitem4}]
                        [{/if}]
                        <td valign="top" class="[{$listclass}]" height="15">
                            <div class="listitemfloating">
                                <a href="Javascript:top.oxid.admin.editThis('[{$listitem->mollierequestlog__oxid->value}]');" class="[{$listclass}]">
                                    [{$listitem->mollierequestlog__timestamp->value}]
                                </a>
                            </div>
                        </td>
                        <td valign="top" class="[{$listclass}]" height="15">
                            <div class="listitemfloating">
                                <a href="Javascript:top.oxid.admin.editThis('[{$listitem->mollierequestlog__oxid->value}]');" class="[{$listclass}]">
                                    [{$listitem->mollierequestlog__orderid->value}]
                                </a>
                            </div>
                        </td>
                        <td valign="top" class="[{$listclass}]" height="15">
                            <div class="listitemfloating">
                                <a href="Javascript:top.oxid.admin.editThis('[{$listitem->mollierequestlog__oxid->value}]');" class="[{$listclass}]">
                                    [{$listitem->mollierequestlog__storeid->value}]
                                </a>
                            </div>
                        </td>
                        <td valign="top" class="[{$listclass}]" height="15">
                            <div class="listitemfloating">
                                <a href="Javascript:top.oxid.admin.editThis('[{$listitem->mollierequestlog__oxid->value}]');" class="[{$listclass}]">
                                    [{$listitem->mollierequestlog__requesttype->value}]
                                </a>
                            </div>
                        </td>
                        <td valign="top" class="[{$listclass}]" height="15">
                            <div class="listitemfloating">
                                <a href="Javascript:top.oxid.admin.editThis('[{$listitem->mollierequestlog__oxid->value}]');" class="[{$listclass}]">
                                    [{$listitem->mollierequestlog__responsestatus->value}]
                                </a>
                            </div>
                        </td>
                    [{/block}]
                </tr>

            [{/foreach}]
            [{include file="pagenavisnippet.tpl" colspan="3"}]
        </table>
    </form>
</div>

[{include file="pagetabsnippet.tpl"}]


</body>
</html>