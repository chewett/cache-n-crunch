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

    private function getCacheDir() {
        return __DIR__ . "/../build/output/test_cache/" . str_replace('"', '_', $this->getName(true)) . "/";
    }

    private function getCacheFile() {
        return $this->getCacheDir() . "/CacheNCrunch/cacheFile.php";
    }

    public function setUp() {
        $cacheDir = $this->getCacheDir();

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
    public function testNoFilesToCrunch($jsHeaderFile, $cssHeaderFile) {
        if($jsHeaderFile) {
            $this->cnc->setUglifyJsHeaderFile($jsHeaderFile);
        }
        if($cssHeaderFile) {
            $this->cnc->setUglifyCssHeaderFile($cssHeaderFile);
        }

        $this->cnc->crunch();

        $importStatements = $this->cnc->getScriptImports();

        //Make sure there is no import statements at all
        $this->assertEquals(0, substr_count($importStatements, "script src="));
        $this->assertEquals(0, substr_count($importStatements, "link href="));
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
     * Tests to see whether the crunched file changes when the header file changes
     */
    public function testJsHeaderFileChanges() {
        $jsHeaderFile = __DIR__ ."/../vendor/chewett/php-uglifyjs/build/headerfile.js";
        $jsHeaderFile2  = __DIR__ . "/../vendor/chewett/php-uglifyjs/build/emptyFile.js";

        $this->cnc->setUglifyJsHeaderFile($jsHeaderFile);
        $this->cnc->registerJsFile("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");
        $this->cnc->crunch();

        $JS_FILES = [];
        require $this->getCacheFile();

        $allCrushedKeys = array_keys($JS_FILES);

        //Make sure there is only one file
        $this->assertCount(1, $allCrushedKeys);

        $crushedFilePath = $JS_FILES[$allCrushedKeys[0]]['cachePath'];
        $crushedJsFile = file_get_contents($crushedFilePath);

        //Check we have the copyright line in the full file
        $this->assertEquals(1, substr_count($crushedJsFile, "chewett/php-uglify"));

        //Now change the file and re-crush
        $this->cnc->setUglifyJsHeaderFile($jsHeaderFile2);
        $this->cnc->crunch();

        $JS_FILES = [];
        require $this->getCacheFile();

        $allCrushedKeys = array_keys($JS_FILES);

        //Make sure there is only one file
        $this->assertCount(1, $allCrushedKeys);

        $crushedFilePath = $JS_FILES[$allCrushedKeys[0]]['cachePath'];
        $crushedJsFile = file_get_contents($crushedFilePath);

        //Check we no longer have the copyright details in the output
        $this->assertEquals(0, substr_count($crushedJsFile, "chewett/php-uglify"));
    }

    /**
     * Tests to see whether the crunched file is rebuilt when the header file changes
     */
    public function testCssHeaderFileChanges() {
        $cssHeaderFile = __DIR__ . "/../vendor/chewett/php-uglifycss/build/headerfile.css";
        $cssHeaderFile2 = __DIR__ . "/../vendor/chewett/php-uglifycss/build/emptyFile.css";

        $this->cnc->setUglifyJsHeaderFile($cssHeaderFile);
        $this->cnc->registerCssFile("testCss", "/static/testCss.css", __DIR__ . "/../static/testCss.css");
        $this->cnc->crunch();

        $CSS_FILES = [];
        require $this->getCacheFile();

        $allCrushedKeys = array_keys($CSS_FILES);

        //Make sure there is only one file
        $this->assertCount(1, $allCrushedKeys);

        $crushedFilePath = $CSS_FILES[$allCrushedKeys[0]]['cachePath'];
        $crushedCssFile = file_get_contents($crushedFilePath);

        //Check we have the copyright line in the full file
        $this->assertEquals(1, substr_count($crushedCssFile, "chewett/php-uglify"));

        //Now change the file and re-crush
        $this->cnc->setUglifyJsHeaderFile($cssHeaderFile2);
        $this->cnc->crunch();

        $CSS_FILES = [];
        require $this->getCacheFile();

        $allCrushedKeys = array_keys($CSS_FILES);

        //Make sure there is only one file
        $this->assertCount(1, $allCrushedKeys);

        $crushedFilePath = $CSS_FILES[$allCrushedKeys[0]]['cachePath'];
        $crushedCssFile = file_get_contents($crushedFilePath);

        //Check we no longer have the copyright details in the output
        $this->assertEquals(0, substr_count($crushedCssFile, "chewett/php-uglify"));


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

    /**
     * Test to make sure that when we ask not to serve crunched files, we dont.
     * @dataProvider headerFileProvider
     */
    public function testDontServeCrunchedFiles($jsHeaderFile, $cssHeaderFile) {
        if($jsHeaderFile) {
            $this->cnc->setUglifyJsHeaderFile($jsHeaderFile);
        }
        if($cssHeaderFile) {
            $this->cnc->setUglifyCssHeaderFile($cssHeaderFile);
        }
        $this->cnc->setDontServeCrunchedFiles(true);
        $this->cnc->registerJsFile("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");
        $this->cnc->registerCssFile("testCss", "/static/testCss.css", __DIR__ . "/../static/testCss.css");
        $this->cnc->crunch();

        $this->assertEquals(
            "<link href='/static/testCss.css' rel='stylesheet'><script src='/static/testJs.js'></script>",
            $this->cnc->getScriptImports()
        );
    }

    /**
     * Get script imports in debug mode, Then turn off debug mode and get it again.
     *
     * If crunching in debug mode is turned on, this should have crunched already, and not in debug mode should load the correct data
     * @dataProvider headerFileProvider
     */
    public function testDebugModeHasCrunched($jsHeaderFile, $cssHeaderFile) {
        if($jsHeaderFile) {
            $this->cnc->setUglifyJsHeaderFile($jsHeaderFile);
        }
        if($cssHeaderFile) {
            $this->cnc->setUglifyCssHeaderFile($cssHeaderFile);
        }
        $this->cnc->setCrunchAlwaysOnDevMode(true);
        $this->cnc->setDebugMode(true);
        $this->cnc->registerJsFile("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");
        $this->cnc->registerCssFile("testCss", "/static/testCss.css", __DIR__ . "/../static/testCss.css");
        $this->assertEquals(
            "<link href='/static/testCss.css' rel='stylesheet'><script src='/static/testJs.js'></script>",
            $this->cnc->getScriptImports()
        );
        $this->cnc->setDebugMode(false);

        $importStatements = $this->cnc->getScriptImports();

        //Make sure there is only one import for each type
        $this->assertEquals(1, substr_count($importStatements, "script src="));
        $this->assertEquals(1, substr_count($importStatements, "link href="));
        //Make sure its not importing the old files directly
        $this->assertEquals(0, substr_count($importStatements, "testJs.js"));
        $this->assertEquals(0, substr_count($importStatements, "testCss.css"));
    }

    /**
     * Get script imports in debug mode, Then turn off debug mode and get it again.
     *
     * This shouldnt crunch in debug mode, so it should correctly still return the specific imports
     * @dataProvider headerFileProvider
     */
    public function testDebugModeHasNotCrunched($jsHeaderFile, $cssHeaderFile) {
        if ($jsHeaderFile) {
            $this->cnc->setUglifyJsHeaderFile($jsHeaderFile);
        }
        if ($cssHeaderFile) {
            $this->cnc->setUglifyCssHeaderFile($cssHeaderFile);
        }
        $this->cnc->setCrunchAlwaysOnDevMode(false);
        $this->cnc->setDebugMode(true);
        $this->cnc->registerJsFile("testJs", "/static/testJs.js", __DIR__ . "/../static/testJs.js");
        $this->cnc->registerCssFile("testCss", "/static/testCss.css", __DIR__ . "/../static/testCss.css");
        $this->assertEquals(
            "<link href='/static/testCss.css' rel='stylesheet'><script src='/static/testJs.js'></script>",
            $this->cnc->getScriptImports()
        );
        $this->cnc->setDebugMode(false);

        //Make sure that this still hasnt crushed the files
        $this->assertEquals(
            "<link href='/static/testCss.css' rel='stylesheet'><script src='/static/testJs.js'></script>",
            $this->cnc->getScriptImports()
        );
    }


}
