[{if $oViewConf->mollieCanShowApplePayButton($_mollie_payment_price)}]
    <div id="mollieApplePayButton[{$_mollie_position}]" class="apple-pay-button-with-text apple-pay-button-black-with-text" style="display: none;">
        <span class="text">Buy with</span>
        <span class="logo"></span>
    </div>
    [{assign var="langTotalGross" value="TOTAL_GROSS"|oxmultilangassign|replace:"'":""}]
    [{assign var="langShipping" value="SHIPPING_COST"|oxmultilangassign|replace:"'":""}]
    [{capture name="mollieApplePayButtonEnable"}]
        var applePayDiv = document.getElementById('mollieApplePayButton[{$_mollie_position}]');
        if (isApplePayAvailable()) {
            var price = [{$_mollie_payment_price}];
            [{if $_mollie_delivery_costs}]
                var delivery_price = [{$_mollie_delivery_costs}];
            [{else}]
                var delivery_price = false;
            [{/if}]
            var shopName = '[{$oxcmp_shop->oxshops__oxname->rawValue|replace:"'":''}]';
            var shopBaseUrl = '[{$oViewConf->mollieGetShopUrl()}]';
            var countryCode = '[{$oViewConf->mollieGetHomeCountryCode()}]';
            var currencyCode = '[{$oViewConf->mollieGetCurrentCurrency()}]';
            [{if $_mollie_shipping_id}]
                var shippingId = '[{$_mollie_shipping_id}]';
            [{else}]
                var shippingId = false;
            [{/if}]
            [{if $_mollie_details_product_id}]
                var detailsProductId = '[{$_mollie_details_product_id}]';
            [{else}]
                var detailsProductId = null;
            [{/if}]
            var totalLabel = '[{$langTotalGross}]';
            var shippingLabel = '[{$langShipping}]';
            var implementationPosition = '[{$_mollie_position}]';
            applePayDiv.addEventListener("click", function() {
                mollieInitApplePay(countryCode, currencyCode, shopName, price, delivery_price, detailsProductId, totalLabel, shippingLabel, shippingId, implementationPosition);
            });
            applePayDiv.className += ' active';
            applePayDiv.style.display = '';
        } else {
            applePayDiv.remove();
        }
    [{/capture}]
    [{oxscript add=$smarty.capture.mollieApplePayButtonEnable}]
[{/if}]