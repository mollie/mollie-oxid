[{assign var="aIssuers" value=$paymentModel->getIssuers($oView->getDynValue(), $sInputName)}]
[{if $aIssuers|count}]
    <div class="form-group">
        <label class="req control-label col-lg-3">[{$sLabel}]</label>
        <div class="col-lg-9">
            <select name="dynvalue[[{$sInputName}]]" class="form-control selectpicker" required="required">
                [{foreach from=$aIssuers item=issuer key=key}]
                    <option value="[{$key}]" [{if $sSavedValue == $key}]selected[{/if}]>[{$issuer.title}]</option>
                [{/foreach}]
            </select>
        </div>
    </div>
[{/if}]
