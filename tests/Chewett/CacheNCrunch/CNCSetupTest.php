<?php
namespace Chewett\CacheNCrunch;


use Symfony\Component\Filesystem\Filesystem;


class CNCSetupTest extends \PHPUnit_Framework_TestCase {

    public function testSetup() {
        $cacheDir = __DIR__ . "/../../../build/output/cache/";

        if(is_dir($cacheDir)) {
            $fs = new Filesystem();
            $fs->remove($cacheDir);
        }

        CacheNCrunch::setUpCacheDirectory($cacheDir, '/build/output/cache/');
        CacheNCrunch::setDebug(false);

        $this->assertFileExists(CacheNCrunch::getCacheDirectory() . CacheNCrunch::$JS_LOADING_FILES);
        $this->assertFileExists(CacheNCrunch::getCacheDirectory() . CacheNCrunch::$JS_LOADING_FILES . CacheNCrunch::$JS_FILE_CACHE_DETAILS);

    }

}
