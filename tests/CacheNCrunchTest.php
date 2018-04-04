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

    /**
     * Provides some example options to the tests to allow for a test with and without a header file
     * @return array
     */
    public function headerFileProvider() {
        return [
            "No header" => [null],
            "Header file" => [__DIR__ ."/../vendor/chewett/php-uglifyjs/build/headerfile.js"]
        ];
    }

    public function setUp() {
        if(is_dir(self::$CACHE_DIR)) {
            $fs = new Filesystem();
            $fs->remove(self::$CACHE_DIR);
        }

        CacheNCrunch::setUpCacheDirectory(self::$CACHE_DIR, '/build/output/cache/');
        CacheNCrunch::setDebug(false);
    }

    /**
     * @dataProvider headerFileProvider
     */
    public function testDebugModeCacheOutput($headerFile) {
        if($headerFile) {
            CacheNCrunch::setUglifyHeaderFile($headerFile);
        }

        CacheNCrunch::setDebug(true);
        CacheNCrunch::register("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");

        $this->assertEquals(
            "<script src='/static/testJs.js'></script>",
            CacheNCrunch::getScriptImports()
        );
    }

    /**
     * @dataProvider headerFileProvider
     */
    public function testNoCachePresentOutput($headerFile) {
        if($headerFile) {
            CacheNCrunch::setUglifyHeaderFile($headerFile);
        }

        CacheNCrunch::register("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");

        $this->assertEquals(
            "<script src='/static/testJs.js'></script>",
            CacheNCrunch::getScriptImports()
        );
    }

    /**
     * @dataProvider headerFileProvider
     */
    public function testCrunch($headerFile) {
        if($headerFile) {
            CacheNCrunch::setUglifyHeaderFile($headerFile);
        }

        CacheNCrunch::register("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");
        CacheNCrunch::crunch();
    }

    /**
     * @dataProvider headerFileProvider
     */
    public function testCachePresentOutput($headerFile) {
        if($headerFile) {
            CacheNCrunch::setUglifyHeaderFile($headerFile);
        }

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

    /**
     * @dataProvider headerFileProvider
     */
    public function testMultiCachePresentOutput($headerFile) {
        if($headerFile) {
            CacheNCrunch::setUglifyHeaderFile($headerFile);
        }

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

    /**
     * @dataProvider headerFileProvider
     */
    public function testMultiCachePresentOutputTwoCrunches($headerFile) {
        if($headerFile) {
            CacheNCrunch::setUglifyHeaderFile($headerFile);
        }

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
