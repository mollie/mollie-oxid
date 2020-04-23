function isApplePayAvailable()
{
    try {
        return window.ApplePaySession && window.ApplePaySession.canMakePayments();
    } catch (error) {
        console.warn('Apple Pay could not be initialized:', error);
    }
    return false;
}

function mollieInitApplePay(countryCode, currencyCode, shopName, price, detailsProductId, lineItems, shippingLabel) {
    var request = {
        countryCode: countryCode,
        currencyCode: currencyCode,
        supportedNetworks: ["amex", "maestro", "masterCard", "visa", "vPay"],
        merchantCapabilities: ['supports3DS'],
        total: { label: shopName, amount: price },
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
                detailsProductId: detailsProductId
            },
            success: function (response) {
                if (response.success === true && response.shippingMethods !== undefined && response.shippingMethods.length > 0) {
                    var newPrice = (price + parseFloat(response.shippingMethods[0].amount)).toFixed(2);
                    var newLineItems = JSON.parse(JSON.stringify(lineItems)); // Json-Workaround to clone the lineItems object, so that original lineItems variable stays untouched
                    newLineItems.push({label: shippingLabel + ': ' + response.shippingMethods[0].label, amount: parseFloat(response.shippingMethods[0].amount), type: 'final'});
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
                        newTotal: { label: shopName, amount: price },
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
                var newPrice = price + parseFloat(event.shippingMethod.amount);
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