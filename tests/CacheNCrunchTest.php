<?php

namespace Chewett\CacheNCrunch\Test;

use Chewett\CacheNCrunch\Cruncher;
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
        $jsHeaderFile = __DIR__ ."/../vendor/chewett/php-uglifyjs/build/headerfile.js";
        $cssHeaderFile = __DIR__ . "/../vendor/chewett/php-uglifycss/build/headerfile.css";

        return [
            "No headers" => [null, null],
            "JS Header file" => [$jsHeaderFile, null],
            "CSS Header file" => [null, $cssHeaderFile],
            "JS and CSS header files" => [$jsHeaderFile, $cssHeaderFile]
        ];
    }

    public function setUp() {
        if(is_dir(self::$CACHE_DIR)) {
            $fs = new Filesystem();
            $fs->remove(self::$CACHE_DIR);
        }

        CacheNCrunch::setUpCacheDirectory(self::$CACHE_DIR, '/build/output/cache/');
        CacheNCrunch::setDebugMode(false);
    }

    /**
     * @dataProvider headerFileProvider
     */
    public function testDebugModeCacheOutput($jsHeaderFile, $cssHeaderFile) {
        if($jsHeaderFile) {
            CacheNCrunch::setUglifyJsHeaderFile($jsHeaderFile);
        }
        if($cssHeaderFile) {
            CacheNCrunch::setUglifyCssHeaderFile($cssHeaderFile);
        }

        CacheNCrunch::setDebugMode(true);
        CacheNCrunch::registerJsFile("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");
        CacheNCrunch::registerCssFile("testCss", "/static/testCss.css", __DIR__ . "/../static/testCss.css");

        $this->assertEquals(
            "<link href='/static/testCss.css' rel='stylesheet'><script src='/static/testJs.js'></script>",
            CacheNCrunch::getScriptImports()
        );
    }

    /**
     * @dataProvider headerFileProvider
     */
    public function testNoCachePresentOutput($jsHeaderFile, $cssHeaderFile) {
        if($jsHeaderFile) {
            CacheNCrunch::setUglifyJsHeaderFile($jsHeaderFile);
        }
        if($cssHeaderFile) {
            CacheNCrunch::setUglifyCssHeaderFile($cssHeaderFile);
        }

        CacheNCrunch::registerJsFile("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");
        CacheNCrunch::registerCssFile("testCss", "/static/testCss.css", __DIR__ . "/../static/testCss.css");

        $this->assertEquals(
            "<link href='/static/testCss.css' rel='stylesheet'><script src='/static/testJs.js'></script>",
            CacheNCrunch::getScriptImports()
        );
    }

    /**
     * @dataProvider headerFileProvider
     */
    public function testCrunch($jsHeaderFile, $cssHeaderFile) {
        if($jsHeaderFile) {
            CacheNCrunch::setUglifyJsHeaderFile($jsHeaderFile);
        }
        if($cssHeaderFile) {
            CacheNCrunch::setUglifyCssHeaderFile($cssHeaderFile);
        }

        CacheNCrunch::registerJsFile("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");
        CacheNCrunch::registerCssFile("testCss", "/static/testCss.css", __DIR__ . "/../static/testCss.css");
        Cruncher::crunch();
    }

    /**
     * @dataProvider headerFileProvider
     */
    public function testCachePresentOutput($jsHeaderFile, $cssHeaderFile) {
        if($jsHeaderFile) {
            CacheNCrunch::setUglifyJsHeaderFile($jsHeaderFile);
        }
        if($cssHeaderFile) {
            CacheNCrunch::setUglifyCssHeaderFile($cssHeaderFile);
        }

        CacheNCrunch::registerJsFile("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");
        CacheNCrunch::registerCssFile("testCss", "/static/testCss.css", __DIR__ . "/../static/testCss.css");

        $this->assertEquals(
            "<link href='/static/testCss.css' rel='stylesheet'><script src='/static/testJs.js'></script>",
            CacheNCrunch::getScriptImports()
        );

        Cruncher::crunch();
        $importStatements = CacheNCrunch::getScriptImports();

        //Make sure there is only one import
        $this->assertEquals(1, substr_count($importStatements, "script src="));
        //Make sure its not importing the old testJs file directly
        $this->assertEquals(0, substr_count($importStatements, "testJs.js"));
    }

    /**
     * @dataProvider headerFileProvider
     */
    public function testMultiCachePresentOutput($jsHeaderFile, $cssHeaderFile) {
        if($jsHeaderFile) {
            CacheNCrunch::setUglifyJsHeaderFile($jsHeaderFile);
        }
        if($cssHeaderFile) {
            CacheNCrunch::setUglifyCssHeaderFile($cssHeaderFile);
        }

        CacheNCrunch::registerJsFile("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");
        CacheNCrunch::registerJsFile("testA", "/static/testA.js", __DIR__ . "/../static/testA.js");
        CacheNCrunch::registerCssFile("testCss", "/static/testCss.css", __DIR__ . "/../static/testCss.css");
        CacheNCrunch::registerCssFile("testA", "/static/testA.css", __DIR__ . "/../static/testA.css");

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
    public function testMultiCachePresentOutputTwoCrunches($jsHeaderFile, $cssHeaderFile) {
        $this->markTestIncomplete("Need to rework");
        if($jsHeaderFile) {
            CacheNCrunch::setUglifyJsHeaderFile($jsHeaderFile);
        }
        if($cssHeaderFile) {
            CacheNCrunch::setUglifyCssHeaderFile($cssHeaderFile);
        }

        CacheNCrunch::registerJsFile("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");
        CacheNCrunch::crunch();
        CacheNCrunch::removeJsFile("testJs");
        CacheNCrunch::registerJsFile("testA", "/static/testA.js", __DIR__ . "/../static/testA.js");
        CacheNCrunch::crunch();

        require self::$CACHE_DIR . CacheNCrunch::$CACHE_FILE_DIR . CacheNCrunch::$FILE_CACHE_DETAILS;
        $this->assertTrue(isset($JS_FILES));
        $this->assertCount(2, $JS_FILES);

    }


}
