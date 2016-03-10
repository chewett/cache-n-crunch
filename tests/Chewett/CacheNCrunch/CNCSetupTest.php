<?php
namespace Chewett\CacheNCrunch;


class CNCSetupTest extends \PHPUnit_Framework_TestCase {

    public function testSetup() {
        $cacheDir = __DIR__ . "/../../../build/output/";
        if(is_dir($cacheDir . "cache")) {
            rmdir($cacheDir . "cache");
        }

        CacheNCrunch::setUpCacheDirectory($cacheDir);
        CNCSetup::setupBaseDirs();

        $this->assertFileExists(CacheNCrunch::getCacheDirectory() . CacheNCrunch::$JS_LOADING_FILES);
        $this->assertFileExists(CacheNCrunch::getCacheDirectory() . CacheNCrunch::$JS_LOADING_FILES . CacheNCrunch::$JS_FILE_CACHE_DETAILS);



    }

}
