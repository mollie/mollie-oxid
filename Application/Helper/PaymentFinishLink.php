<?php

namespace Mollie\Payment\Application\Helper;

/**
 * Helper for the payment finish link (MollieFinishPayment controller).
 * The link restarts the payment of an unpaid order (second chance email, admin order view).
 * Every order has its own random token (oxorder.MOLLIEFINISHPAYMENTTOKEN) which has to be part of the link.
 * The token is reset when the link is called with a non-matching token and can be renewed in the admin
 * by sending a new second chance email.
 */
class PaymentFinishLink
{
    /**
     * Url parameter containing the token
     */
    const PARAM_TOKEN = 'mtoken';

    /**
     * Value of the token field when the token was invalidated
     */
    const TOKEN_INVALIDATED = 'INVALIDATED';

    /**
     * Session variable containing the id of the order whose payment was restarted via the payment finish link
     */
    const SESSION_VAR_REINIT_ORDER_ID = 'mollieReinitOrderId';

    /**
     * @var PaymentFinishLink
     */
    protected static $oInstance = null;

    /**
     * Create singleton instance of payment finish link helper
     *
     * @return PaymentFinishLink
     */
    public static function getInstance()
    {
        if (self::$oInstance === null) {
            self::$oInstance = oxNew(self::class);
        }
        return self::$oInstance;
    }

    /**
     * Resets singleton class
     * Needed for unit testing
     *
     * @return void
     */
    public static function destroyInstance()
    {
        self::$oInstance = null;
    }

    /**
     * Generates a new random token
     *
     * @return string
     */
    public function generateToken()
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Checks if the given value has the format of a token
     * Used to distinguish broken links (i.e. truncated by an email client) from non-matching tokens
     *
     * @param  mixed $sToken
     * @return bool
     */
    public function isWellFormedToken($sToken)
    {
        return is_string($sToken) && preg_match('/^[0-9a-f]{64}$/', $sToken) === 1;
    }
}
