<?php

namespace Chewett\CacheNCrunch;


/**
 * Class CacheNCrunchLogger
 * Simple class to hold the log which is used in debug mode
 *
 * @package Chewett\CacheNCrunch
 * @author Christopher Hewett <chewett@hotmail.co.uk>
 */
class CNCLogger {

    private $debugLog = [];

    public function log($logString) {
        $this->debugLog[] = $logString;
    }

    public function getLog() {
        return $this->debugLog;
    }

}