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
    var formAmount = document.querySelectorAll('input[name="am"]')[0].value;
    if (formAmount !== undefined) {
        return formAmount;
    }
    return 1;
}

async function mollieGetProductBasketPrice(detailsProductId, implementationPosition) {
    try {
        var responseJson = await mollieSendAjaxRequest(shopBaseUrl + 'index.php', {
            cl: "mollieApplePay",
            fnc: "getProductBasketPrice",
            detailsProductId: detailsProductId,
            detailsProductAmount: mollieGetProductAmount(implementationPosition)
        });

        if (responseJson) {
            if (responseJson.productBasketPrice) {
                if (responseJson.productBasketPrice === false) {
                    return false;
                }
                if (!isNaN(responseJson.productBasketPrice)) {
                    responseJson.productBasketPrice = parseFloat(responseJson.productBasketPrice.toFixed(2));
                }
                return responseJson.productBasketPrice;
            }
            if (responseJson.errormessage && responseJson.showexception && responseJson.showexception == true) {
                alert(responseJson.errormessage);
            }
            if (responseJson.errors) {
                console.log('Apple Pay: Could not get products basket price. ' + responseJson.errors[0].message);
            }
        }
    } catch (error) {
        console.log(error);
    }

    return false;
}

/**
 * Formats an object into a URL-encoded query string, recursively encoding nested objects.
 *
 * @param {Object} obj - The object to be formatted into URL-encoded parameters.
 * @param {string} [prefix] - The prefix for nested object keys, used for recursion.
 * @return {string} A URL-encoded query string representation of the object.
 */
function mollieFormatParams(obj, prefix) {
    const str = [];

    for (let key in obj) {
        if (!obj.hasOwnProperty(key)) continue;

        const k = prefix ? prefix + "[" + key + "]" : key;
        const v = obj[key];

        if (typeof v === "object" && v !== null) {
            str.push(mollieFormatParams(v, k)); // recursion
        } else {
            str.push(encodeURIComponent(k) + "=" + encodeURIComponent(v));
        }
    }

    return str.join("&");
}

/**
 * Sends an AJAX POST request to the specified URL with the provided parameters formatted for Mollie.
 *
 * @param {string} url - The endpoint URL to send the request to.
 * @param {Object} params - An object containing the key-value pairs to be sent as the body of the request.
 * @return {Promise<Object>} A promise that resolves to the JSON response from the server.
 * @throws {Error} Throws an error if the response status is not OK and includes the response status and text.
 */
async function mollieSendAjaxRequest(url, params) {
    const response = await fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: mollieFormatParams(params)
    });

    if (!response.ok) {
        const text = await response.text();
        throw new Error("HTTP " + response.status + ": " + text);
    }

    return response.json();
}

async function mollieInitApplePay(countryCode, currencyCode, shopName, price, delivery_price, detailsProductId, totalLabel, shippingLabel, shippingId, implementationPosition) {
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
    session.onvalidatemerchant = async function(event) {
        try {
            var responseJson = await mollieSendAjaxRequest(shopBaseUrl + 'index.php', {
                cl: "mollieApplePay",
                fnc: "getMerchantSession",
                validationUrl: event.validationURL,
            });

            if (responseJson) {
                if (responseJson.success === true && responseJson.merchantSession !== undefined) {
                    session.completeMerchantValidation(JSON.parse(responseJson.merchantSession));
                } else {
                    throw new Error('Apple Pay initialization failed');
                }
            }
        } catch (error) {
            console.log('Apple Pay: An error occured. ' + error);
            session.abort();
        }
    };
    session.onshippingcontactselected = async function(event) {
        try {
            if (implementationPosition === "Details") {
                // Adds product to basket with amount selected in details page - price could change there if amount is > 1
                let productBasketPrice = await mollieGetProductBasketPrice(detailsProductId, implementationPosition);
                if (productBasketPrice && productBasketPrice > price) {
                    // Update prices
                    price = productBasketPrice;
                    lineItems[0].amount = price;
                }
            }

            var response = await mollieSendAjaxRequest(shopBaseUrl + 'index.php', {
                cl: "mollieApplePay",
                fnc: "getDeliveryMethods",
                countryCode: event.shippingContact.countryCode,
                city: event.shippingContact.locality,
                zip: event.shippingContact.postalCode,
                detailsProductId: detailsProductId,
                detailsProductAmount: mollieGetProductAmount(implementationPosition)
            });

            if (response && response.success === true && response.shippingMethods !== undefined && response.shippingMethods.length > 0) {
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
        } catch (error) {
            console.log('Apple Pay: An error occured. ' + error);
            session.abort();
        }
    };
    session.onshippingmethodselected = async function(event) {
        try {
            var response = await mollieSendAjaxRequest(shopBaseUrl + 'index.php', {
                cl: "mollieApplePay",
                fnc: "updateShippingSet",
                shipSet: event.shippingMethod.identifier,
            });

            if (response) {
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
            }
        } catch (error) {
            console.log('Apple Pay: An error occured. ' + error);
            session.abort();
        }
    };
    session.onpaymentauthorized = async function(event) {
        try {
            var response = await mollieSendAjaxRequest(shopBaseUrl + 'index.php', {
                cl: "mollieApplePay",
                fnc: "finalizeMollieOrder",
                detailsProductId: detailsProductId,
                detailsProductAmount: mollieGetProductAmount(implementationPosition),
                token: event.payment.token,
                billingContact: event.payment.billingContact,
                shippingContact: event.payment.shippingContact,
            });

            if (response && response.success !== undefined && response.success === true) {
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
        } catch (error) {
            console.log('Apple Pay: An error occured. ' + error);
            session.abort();
        }
    };
    session.begin();
}
