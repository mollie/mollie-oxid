[{capture name="mollieApplePayEnable"}]
    function isApplePayAvailable()
    {
        try {
            return window.ApplePaySession && window.ApplePaySession.canMakePayments();
        } catch (error) {
            console.warn('Apple Pay could not be initialized:', error);
        }
        return false;
    }

    var applePayDiv = document.getElementById('container_[{$sPaymentID}]');
    if (isApplePayAvailable()) {
        applePayDiv.style.display = '';
    } else {
        applePayDiv.remove();
    }
[{/capture}]
[{oxscript add=$smarty.capture.mollieApplePayEnable}]
