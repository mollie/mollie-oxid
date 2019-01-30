<?php

namespace Mollie\Payment\extend\Application\Model;

class OrderArticle extends OrderArticle_parent
{
    /**
     * Uncancel order article
     *
     * @return void
     */
    public function mollieUncancelOrderArticle()
    {
        if ($this->oxorderarticles__oxstorno->value == 1) {
            $this->oxorderarticles__oxstorno = new \OxidEsales\Eshop\Core\Field(0);
            if ($this->save()) {
                $this->updateArticleStock(($this->oxorderarticles__oxamount->value * -1), $this->getConfig()->getConfigParam('blAllowNegativeStock'));
            }
        }
    }
}
