<?php

namespace Mollie\Payment\extend\Core;

use OxidEsales\Eshop\Core\Registry;

class Email extends Email_parent
{
    protected $_sMollieSecondChanceTemplate = 'mollie_second_chance.tpl';

    public function mollieSendSecondChanceEmail($oOrder, $sFinishPaymentUrl)
    {
        // add user defined stuff if there is any
        #$user = $this->_addUserRegisterEmail($user);

        // shop info
        $shop = $this->_getShop();

        //set mail params (from, fromName, smtp )
        $this->_setMailParams($shop);

        // create messages
        $smarty = $this->_getSmarty();

        $subject = Registry::getLang()->translateString('MOLLIE_SECOND_CHANCE_MAIL_SUBJECT', null, false) . " " . $shop->oxshops__oxname->getRawValue() . " (#" . $oOrder->oxorder__oxordernr->value . ")";

        $this->setViewData("order", $oOrder);
        $this->setViewData("shop", $shop);
        $this->setViewData("subject", $subject);
        $this->setViewData("sFinishPaymentUrl", $sFinishPaymentUrl);

        // Process view data array through oxOutput processor
        $this->_processViewArray();

        $oConfig = $this->getConfig();
        $oConfig->setAdminMode(false);

        $this->setBody($smarty->fetch($this->_sMollieSecondChanceTemplate));
        $this->setSubject($subject);

        $oConfig->setAdminMode(true);

        $fullName = $oOrder->oxorder__oxbillfname->getRawValue() . " " . $oOrder->oxorder__oxbilllname->getRawValue();

        $this->setRecipient($oOrder->oxorder__oxbillemail->value, $fullName);
        $this->setReplyTo($shop->oxshops__oxorderemail->value, $shop->oxshops__oxname->getRawValue());

        return $this->send();
    }
}
