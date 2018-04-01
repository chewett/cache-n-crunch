<?php

namespace Chewett\CacheNCrunch\Test;

use Symfony\Component\Filesystem\Filesystem;
use Chewett\CacheNCrunch\CacheNCrunch;

/**
 * Class CacheNCrunchTest
 * @package Chewett\CacheNCrunch
 * @author Christopher Hewett <chewett@hotmail.co.uk>
 */
class CacheNCrunchTest extends \PHPUnit_Framework_TestCase {

    private static $CACHE_DIR = __DIR__ . "/../build/output/cache/";

    public function setUp() {
        if(is_dir(self::$CACHE_DIR)) {
            $fs = new Filesystem();
            $fs->remove(self::$CACHE_DIR);
        }

        CacheNCrunch::setUpCacheDirectory(self::$CACHE_DIR, '/build/output/cache/');
        CacheNCrunch::setDebug(false);
    }

    public function testDebugModeCacheOutput() {
        CacheNCrunch::setDebug(true);
        CacheNCrunch::register("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");

        $this->assertEquals(
            "<script src='/static/testJs.js'></script>",
            CacheNCrunch::getScriptImports()
        );
    }

    public function testNoCachePresentOutput() {
        CacheNCrunch::register("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");

        $this->assertEquals(
            "<script src='/static/testJs.js'></script>",
            CacheNCrunch::getScriptImports()
        );
    }

    public function testCrunch() {
        CacheNCrunch::register("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");
        CacheNCrunch::crunch();
    }

    public function testCachePresentOutput() {
        CacheNCrunch::register("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");

        $this->assertEquals(
            "<script src='/static/testJs.js'></script>",
            CacheNCrunch::getScriptImports()
        );

        CacheNCrunch::crunch();
        $importStatements = CacheNCrunch::getScriptImports();

        //Make sure there is only one import
        $this->assertEquals(1, substr_count($importStatements, "script src="));
        //Make sure its not importing the old testJs file directly
        $this->assertEquals(0, substr_count($importStatements, "testJs.js"));
    }

    public function testMultiCachePresentOutput() {
        CacheNCrunch::register("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");
        CacheNCrunch::register("testA", "/static/testA.js", __DIR__ . "/../static/testA.js");

        $this->assertEquals(
            "<script src='/static/testJs.js'></script><script src='/static/testA.js'></script>",
            CacheNCrunch::getScriptImports()
        );

        CacheNCrunch::crunch();
        $importStatements = CacheNCrunch::getScriptImports();

        //Make sure there is onl one import and we have crunched the files together
        $this->assertEquals(1, substr_count($importStatements, "script src="));
    }

    public function testMultiCachePresentOutputTwoCrunches() {
        CacheNCrunch::register("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");
        CacheNCrunch::crunch();
        CacheNCrunch::removeScript("testJs");
        CacheNCrunch::register("testA", "/static/testA.js", __DIR__ . "/../static/testA.js");
        CacheNCrunch::crunch();

        require self::$CACHE_DIR . CacheNCrunch::$JS_LOADING_FILES . CacheNCrunch::$JS_FILE_CACHE_DETAILS;
        $this->assertTrue(isset($JS_FILES));
        $this->assertCount(2, $JS_FILES);

    }


}
