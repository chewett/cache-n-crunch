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

}
