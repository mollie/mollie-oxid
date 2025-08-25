<?php

/*
 * This file is part of composer/ca-bundle.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
/*
 * This code has been taken from the composer/ca-bundle repository.
 * Code has been moved to the Mollie namespace to not create autoloader problems when updating the module
 * Unused code from the source class has been removed to not cause problems with the unknown referenced classes
namespace _PhpScoperfb65c95ebc2e\Composer\CaBundle;
*/

namespace Mollie\Api\HttpAdapter;

/**
 * @author Chris Smith <chris@cs278.org>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class CaBundle
{
    /**
     * Returns the path to the bundled CA file
     *
     * In case you don't want to trust the user or the system, you can use this directly
     *
     * @return string path to a CA bundle file
     */
    public static function getBundledCaBundlePath()
    {
        $caBundleFile = __DIR__ . '/res/cacert.pem';
        // cURL does not understand 'phar://' paths
        // see https://github.com/composer/ca-bundle/issues/10
        if (0 === \strpos($caBundleFile, 'phar://')) {
            $tempCaBundleFile = \tempnam(\sys_get_temp_dir(), 'openssl-ca-bundle-');
            if (\false === $tempCaBundleFile) {
                throw new \RuntimeException('Could not create a temporary file to store the bundled CA file');
            }
            \file_put_contents($tempCaBundleFile, \file_get_contents($caBundleFile));
            \register_shutdown_function(function () use($tempCaBundleFile) {
                @\unlink($tempCaBundleFile);
            });
            $caBundleFile = $tempCaBundleFile;
        }
        return $caBundleFile;
    }
}
