<?php

namespace Mollie\Api\HttpAdapter;

use Mollie\Api\Exceptions\UnrecognizedClientException;
class MollieHttpAdapterPicker implements \Mollie\Api\HttpAdapter\MollieHttpAdapterPickerInterface
{
    /**
     * @param \GuzzleHttp\ClientInterface|\Mollie\Api\HttpAdapter\MollieHttpAdapterInterface|null|\stdClass $httpClient
     *
     * @return \Mollie\Api\HttpAdapter\MollieHttpAdapterInterface
     * @throws \Mollie\Api\Exceptions\UnrecognizedClientException
     */
    public function pickHttpAdapter($httpClient)
    {
        if (!$httpClient) {
            if ($this->guzzleIsDetected()) {
                $guzzleVersion = $this->guzzleMajorVersionNumber();
                if ($guzzleVersion && \in_array($guzzleVersion, [6, 7])) {
                    return \Mollie\Api\HttpAdapter\Guzzle6And7MollieHttpAdapter::createDefault();
                }
            }
            return new \Mollie\Api\HttpAdapter\CurlMollieHttpAdapter();
        }
        if ($httpClient instanceof \Mollie\Api\HttpAdapter\MollieHttpAdapterInterface) {
            return $httpClient;
        }
        if ($httpClient instanceof \_PhpScoperf7c63b60b99d\GuzzleHttp\ClientInterface) {
            return new \Mollie\Api\HttpAdapter\Guzzle6And7MollieHttpAdapter($httpClient);
        }
        throw new \Mollie\Api\Exceptions\UnrecognizedClientException('The provided http client or adapter was not recognized.');
    }
    /**
     * @return bool
     */
    private function guzzleIsDetected()
    {
        return \interface_exists('\\' . \_PhpScoperf7c63b60b99d\GuzzleHttp\ClientInterface::class);
    }
    /**
     * @return int|null
     */
    private function guzzleMajorVersionNumber()
    {
        // Guzzle 7
        if (\defined('\\GuzzleHttp\\ClientInterface::MAJOR_VERSION')) {
            return (int) \_PhpScoperf7c63b60b99d\GuzzleHttp\ClientInterface::MAJOR_VERSION;
        }
        // Before Guzzle 7
        if (\defined('\\GuzzleHttp\\ClientInterface::VERSION')) {
            return (int) \_PhpScoperf7c63b60b99d\GuzzleHttp\ClientInterface::VERSION[0];
        }
        return null;
    }
}
