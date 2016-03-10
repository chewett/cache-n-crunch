<?php

namespace Chewett\CacheNCrunch;

/**
 * Class CacheNCrunchTest
 * @package Chewett\CacheNCrunch
 * @author Christopher Hewett <chewett@hotmail.co.uk>
 */
class CacheNCrunchTest extends \PHPUnit_Framework_TestCase {

    public function setUp() {
        $cacheDir = __DIR__ . "/../../../build/output/";
        if(is_dir($cacheDir . "cache")) {
            rmdir($cacheDir . "cache");
        }

        CacheNCrunch::setUpCacheDirectory($cacheDir);
        CacheNCrunch::setDebug(false);
    }

    public function testDebugModeCacheOutput() {
        CacheNCrunch::setDebug(true);
        CacheNCrunch::register("thing", "foobar", "foobar");

        $this->assertEquals(
            "<script src='foobar'></script>",
            CacheNCrunch::getScriptImports()
        );
    }

    public function testNoCachePresentOutput() {
        CacheNCrunch::register("thing", "foobar", "foobar");

        $this->assertEquals(
            "<script src='foobar'></script>",
            CacheNCrunch::getScriptImports()
        );
    }

    public function testCachePresentOutput() {
        CacheNCrunch::register("thing", "foobar", "foobar");
    }

}
