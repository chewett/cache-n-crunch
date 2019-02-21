<?php
namespace Chewett\CacheNCrunch\Test;

use Chewett\CacheNCrunch\CacheNCrunch;
use Symfony\Component\Filesystem\Filesystem;


class CNCSetupTest extends \PHPUnit_Framework_TestCase {

    public function testSetup() {
        $cacheDir = __DIR__ . "/../build/output/test_cache/cncsetuptest/";

        if(is_dir($cacheDir)) {
            $fs = new Filesystem();
            $fs->remove($cacheDir);
        }

        $cnc = new CacheNCrunch();
        $cnc->setUpCacheDirectory($cacheDir, "Foobar");
        $cnc->setDebugMode(false);

        //Make sure the directory has been created
        $this->assertFileExists($cacheDir);
    }

    public function testSetupMultipleTimes() {
        $cacheDir = __DIR__ . "/../build/output/test_cache/cncsetuptest2/";

        if(is_dir($cacheDir)) {
            $fs = new Filesystem();
            $fs->remove($cacheDir);
        }

        $cnc = new CacheNCrunch();
        $cnc->setUpCacheDirectory($cacheDir, "Foobar");
        $cnc->setDebugMode(false);

        //Make sure the directory has been created
        $this->assertFileExists($cacheDir);

        //Lets try and set it up multiple times
        $cnc->setUpCacheDirectory($cacheDir, "Foobar");
        $this->assertFileExists($cacheDir);
        $cnc->setUpCacheDirectory($cacheDir, "Foobar");
        $this->assertFileExists($cacheDir);
        $cnc->setUpCacheDirectory($cacheDir, "Foobar");
        $this->assertFileExists($cacheDir);
        $cnc->setUpCacheDirectory($cacheDir, "Foobar");
        $this->assertFileExists($cacheDir);
        $cnc->setUpCacheDirectory($cacheDir, "Foobar");
        $this->assertFileExists($cacheDir);
    }

}
