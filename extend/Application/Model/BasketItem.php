<?php

namespace Mollie\Payment\extend\Application\Model;

class BasketItem extends BasketItem_parent
{
    /**
     * Empties oArticle property.
     * Is used in FinishOrders cronjob.
     * In this scenario there is a OrderArticle object in _oArticle and not a Article object.
     * This can lead to problems when order email is generated.
     * Resetting it so when getArticle() is called a new Article object will be loaded.
     * Added in PIOXD-280
     *
     * @return void
     */
    public function mollieUnsetArticle()
    {
        $this->_oArticle = null;
    }
}
