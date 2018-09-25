<?php
/**
 * Created by PhpStorm.
 * User: chewe
 * Date: 25/09/2018
 * Time: 12:10 PM
 */

namespace Chewett\CacheNCrunch\Test;


use Chewett\CacheNCrunch\CNCLogger;

class CNCLoggerTest extends \PHPUnit_Framework_TestCase {

    public function testLogOnce() {
        $logger = new CNCLogger();

        $logger->log("Log 123");
    }

    public function testGetEmptyLog() {
        $logger = new CNCLogger();

        $logArray = $logger->getLog();
        $this->assertCount(0, $logArray);
    }

    public function testGetSingleLog() {
        $logger = new CNCLogger();
        $logger->log("First log line");

        $logArray = $logger->getLog();
        $this->assertCount(1, $logArray);
    }

    public function testGetManyLogs() {
        $logger = new CNCLogger();
        $logger->log("First log line");
        $logger->log("Second log line");
        $logger->log("Third log line");
        $logger->log("Fourth log line");
        $logger->log("Fifth log line");

        $logArray = $logger->getLog();
        $this->assertCount(5, $logArray);
    }

}