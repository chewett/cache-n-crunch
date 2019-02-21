<?php
/**
 * User: Christopher Hewett
 * Date: 21/02/2019
 * Time: 08:50 PM
 */

namespace Chewett\CacheNCrunch\Test;

use Chewett\CacheNCrunch\CacheNCrunchException;


class CacheNCrunchExceptionTest extends \PHPUnit_Framework_TestCase {

    /**
     * @expectedException CacheNCrunchException
     */
    public function testBasicException() {
        throw new CacheNCrunchException("test message");
    }

    /**
     * @expectedException CacheNCrunchException
     */
    public function testBasicWithCodeException() {
        throw new CacheNCrunchException("test message", 12);
    }

    /**
     * @expectedException CacheNCrunchException
     */
    public function testBasicWithCodeAndPreviousException() {
        $prevException = new \Exception("Previous exception");

        throw new CacheNCrunchException("test message", 15, $prevException);
    }

    public function testBasicCreationOfException() {
        $e = new CacheNCrunchException("test message");
        $this->assertNotNull($e);
    }

    public function testBasicCreationOfWithCodeException() {
        $e = new CacheNCrunchException("test message", 12);
        $this->assertNotNull($e);
    }

    public function testBasicCreationOfWithCodeAndPreviousException() {
        $prevException = new \Exception("Previous exception");

        $e = new CacheNCrunchException("test message", 15, $prevException);
        $this->assertNotNull($e);
    }


}
