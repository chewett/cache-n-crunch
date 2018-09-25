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
    /** @var CacheNCrunch */
    private $cnc = null;

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
        $cacheDir = __DIR__ . "/../build/output/test_cache/" . str_replace('"', '_', $this->getName(true)) . "/";

        if(is_dir($cacheDir)) {
            $fs = new Filesystem();
            $fs->remove($cacheDir);
        }

        $cnc = new CacheNCrunch();
        $cnc->setUpCacheDirectory($cacheDir, "Foobar");
        $cnc->setDebugMode(false);

        $this->cnc = $cnc;
    }

    /**
     * @dataProvider headerFileProvider
     */
    public function testDebugModeCacheOutput($jsHeaderFile, $cssHeaderFile) {
        if($jsHeaderFile) {
            $this->cnc->setUglifyJsHeaderFile($jsHeaderFile);
        }
        if($cssHeaderFile) {
            $this->cnc->setUglifyCssHeaderFile($cssHeaderFile);
        }
        $this->cnc->setDebugMode(true);
        $this->cnc->registerJsFile("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");
        $this->cnc->registerCssFile("testCss", "/static/testCss.css", __DIR__ . "/../static/testCss.css");

        $this->assertEquals(
            "<link href='/static/testCss.css' rel='stylesheet'><script src='/static/testJs.js'></script>",
            $this->cnc->getScriptImports()
        );
    }

    /**
     * @dataProvider headerFileProvider
     */
    public function testNoCachePresentOutput($jsHeaderFile, $cssHeaderFile) {
        if($jsHeaderFile) {
            $this->cnc->setUglifyJsHeaderFile($jsHeaderFile);
        }
        if($cssHeaderFile) {
            $this->cnc->setUglifyCssHeaderFile($cssHeaderFile);
        }

        $this->cnc->registerJsFile("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");
        $this->cnc->registerCssFile("testCss", "/static/testCss.css", __DIR__ . "/../static/testCss.css");

        $this->assertEquals(
            "<link href='/static/testCss.css' rel='stylesheet'><script src='/static/testJs.js'></script>",
            $this->cnc->getScriptImports()
        );
    }

    /**
     * @dataProvider headerFileProvider
     */
    public function testNoJsFilesToCrunch($jsHeaderFile, $cssHeaderFile) {
        if($jsHeaderFile) {
            $this->cnc->setUglifyJsHeaderFile($jsHeaderFile);
        }
        if($cssHeaderFile) {
            $this->cnc->setUglifyCssHeaderFile($cssHeaderFile);
        }

        $this->cnc->registerCssFile("testCss", "/static/testCss.css", __DIR__ . "/../static/testCss.css");
        $this->cnc->crunch();

        $importStatements = $this->cnc->getScriptImports();

        //Make sure there is only one import for CSS
        $this->assertEquals(0, substr_count($importStatements, "script src="));
        $this->assertEquals(1, substr_count($importStatements, "link href="));
        //Make sure its not importing the old files directly
        $this->assertEquals(0, substr_count($importStatements, "testJs.js"));
        $this->assertEquals(0, substr_count($importStatements, "testCss.css"));
    }

    /**
     * @dataProvider headerFileProvider
     */
    public function testNoCssFilesToCrunch($jsHeaderFile, $cssHeaderFile) {
        if($jsHeaderFile) {
            $this->cnc->setUglifyJsHeaderFile($jsHeaderFile);
        }
        if($cssHeaderFile) {
            $this->cnc->setUglifyCssHeaderFile($cssHeaderFile);
        }

        $this->cnc->registerJsFile("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");
        $this->cnc->crunch();

        $importStatements = $this->cnc->getScriptImports();

        //Make sure there is only one import for JS
        $this->assertEquals(1, substr_count($importStatements, "script src="));
        $this->assertEquals(0, substr_count($importStatements, "link href="));
        //Make sure its not importing the old files directly
        $this->assertEquals(0, substr_count($importStatements, "testJs.js"));
        $this->assertEquals(0, substr_count($importStatements, "testCss.css"));
    }

    /**
     * @dataProvider headerFileProvider
     */
    public function testCrunch($jsHeaderFile, $cssHeaderFile) {
        if($jsHeaderFile) {
            $this->cnc->setUglifyJsHeaderFile($jsHeaderFile);
        }
        if($cssHeaderFile) {
            $this->cnc->setUglifyCssHeaderFile($cssHeaderFile);
        }

        $this->cnc->registerJsFile("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");
        $this->cnc->registerCssFile("testCss", "/static/testCss.css", __DIR__ . "/../static/testCss.css");
        $this->cnc->crunch();
    }

    /**
     * @dataProvider headerFileProvider
     */
    public function testCachePresentOutput($jsHeaderFile, $cssHeaderFile) {
        if($jsHeaderFile) {
            $this->cnc->setUglifyJsHeaderFile($jsHeaderFile);
        }
        if($cssHeaderFile) {
            $this->cnc->setUglifyCssHeaderFile($cssHeaderFile);
        }

        $this->cnc->registerJsFile("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");
        $this->cnc->registerCssFile("testCss", "/static/testCss.css", __DIR__ . "/../static/testCss.css");

        $this->assertEquals(
            "<link href='/static/testCss.css' rel='stylesheet'><script src='/static/testJs.js'></script>",
            $this->cnc->getScriptImports()
        );

        $this->cnc->crunch();
        $importStatements = $this->cnc->getScriptImports();

        //Make sure there is only one import for each type
        $this->assertEquals(1, substr_count($importStatements, "script src="));
        $this->assertEquals(1, substr_count($importStatements, "link href="));
        //Make sure its not importing the old testJs/testCss file directly
        $this->assertEquals(0, substr_count($importStatements, "testJs.js"));
        $this->assertEquals(0, substr_count($importStatements, "testCss.css"));
    }

    /**
     * @dataProvider headerFileProvider
     */
    public function testMultiCachePresentOutput($jsHeaderFile, $cssHeaderFile) {
        if($jsHeaderFile) {
            $this->cnc->setUglifyJsHeaderFile($jsHeaderFile);
        }
        if($cssHeaderFile) {
            $this->cnc->setUglifyCssHeaderFile($cssHeaderFile);
        }

        $this->cnc->registerJsFile("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");
        $this->cnc->registerJsFile("testA", "/static/testA.js", __DIR__ . "/../static/testA.js");
        $this->cnc->registerCssFile("testCss", "/static/testCss.css", __DIR__ . "/../static/testCss.css");
        $this->cnc->registerCssFile("testA", "/static/testA.css", __DIR__ . "/../static/testA.css");

        $this->assertEquals(
            "<link href='/static/testCss.css' rel='stylesheet'><link href='/static/testA.css' rel='stylesheet'>" .
            "<script src='/static/testJs.js'></script><script src='/static/testA.js'></script>",
            $this->cnc->getScriptImports()
        );

        $this->cnc->crunch();
        $this->cnc->getScriptImports();
        $importStatements = $this->cnc->getScriptImports();

        //Make sure there is only one import for each type
        $this->assertEquals(1, substr_count($importStatements, "script src="));
        $this->assertEquals(1, substr_count($importStatements, "link href="));
        //Make sure its not importing the old files directly
        $this->assertEquals(0, substr_count($importStatements, "testJs.js"));
        $this->assertEquals(0, substr_count($importStatements, "testA.js"));
        $this->assertEquals(0, substr_count($importStatements, "testCss.css"));
        $this->assertEquals(0, substr_count($importStatements, "testA.css"));
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
