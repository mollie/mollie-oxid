<?php

namespace Mollie\Api\HttpAdapter;

use _PhpScoperfb65c95ebc2e\GuzzleHttp\Client;
use _PhpScoperfb65c95ebc2e\GuzzleHttp\ClientInterface;
use _PhpScoperfb65c95ebc2e\GuzzleHttp\Exception\GuzzleException;
use _PhpScoperfb65c95ebc2e\GuzzleHttp\HandlerStack;
use _PhpScoperfb65c95ebc2e\GuzzleHttp\Psr7\Request;
use _PhpScoperfb65c95ebc2e\GuzzleHttp\RequestOptions as GuzzleRequestOptions;
use Mollie\Api\HttpAdapter\CaBundle;
use Mollie\Api\Exceptions\ApiException;
use _PhpScoperfb65c95ebc2e\Psr\Http\Message\ResponseInterface;
final class Guzzle6And7MollieHttpAdapter implements \Mollie\Api\HttpAdapter\MollieHttpAdapterInterface
{
    /**
     * Default response timeout (in seconds).
     */
    public const DEFAULT_TIMEOUT = 10;
    /**
     * Default connect timeout (in seconds).
     */
    public const DEFAULT_CONNECT_TIMEOUT = 2;
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    protected $httpClient;
    /**
     * Whether debugging is enabled. If debugging mode is enabled, the request will
     * be included in the ApiException. By default, debugging is disabled to prevent
     * sensitive request data from leaking into exception logs.
     *
     * @var bool
     */
    protected $debugging = \false;
    public function __construct(\_PhpScoperfb65c95ebc2e\GuzzleHttp\ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
    /**
     * Instantiate a default adapter with sane configuration for Guzzle 6 or 7.
     *
     * @return static
     */
    public static function createDefault()
    {
        $retryMiddlewareFactory = new \Mollie\Api\HttpAdapter\Guzzle6And7RetryMiddlewareFactory();
        $handlerStack = \_PhpScoperfb65c95ebc2e\GuzzleHttp\HandlerStack::create();
        $handlerStack->push($retryMiddlewareFactory->retry());
        $client = new \_PhpScoperfb65c95ebc2e\GuzzleHttp\Client([\_PhpScoperfb65c95ebc2e\GuzzleHttp\RequestOptions::VERIFY => \Mollie\Api\HttpAdapter\CaBundle::getBundledCaBundlePath(), \_PhpScoperfb65c95ebc2e\GuzzleHttp\RequestOptions::TIMEOUT => self::DEFAULT_TIMEOUT, \_PhpScoperfb65c95ebc2e\GuzzleHttp\RequestOptions::CONNECT_TIMEOUT => self::DEFAULT_CONNECT_TIMEOUT, 'handler' => $handlerStack]);
        return new \Mollie\Api\HttpAdapter\Guzzle6And7MollieHttpAdapter($client);
    }
    /**
     * Send a request to the specified Mollie api url.
     *
     * @param string $httpMethod
     * @param string $url
     * @param array $headers
     * @param string $httpBody
     * @return \stdClass|null
     * @throws \Mollie\Api\Exceptions\ApiException
     */
    public function send($httpMethod, $url, $headers, $httpBody)
    {
        $request = new \_PhpScoperfb65c95ebc2e\GuzzleHttp\Psr7\Request($httpMethod, $url, $headers, $httpBody);
        try {
            $response = $this->httpClient->send($request, ['http_errors' => \false]);
        } catch (\_PhpScoperfb65c95ebc2e\GuzzleHttp\Exception\GuzzleException $e) {
            // Prevent sensitive request data from ending up in exception logs unintended
            if (!$this->debugging) {
                $request = null;
            }
            // Not all Guzzle Exceptions implement hasResponse() / getResponse()
            if (\method_exists($e, 'hasResponse') && \method_exists($e, 'getResponse')) {
                if ($e->hasResponse()) {
                    throw \Mollie\Api\Exceptions\ApiException::createFromResponse($e->getResponse(), $request);
                }
            }
            throw new \Mollie\Api\Exceptions\ApiException($e->getMessage(), $e->getCode(), null, $request, null);
        }
        return $this->parseResponseBody($response);
    }
    /**
     * Whether this http adapter provides a debugging mode. If debugging mode is enabled, the
     * request will be included in the ApiException.
     *
     * @return true
     */
    public function supportsDebugging()
    {
        return \true;
    }
    /**
     * Whether debugging is enabled. If debugging mode is enabled, the request will
     * be included in the ApiException. By default, debugging is disabled to prevent
     * sensitive request data from leaking into exception logs.
     *
     * @return bool
     */
    public function debugging()
    {
        return $this->debugging;
    }
    /**
     * Enable debugging. If debugging mode is enabled, the request will
     * be included in the ApiException. By default, debugging is disabled to prevent
     * sensitive request data from leaking into exception logs.
     */
    public function enableDebugging()
    {
        $this->debugging = \true;
    }
    /**
     * Disable debugging. If debugging mode is enabled, the request will
     * be included in the ApiException. By default, debugging is disabled to prevent
     * sensitive request data from leaking into exception logs.
     */
    public function disableDebugging()
    {
        $this->debugging = \false;
    }
    /**
     * Parse the PSR-7 Response body
     *
     * @param ResponseInterface $response
     * @return \stdClass|null
     * @throws ApiException
     */
    private function parseResponseBody(\_PhpScoperfb65c95ebc2e\Psr\Http\Message\ResponseInterface $response)
    {
        $body = (string) $response->getBody();
        if (empty($body) && $response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            return null;
        }
        $object = @\json_decode($body);
        if (\json_last_error() !== \JSON_ERROR_NONE) {
            throw new \Mollie\Api\Exceptions\ApiException("Unable to decode Mollie response: '{$body}'.");
        }
        if ($response->getStatusCode() >= 400) {
            throw \Mollie\Api\Exceptions\ApiException::createFromResponse($response, null);
        }
        return $object;
    }
    /**
     * The version number for the underlying http client, if available. This is used to report the UserAgent to Mollie,
     * for convenient support.
     * @example Guzzle/6.3
     *
     * @return string|null
     */
    public function versionString()
    {
        if (\defined('\\GuzzleHttp\\ClientInterface::MAJOR_VERSION')) {
            // Guzzle 7
            return "Guzzle/" . \_PhpScoperfb65c95ebc2e\GuzzleHttp\ClientInterface::MAJOR_VERSION;
        } elseif (\defined('\\GuzzleHttp\\ClientInterface::VERSION')) {
            // Before Guzzle 7
            return "Guzzle/" . \_PhpScoperfb65c95ebc2e\GuzzleHttp\ClientInterface::VERSION;
        }
        return null;
    }
}
