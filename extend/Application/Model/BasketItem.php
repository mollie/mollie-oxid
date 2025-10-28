<?php

namespace Mollie\Payment\extend\Application\Model;

use Mollie\Payment\Application\Model\Cronjob\FinishOrders;

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

    /**
     * Retrieves the article .Throws an exception if article does not exist,
     * is not buyable or visible.
     *
     * @param bool   $blCheckProduct       checks if product is buyable and visible
     * @param string $sProductId           product id
     * @param bool   $blDisableLazyLoading disable lazy loading
     *
     * @throws oxArticleException exception in case of no current object product id is set
     * @throws oxNoArticleException exception in case if product not exitst or not visible
     * @throws oxArticleInputException exception if product is not buyable (stock and so on)
     *
     * @return \OxidEsales\Eshop\Application\Model\Article|oxOrderArticle
     */
    public function getArticle($blCheckProduct = false, $sProductId = null, $blDisableLazyLoading = false)
    {
        if (FinishOrders::mollieIsFinishingOrder() === true) {
            // We don't need to check product when finishing order - item was already bought and buyability was checked
            // Order emails may be incomplete - with missing products - when $blCheckProduct is true
            $blCheckProduct = false;
        }
        return parent::getArticle($blCheckProduct, $sProductId, $blDisableLazyLoading);
    }
}
