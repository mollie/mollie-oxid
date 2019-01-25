<?php

// scoper-composer-autoload.php @generated by PhpScoper

$loader = require_once __DIR__.'/composer-autoload.php';

// Functions whitelisting. For more information see:
// https://github.com/humbug/php-scoper/blob/master/README.md#functions-whitelisting
if (!function_exists('database_read')) {
    function database_read() {
        return \_PhpScoper5c46f59d284a1\database_read(...func_get_args());
    }
}
if (!function_exists('database_write')) {
    function database_write() {
        return \_PhpScoper5c46f59d284a1\database_write(...func_get_args());
    }
}
if (!function_exists('printOrders')) {
    function printOrders() {
        return \_PhpScoper5c46f59d284a1\printOrders(...func_get_args());
    }
}
if (!function_exists('composerRequire41faa85cc3c0c35e5aea8f62d12fa56f')) {
    function composerRequire41faa85cc3c0c35e5aea8f62d12fa56f() {
        return \_PhpScoper5c46f59d284a1\composerRequire41faa85cc3c0c35e5aea8f62d12fa56f(...func_get_args());
    }
}
if (!function_exists('getallheaders')) {
    function getallheaders() {
        return \_PhpScoper5c46f59d284a1\getallheaders(...func_get_args());
    }
}

return $loader;