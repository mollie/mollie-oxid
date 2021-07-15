<?php

namespace Mollie\Payment\extend\Core;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Core\ShopVersion;
use OxidEsales\EshopCommunity\Internal\Framework\Templating\TemplateRendererBridgeInterface;

class Email extends Email_parent
{
    protected $_sMollieSecondChanceTemplate = 'mollie_second_chance.tpl';

    protected $_sMollieSupportEmail = 'mollie_support_email.tpl';

    /**
     * Returns old or current template renderer
     *
     * @return \OxidEsales\EshopCommunity\Internal\Framework\Templating\TemplateRendererInterface|\Smarty
     */
    protected function mollieGetRenderer()
    {
        if (method_exists($this, 'getRenderer')) { // mechanism changed in Oxid 6.2
            // content of getRenderer method... for whatever reason someone put the method on private so that it cant be used here
            $bridge = $this->getContainer()->get(TemplateRendererBridgeInterface::class);
            $bridge->setEngine($this->_getSmarty());

            return $bridge->getTemplateRenderer();
        }
        return $this->_getSmarty();
    }

    /**
     * Renders the template with old or current method
     *
     * @param \OxidEsales\EshopCommunity\Internal\Framework\Templating\TemplateRendererInterface|\Smarty $oRenderer
     * @param string $sTemplate
     * @return string
     */
    protected function mollieRenderTemplate($oRenderer, $sTemplate)
    {
        if (method_exists($this, 'getRenderer')) { // mechanism changed in Oxid 6.2
            return $oRenderer->renderTemplate($sTemplate, $this->getViewData());
        }
        return $oRenderer->fetch($sTemplate);
    }

    /**
     * Sends second chance email to customer
     *
     * @param object $oOrder
     * @param string $sFinishPaymentUrl
     * @return bool
     */
    public function mollieSendSecondChanceEmail($oOrder, $sFinishPaymentUrl)
    {
        // add user defined stuff if there is any
        #$user = $this->_addUserRegisterEmail($user);

        // shop info
        $shop = $this->_getShop();

        //set mail params (from, fromName, smtp )
        $this->_setMailParams($shop);

        // create messages
        $oRenderer = $this->mollieGetRenderer();

        $subject = Registry::getLang()->translateString('MOLLIE_SECOND_CHANCE_MAIL_SUBJECT', null, false) . " " . $shop->oxshops__oxname->getRawValue() . " (#" . $oOrder->oxorder__oxordernr->value . ")";

        $this->setViewData("order", $oOrder);
        $this->setViewData("shop", $shop);
        $this->setViewData("subject", $subject);
        $this->setViewData("sFinishPaymentUrl", $sFinishPaymentUrl);

        // Process view data array through oxOutput processor
        $this->_processViewArray();

        $oConfig = $this->getConfig();
        $oConfig->setAdminMode(false);

        $this->setBody($this->mollieRenderTemplate($oRenderer, $this->_sMollieSecondChanceTemplate));
        $this->setSubject($subject);

        $oConfig->setAdminMode(true);

        $fullName = $oOrder->oxorder__oxbillfname->getRawValue() . " " . $oOrder->oxorder__oxbilllname->getRawValue();

        $this->setRecipient($oOrder->oxorder__oxbillemail->value, $fullName);
        $this->setReplyTo($shop->oxshops__oxorderemail->value, $shop->oxshops__oxname->getRawValue());

        if (defined('OXID_PHP_UNIT')) { // dont send email when unittesting
            return true;
        }

        return $this->send();
    }

    public function mollieSendSupportEmail($sName, $sEmail, $sSubject, $sEnquiryText, $sModuleVersion, $sAttachmentPath = false)
    {
        // add user defined stuff if there is any
        #$user = $this->_addUserRegisterEmail($user);

        // shop info
        $shop = $this->_getShop();

        //set mail params (from, fromName, smtp )
        $this->_setMailParams($shop);

        // create messages
        $oRenderer = $this->mollieGetRenderer();

        $this->setViewData("shop", $shop);
        $this->setViewData("subject", "");
        $this->setViewData("enquiry", $sEnquiryText);
        $this->setViewData("shopversion", \OxidEsales\Facts\Facts::getEdition()." ".ShopVersion::getVersion());
        $this->setViewData("moduleversion", $sModuleVersion);
        $this->setViewData("contact_name", $sName);
        $this->setViewData("contact_email", $sEmail);

        // Process view data array through oxOutput processor
        $this->_processViewArray();

        $oConfig = $this->getConfig();
        $oConfig->setAdminMode(false);

        $this->setBody($this->mollieRenderTemplate($oRenderer, $this->_sMollieSupportEmail));
        $this->setSubject("Mollie Support Oxid: ".$sSubject);

        if ($sAttachmentPath !== false) {
            $this->addAttachment($sAttachmentPath, 'Mollie.log');
        }

        $oConfig->setAdminMode(true);

        $this->setRecipient("support@mollie.com", "Mollie Support");
        $this->setRecipient($sEmail, $sName);
        $this->setReplyTo($shop->oxshops__oxorderemail->value, $shop->oxshops__oxname->getRawValue());

        if (defined('OXID_PHP_UNIT')) { // dont send email when unittesting
            return true;
        }

        return $this->send();
    }
}
