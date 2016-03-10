<?php
namespace Chewett\CacheNCrunch;


class CNCSetup {

    public static function setupBaseDirs() {
        if(!is_dir(CacheNCrunch::getCacheDirectory())) {
            mkdir(CacheNCrunch::getCacheDirectory());
        }

        $cachePhpConfigDir = CacheNCrunch::getCacheDirectory() . CacheNCrunch::$JS_LOADING_FILES;
        if(!is_dir($cachePhpConfigDir)) {
            mkdir($cachePhpConfigDir, 0777, true);
        }

        $jsFileCachePath = $cachePhpConfigDir . CacheNCrunch::$JS_FILE_CACHE_DETAILS;
        $jsFile =
            '<?php ' . PHP_EOL .
            '$JS_FILES = []; ' . PHP_EOL;


        file_put_contents($jsFileCachePath, $jsFile);
    }

}