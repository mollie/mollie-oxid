function isApplePayAvailable() {
    try {
        return window.ApplePaySession && window.ApplePaySession.canMakePayments();
    } catch (error) {
        console.warn('Apple Pay could not be initialized:', error);
    }
    return false;
}

function mollieGetProductAmount(implementationPosition) {
    if (implementationPosition !== "Details") {
        return false;
    }
    var formAmount = $('input[name="am"]').val();
    if (formAmount !== undefined) {
        return formAmount;
    }
    return 1;
}

function prepareMollieApplePayRequestData(object, form, namespace) {
    var formData = form || new FormData();
    var formKey;
    var value;

    for (var property in object) {
        if (object.hasOwnProperty(property)) {
            formKey = namespace ? namespace + '[' + property + ']' : property;
            value = object[property];

            if (typeof value === 'object' && !(value instanceof File)) {
                prepareMollieApplePayRequestData(
                    value,
                    formData,
                    namespace
                        ? namespace + '[' + property + ']'
                        : property
                );
                continue;
            }

            formData.append(formKey, value);
        }
    }

    return formData;
}

function mollieGetProductBasketPrice(detailsProductId, implementationPosition) {
    fetch(
        shopBaseUrl + 'index.php',
        {
            method: 'POST',
            body: prepareMollieApplePayRequestData({
                cl: "mollieApplePay",
                fnc: "getProductBasketPrice",
                detailsProductId: detailsProductId,
                detailsProductAmount: mollieGetProductAmount(implementationPosition)
            })
        }
    ).then(function (response) {
        response.json().then(function (jsonResponse) {
            if (jsonResponse.productBasketPrice) {
                return response.productBasketPrice;
            }
            if (jsonResponse.errormessage && jsonResponse.showexception && jsonResponse.showexception === true) {
                alert(jsonResponse.errormessage);
            }
            if (jsonResponse.errors) {
                console.log('Apple Pay: Could not get products basket price. ' + jsonResponse.errors[0].message);
            }
        });
    });

    return false;
}

function mollieInitApplePay(
    countryCode,
    currencyCode,
    shopName,
    price,
    deliveryPrice,
    detailsProductId,
    totalLabel,
    shippingLabel,
    shippingId,
    implementationPosition
) {
    if (implementationPosition === "Details") {
        var productBasketPrice = mollieGetProductBasketPrice(detailsProductId, implementationPosition);
        if (!isNaN(productBasketPrice)) {
            price = parseFloat(productBasketPrice.toFixed(2));
        }
        if (productBasketPrice === false) {
            return false;
        }
    }
    var initialPrice = price;
    if (deliveryPrice !== false && !isNaN(deliveryPrice)) {
        initialPrice = (price + deliveryPrice).toFixed(2);
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
        fetch(
            shopBaseUrl + 'index.php',
            {
                method: 'POST',
                body: prepareMollieApplePayRequestData({
                    cl: "mollieApplePay",
                    fnc: "getMerchantSession",
                    validationUrl: event.validationURL,
                })
            }
        ).then(function (response) {
            response.json().then(function (jsonResponse) {
                if (jsonResponse.success === true && jsonResponse.merchantSession !== undefined) {
                    session.completeMerchantValidation(JSON.parse(jsonResponse.merchantSession));
                } else {
                    console.log('Apple Pay initialization failed');
                    session.abort();
                }
            });
        }).catch(function (error) {
            console.log('Apple Pay: An error occurred. ' + error.status);
            session.abort();
        });
    };
    session.onshippingcontactselected = function(event) {
        fetch(
            shopBaseUrl + 'index.php',
            {
                method: 'POST',
                body: prepareMollieApplePayRequestData({
                    cl: "mollieApplePay",
                    fnc: "getDeliveryMethods",
                    countryCode: event.shippingContact.countryCode,
                    city: event.shippingContact.locality,
                    zip: event.shippingContact.postalCode,
                    detailsProductId: detailsProductId,
                    detailsProductAmount: mollieGetProductAmount(implementationPosition)
                })
            }
        ).then(function (response) {
            response.json().then(function (jsonResponse) {
                if (jsonResponse.success === true && jsonResponse.shippingMethods !== undefined && jsonResponse.shippingMethods.length > 0) {
                    var shippingMethod = jsonResponse.shippingMethods[0];
                    var newLineItems = JSON.parse(JSON.stringify(lineItems)); // Json-Workaround to clone the lineItems object, so that original lineItems variable stays untouched
                    newLineItems.push({label: shippingLabel + ': ' + shippingMethod.label, amount: parseFloat(shippingMethod.amount), type: 'final'});

                    session.completeShippingContactSelection({
                        status: 'STATUS_SUCCESS',
                        newShippingMethods: jsonResponse.shippingMethods,
                        newLineItems: newLineItems,
                        newTotal: {
                            label: shopName,
                            amount: (price + parseFloat(shippingMethod.amount)).toFixed(2)
                        }
                    });
                } else {
                    session.completeShippingContactSelection({
                        status: 'STATUS_FAILURE',
                        errors: [new ApplePayError("addressUnserviceable", "country", "No shipping methods available.")],
                        newTotal: { label: shopName, amount: initialPrice }
                    });
                }
            });
        }).catch(function (error) {
            console.log('Apple Pay: An error occurred. ' + error.status);
            session.abort();
        });
    };
    session.onshippingmethodselected = function(event) {
        fetch(
            shopBaseUrl + 'index.php',
            {
                method: 'POST',
                body: prepareMollieApplePayRequestData({
                    cl: "mollieApplePay",
                    fnc: "updateShippingSet",
                    shipSet: event.shippingMethod.identifier,
                })
            }
        ).then(function () {
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
        }).catch(function (error) {
            console.log('Apple Pay: An error occurred. ' + error.status);
            session.abort();
        });
    };
    session.onpaymentauthorized = function(event) {
        fetch(
            shopBaseUrl + 'index.php',
            {
                method: 'POST',
                body: prepareMollieApplePayRequestData({
                    cl: "mollieApplePay",
                    fnc: "finalizeMollieOrder",
                    detailsProductId: detailsProductId,
                    detailsProductAmount: mollieGetProductAmount(implementationPosition),
                    token: event.payment.token,
                    billingContact: event.payment.billingContact,
                    shippingContact: event.payment.shippingContact,
                })
            }
        ).then(function (response) {
            response.json().then(function (jsonResponse) {
                if (jsonResponse.success !== undefined && jsonResponse.success === true) {
                    session.completePayment({status: 'STATUS_SUCCESS'});
                    window.location.replace(jsonResponse.redirectUrl);
                } else {
                    if (jsonResponse.error !== undefined) {
                        session.completePayment({status: 'STATUS_FAILURE', errors: [new ApplePayError(jsonResponse.error.code, jsonResponse.error.contactField, jsonResponse.error.message)]});
                    } else {
                        if (jsonResponse.errormessage !== undefined) {
                            alert('Apple Pay payment was not successful. An error occurred: ' + jsonResponse.errormessage);
                        } else {
                            alert('Apple Pay payment was not successful.');
                        }
                        session.abort();
                    }
                }
            });
        }).catch(function (error) {
            console.log('Apple Pay: An error occurred. ' + error.status);
            session.abort();
        });
    };
    session.begin();
}
