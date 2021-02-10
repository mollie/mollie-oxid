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
                $this->updateArticleStock($this->oxorderarticles__oxamount->value * -1, $this->getConfig()->getConfigParam('blAllowNegativeStock'));
            }
        }
    }

    /**
     * Calculate the quantity that is still refundable for this orderarticle
     *
     * @return int
     */
    public function mollieGetRefundableQuantity()
    {
        if ($this->oxorderarticles__mollieamountrefunded->value == $this->oxorderarticles__oxbrutprice->value) {
            return 0;
        }
        return ($this->oxorderarticles__oxamount->value - $this->oxorderarticles__molliequantityrefunded->value);
    }

    /**
     * Calculate the amount that is still refundable for this orderarticle
     *
     * @return double
     */
    public function mollieGetRefundableAmount()
    {
        if ($this->oxorderarticles__molliequantityrefunded->value == $this->oxorderarticles__oxamount->value) {
            return 0;
        }
        return ($this->oxorderarticles__oxbrutprice->value - $this->oxorderarticles__mollieamountrefunded->value);
    }
}
