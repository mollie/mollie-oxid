function isApplePayAvailable() {
    try {
        return window.ApplePaySession && window.ApplePaySession.canMakePayments();
    } catch (error) {
        console.warn('Apple Pay could not be initialized:', error);
    }
    return false;
}

function mollieGetProductAmount(implementationPosition) {
    if (implementationPosition != "Details") {
        return false;
    }
    var formAmount = $('input[name="am"]').val();
    if (formAmount !== undefined) {
        return formAmount;
    }
    return 1;
}

function mollieGetProductBasketPrice(detailsProductId, implementationPosition) {
    var ajaxCall = $.ajax({
        url: shopBaseUrl + 'index.php',
        type: 'POST',
        dataType: 'json',
        async: false,
        data: {
            cl: "mollieApplePay",
            fnc: "getProductBasketPrice",
            detailsProductId: detailsProductId,
            detailsProductAmount: mollieGetProductAmount(implementationPosition)
        }
    });
    if (ajaxCall.responseJSON) {
        if (ajaxCall.responseJSON.productBasketPrice) {
            return ajaxCall.responseJSON.productBasketPrice;
        }
        if (ajaxCall.responseJSON.errormessage && ajaxCall.responseJSON.showexception && ajaxCall.responseJSON.showexception == true) {
            alert(ajaxCall.responseJSON.errormessage);
        }
        if (ajaxCall.responseJSON.errors) {
            console.log('Apple Pay: Could not get products basket price. ' + ajaxCall.responseJSON.errors[0].message);
        }
    }
    return false;
}

