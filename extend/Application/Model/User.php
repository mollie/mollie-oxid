<?php


namespace Mollie\Payment\extend\Application\Model;


class User extends User_parent
{
    /**
     * Trigger setting auto groups when user is not logged in yet
     *
     * @return void
     */
    public function mollieSetAutoGroups()
    {
        $this->_setAutoGroups($this->oxuser__oxcountryid->value);
    }
}