<?php

namespace Chewett\CacheNCrunch;


/**
 * Class CacheNCrunchLogger
 * Simple class to hold the log which is used in debug mode
 *
 * @package Chewett\CacheNCrunch
 * @author Christopher Hewett <chewett@hotmail.co.uk>
 */
class CacheNCrunchLogger {

    private static $debugLog = [];

    public static function log($logString) {
        self::$debugLog[] = $logString;
    }

    public static function getLog() {
        return self::$debugLog;
    }

}