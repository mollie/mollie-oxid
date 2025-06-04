<?php

namespace Mollie\Api\HttpAdapter;

use _PhpScoperfb65c95ebc2e\GuzzleHttp\Exception\ConnectException;
use _PhpScoperfb65c95ebc2e\GuzzleHttp\Exception\TransferException;
use _PhpScoperfb65c95ebc2e\GuzzleHttp\Middleware;
use _PhpScoperfb65c95ebc2e\GuzzleHttp\Psr7\Request;
use _PhpScoperfb65c95ebc2e\GuzzleHttp\Psr7\Response;
class Guzzle6And7RetryMiddlewareFactory
{
    /**
     * The maximum number of retries
     */
    public const MAX_RETRIES = 5;
    /**
     * The amount of milliseconds the delay is being increased with on each retry.
     */
    public const DELAY_INCREASE_MS = 1000;
    /**
     * @param bool $delay default to true, can be false to speed up tests
     *
     * @return callable
     */
    public function retry($delay = \true)
    {
        return \_PhpScoperfb65c95ebc2e\GuzzleHttp\Middleware::retry($this->newRetryDecider(), $delay ? $this->getRetryDelay() : $this->getZeroRetryDelay());
    }
    /**
     * Returns a method that takes the number of retries and returns the number of milliseconds
     * to wait
     *
     * @return callable
     */
    private function getRetryDelay()
    {
        return function ($numberOfRetries) {
            return static::DELAY_INCREASE_MS * $numberOfRetries;
        };
    }
    /**
     * Returns a method that returns zero milliseconds to wait
     *
     * @return callable
     */
    private function getZeroRetryDelay()
    {
        return function ($numberOfRetries) {
            return 0;
        };
    }
    /**
     * @return callable
     */
    private function newRetryDecider()
    {
        return function ($retries, \_PhpScoperfb65c95ebc2e\GuzzleHttp\Psr7\Request $request, ?\_PhpScoperfb65c95ebc2e\GuzzleHttp\Psr7\Response $response = null, ?\_PhpScoperfb65c95ebc2e\GuzzleHttp\Exception\TransferException $exception = null) {
            if ($retries >= static::MAX_RETRIES) {
                return \false;
            }
            if ($exception instanceof \_PhpScoperfb65c95ebc2e\GuzzleHttp\Exception\ConnectException) {
                return \true;
            }
            return \false;
        };
    }
}
