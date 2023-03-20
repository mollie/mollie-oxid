<?php

namespace Mollie\Payment\Application\Helper;

class Logger
{
    /**
     * Checks for existance of different file write methods and writes an error message to a file
     *
     * @param  string       $sMessage
     * @param  string|false $sFile
     * @return void
     */
    public static function logMessage($sMessage, $sFile = false)
    {
        $sMessage = date('Y-m-d H:i:s - ').$sMessage.PHP_EOL;

        if ($sFile === false) {
            $sFile = getShopBasePath().DIRECTORY_SEPARATOR."log".DIRECTORY_SEPARATOR."MollieErrors.log";
        }

        if (function_exists("error_log")) {
            error_log($sMessage, 3, $sFile);
        } elseif (function_exists("file_put_contents")) {
            file_put_contents($sFile, $sMessage, FILE_APPEND);
        } elseif (function_exists("fopen") && function_exists("fwrite") && function_exists("fclose")) {
            $oFile = fopen($sFile, "a");
            if ($oFile) {
                fwrite($oFile, $sMessage);
                fclose($oFile);
            }
        }
    }
}
