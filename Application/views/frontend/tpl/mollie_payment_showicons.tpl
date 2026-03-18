[{oxstyle include=$oViewConf->getModuleUrl('molliepayment','out/src/css/mollie.css')}]
[{if !$paymentModel}]
    [{assign var="paymentModel" value=$paymentmethod->getMolliePaymentModel()}]
[{/if}]
[{assign var="isDisabled" value=$paymentModel->isNotAvailableButVisible()}]
<dl>
    <dt>
        <input id="payment_[{$sPaymentID}]" type="radio" name="paymentid" value="[{if !$isDisabled}][{$sPaymentID}][{else}]disabled[{/if}]" [{if $oView->getCheckedPaymentId() == $paymentmethod->oxpayments__oxid->value && !$isDisabled}]checked[{/if}] [{if $isDisabled}]disabled[{/if}]>
        <label for="payment_[{$sPaymentID}]">
            <img class="mollie-payment-icon" src="[{$paymentModel->getMolliePaymentMethodPic()}]">
            <b>[{$paymentmethod->oxpayments__oxdesc->value}]</b>
        </label>

        [{if $isDisabled}]
            <div class="col-lg-offset-3 desc">[{$paymentModel->getNotAvailableMessage()}]</div>
        [{/if}]
    </dt>
    <dd class="[{if $oView->getCheckedPaymentId() == $paymentmethod->oxpayments__oxid->value}]activePayment[{/if}]">
        [{if $paymentmethod->getPrice() && !$isDisabled}]
            [{assign var="oPaymentPrice" value=$paymentmethod->getPrice() }]
            [{if $oViewConf->isFunctionalityEnabled('blShowVATForPayCharge') }]
                [{strip}]
                    ([{oxprice price=$oPaymentPrice->getNettoPrice() currency=$currency}]
                    [{if $oPaymentPrice->getVatValue() > 0}]
                        [{oxmultilang ident="PLUS_VAT"}] [{oxprice price=$oPaymentPrice->getVatValue() currency=$currency}]
                    [{/if}])
                [{/strip}]
            [{else}]
                ([{oxprice price=$oPaymentPrice->getBruttoPrice() currency=$currency}])
            [{/if}]
        [{/if}]

        [{if !$isDisabled}]
            [{foreach from=$paymentmethod->getDynValues() item=value name=PaymentDynValues}]
                <div class="form-group">
                    <label class="control-label col-lg-3" for="[{$sPaymentID}]_[{$smarty.foreach.PaymentDynValues.iteration}]">[{$value->name}]</label>
                    <div class="col-lg-9">
                        <input id="[{$sPaymentID}]_[{$smarty.foreach.PaymentDynValues.iteration}]" type="text" class="form-control textbox" size="20" maxlength="64" name="dynvalue[[{$value->name}]]" value="[{$value->value}]">
                    </div>
                </div>
            [{/foreach}]
        [{/if}]

        <div class="clearfix"></div>

        [{block name="checkout_payment_longdesc"}]
            [{if $paymentmethod->oxpayments__oxlongdesc->value|strip_tags|trim}]
                <div class="alert alert-info col-lg-offset-3 desc">
                    [{$paymentmethod->oxpayments__oxlongdesc->getRawValue()}]
                </div>
            [{/if}]
        [{/block}]
    </dd>
</dl>
