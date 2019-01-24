[{assign var="aIssuers" value=$paymentModel->getIssuers($oView->getDynValue(), $sInputName)}]
[{if $aIssuers|count}]
    [{oxstyle include=$oViewConf->getModuleUrl('molliepayment','out/src/css/mollie.css')}]
    <div class="form-group issuers-radio">
        <label class="req control-label col-lg-3">[{$sLabel}]</label>
        <div class="col-lg-9">
            [{foreach from=$aIssuers item=issuer key=key}]
                <input class="issuer-radiobutton" type="radio" id="[{$key}]" name="dynvalue[[{$sInputName}]]" value="[{$key}]" [{if $sSavedValue == $key}]checked[{/if}]>
                <label for="[{$key}]">
                    <img class="payment-icon" src="[{$issuer.pic}]">
                    <span class="issuer-title">[{$issuer.title}]</span>
                </label><br>
            [{/foreach}]
        </div>
    </div>
[{/if}]
