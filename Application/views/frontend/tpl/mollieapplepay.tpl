[{capture name="mollieApplePayEnable"}]
    var applePayDiv = document.getElementById('container_[{$sPaymentID}]');
    if (isApplePayAvailable()) {
        applePayDiv.style.display = '';
    } else {
        applePayDiv.remove();
    }
[{/capture}]
[{oxscript include=$oViewConf->getModuleUrl('molliepayment','out/src/js/mollie.js')}]
[{oxscript add=$smarty.capture.mollieApplePayEnable}]
