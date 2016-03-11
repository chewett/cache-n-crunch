<?php

namespace Chewett\CacheNCrunch;
use Symfony\Component\Filesystem\Filesystem;


/**
 * Class CacheNCrunchTest
 * @package Chewett\CacheNCrunch
 * @author Christopher Hewett <chewett@hotmail.co.uk>
 */
class CacheNCrunchTest extends \PHPUnit_Framework_TestCase {

    public function setUp() {
        $cacheDir = __DIR__ . "/../../../build/output/cache/";

        if(is_dir($cacheDir)) {
            $fs = new Filesystem();
            $fs->remove($cacheDir);
        }

        CacheNCrunch::setUpCacheDirectory($cacheDir, '/build/output/cache/');
        CacheNCrunch::setDebug(false);
    }

    public function testDebugModeCacheOutput() {
        CacheNCrunch::setDebug(true);
        CacheNCrunch::register("testJs", "/static/testJs.js", __DIR__ . "/../../../static/testJs.js");

        $this->assertEquals(
            "<script src='/static/testJs.js'></script>",
            CacheNCrunch::getScriptImports()
        );
    }

    public function testNoCachePresentOutput() {
        CacheNCrunch::register("testJs", "/static/testJs.js", __DIR__ . "/../../../static/testJs.js");

        $this->assertEquals(
            "<script src='/static/testJs.js'></script>",
            CacheNCrunch::getScriptImports()
        );
    }

    public function testCrunch() {
        CacheNCrunch::register("testJs", "/static/testJs.js", __DIR__ . "/../../../static/testJs.js");
        CacheNCrunch::crunch();
    }

    public function testCachePresentOutput() {
        CacheNCrunch::register("testJs", "/static/testJs.js", __DIR__ . "/../../../static/testJs.js");

        $this->assertEquals(
            "<script src='/static/testJs.js'></script>",
            CacheNCrunch::getScriptImports()
        );

        CacheNCrunch::crunch();
        $this->assertEquals(
            "<script src='/build/output/cache/static/js/6ffaf172520927af80aaca83b0e74e48.js'></script>",
            CacheNCrunch::getScriptImports()
        );
    }


}
