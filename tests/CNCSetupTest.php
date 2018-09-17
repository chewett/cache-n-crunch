<?php
namespace Chewett\CacheNCrunch\Test;

use Chewett\CacheNCrunch\CacheNCrunch;
use Symfony\Component\Filesystem\Filesystem;


class CNCSetupTest extends \PHPUnit_Framework_TestCase {

    public function testSetup() {
        $cacheDir = __DIR__ . "/../build/output/cache/";

        if(is_dir($cacheDir)) {
            $fs = new Filesystem();
            $fs->remove($cacheDir);
        }

        CacheNCrunch::setUpCacheDirectory($cacheDir, '/build/output/cache/');
        CacheNCrunch::setDebugMode(false);

        $this->assertFileExists(CacheNCrunch::getCacheDirectory() . CacheNCrunch::$CACHE_FILE_DIR);
        $this->assertFileExists(CacheNCrunch::getCacheDirectory() . CacheNCrunch::$CACHE_FILE_DIR . CacheNCrunch::$FILE_CACHE_DETAILS);

    }

}