function mollieInitApplePay(countryCode, currencyCode, shopName, price, delivery_price, detailsProductId, totalLabel, shippingLabel, shippingId, implementationPosition) {
    if (implementationPosition == "Details") {
        var productBasketPrice = mollieGetProductBasketPrice(detailsProductId, implementationPosition);
        if (!isNaN(productBasketPrice)) {
            price = parseFloat(productBasketPrice.toFixed(2));
        }
        if (productBasketPrice === false) {
            return false;
        }
    }
    var initialPrice = price;
    if (delivery_price !== false && !isNaN(delivery_price)) {
        initialPrice = (price + delivery_price).toFixed(2);
    }
    var lineItems = [
        {label: totalLabel, amount: price, type: 'final'},
    ];
    var request = {
        countryCode: countryCode,
        currencyCode: currencyCode,
        supportedNetworks: ["amex", "maestro", "masterCard", "visa", "vPay"],
        merchantCapabilities: ['supports3DS'],
        total: { label: shopName, amount: initialPrice },
        requiredBillingContactFields: ["postalAddress"],
        requiredShippingContactFields: ["postalAddress", "name", "email"],
        lineItems: lineItems,
    };

    var session = new ApplePaySession(3, request);
    session.onvalidatemerchant = function(event) {
        $.ajax({
            url: shopBaseUrl + 'index.php',
            type: 'POST',
            dataType: 'json',
            data: {
                cl: "mollieApplePay",
                fnc: "getMerchantSession",
                validationUrl: event.validationURL,
            },
            success: function (response) {
                if (response.success === true && response.merchantSession !== undefined) {
                    session.completeMerchantValidation(JSON.parse(response.merchantSession));
                } else {
                    console.log('Apple Pay initialization failed');
                    session.abort();
                }
            },
            error: function (xhr, status, errorThrown) {
                console.log('Apple Pay: An error occured. ' + status);
                session.abort();
            }
        });
    };
    session.onshippingcontactselected = function(event) {
        $.ajax({
            url: shopBaseUrl + 'index.php',
            type: 'POST',
            dataType: 'json',
            data: {
                cl: "mollieApplePay",
                fnc: "getDeliveryMethods",
                countryCode: event.shippingContact.countryCode,
                city: event.shippingContact.locality,
                zip: event.shippingContact.postalCode,
                detailsProductId: detailsProductId,
                detailsProductAmount: mollieGetProductAmount(implementationPosition)
            },
            success: function (response) {
                if (response.success === true && response.shippingMethods !== undefined && response.shippingMethods.length > 0) {
                    var shippingMethod = response.shippingMethods[0];
                    var newPrice = (price + parseFloat(shippingMethod.amount)).toFixed(2);
                    var newLineItems = JSON.parse(JSON.stringify(lineItems)); // Json-Workaround to clone the lineItems object, so that original lineItems variable stays untouched
                    newLineItems.push({label: shippingLabel + ': ' + shippingMethod.label, amount: parseFloat(shippingMethod.amount), type: 'final'});
                    var shippingContactUpdate = {
                        status: 'STATUS_SUCCESS',
                        newShippingMethods: response.shippingMethods,
                        newLineItems: newLineItems,
                        newTotal: { label: shopName, amount: newPrice },
                    };
                    session.completeShippingContactSelection(shippingContactUpdate);
                } else {
                    var shippingContactUpdate = {
                        status: 'STATUS_FAILURE',
                        errors: [new ApplePayError("addressUnserviceable", "country", "No shipping methods available.")],
                        newTotal: { label: shopName, amount: initialPrice },
                    };
                    session.completeShippingContactSelection(shippingContactUpdate);
                }
            },
            error: function (xhr, status, errorThrown) {
                console.log('Apple Pay: An error occured. ' + status);
                session.abort();
            }
        });
    };
    session.onshippingmethodselected = function(event) {
        $.ajax({
            url: shopBaseUrl + 'index.php',
            type: 'POST',
            data: {
                cl: "mollieApplePay",
                fnc: "updateShippingSet",
                shipSet: event.shippingMethod.identifier,
            },
            success: function (response) {
                shippingId = event.shippingMethod.identifier;
                var newPrice = (price + parseFloat(event.shippingMethod.amount)).toFixed(2);
                var newLineItems = JSON.parse(JSON.stringify(lineItems)); // Json-Workaround to clone the lineItems object, so that original lineItems variable stays untouched
                newLineItems.push({label: shippingLabel + ': ' + event.shippingMethod.label, amount: parseFloat(event.shippingMethod.amount), type: 'final'});
                var shippingMethodUpdate = {
                    status: 'STATUS_SUCCESS',
                    newlineItems: newLineItems,
                    newTotal: { label: shopName, amount: newPrice },
                };
                session.completeShippingMethodSelection(shippingMethodUpdate);
            },
            error: function (xhr, status, errorThrown) {
                console.log('Apple Pay: An error occured. ' + status);
                session.abort();
            }
        });
    };
    session.onpaymentauthorized = function(event) {
        $.ajax({
            url: shopBaseUrl + 'index.php',
            type: 'POST',
            dataType: 'json',
            data: {
                cl: "mollieApplePay",
                fnc: "finalizeMollieOrder",
                detailsProductId: detailsProductId,
                detailsProductAmount: mollieGetProductAmount(implementationPosition),
                token: event.payment.token,
                billingContact: event.payment.billingContact,
                shippingContact: event.payment.shippingContact,
            },
            success: function (response) {
                if (response.success !== undefined && response.success === true) {
                    session.completePayment({status: 'STATUS_SUCCESS'});
                    window.location.replace(response.redirectUrl);
                } else {
                    if (response.error !== undefined) {
                        session.completePayment({status: 'STATUS_FAILURE', errors: [new ApplePayError(response.error.code, response.error.contactField, response.error.message)]});
                    } else {
                        if (response.errormessage !== undefined) {
                            alert('Apple Pay payment was not successful. An error occured: ' + response.errormessage);
                        } else {
                            alert('Apple Pay payment was not successful.');
                        }
                        session.abort();
                    }
                }
            },
            error: function (xhr, status, errorThrown) {
                console.log('Apple Pay: An error occured. ' + status);
                session.abort();
            }
        });
    };
    session.begin();
}
