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

        $jsCurrentFileCachePath = $cachePhpConfigDir . md5(time()) . ".php";
        $jsCurrentFileCachePath = str_replace("\\", "/", $jsCurrentFileCachePath);
        $jsCurrentFileCachePathFile =
            '<?php ' . PHP_EOL .
            '$JS_FILES = []; ' . PHP_EOL;
        file_put_contents($jsCurrentFileCachePath, $jsCurrentFileCachePathFile);

        $jsFileCachePath = $cachePhpConfigDir . CacheNCrunch::$JS_FILE_CACHE_DETAILS;
        $jsFileCachePathFile =
            '<?php ' . PHP_EOL .
            'require_once "' . $jsCurrentFileCachePath.'"; ' . PHP_EOL;

        file_put_contents($jsFileCachePath, $jsFileCachePathFile);
    }

    public static function storeDataToCacheFile($data) {
        if(!is_dir(CacheNCrunch::getCacheDirectory())) {
            mkdir(CacheNCrunch::getCacheDirectory());
        }

        $cachePhpConfigDir = CacheNCrunch::getCacheDirectory() . CacheNCrunch::$JS_LOADING_FILES;
        if(!is_dir($cachePhpConfigDir)) {
            mkdir($cachePhpConfigDir, 0777, true);
        }

        $jsCurrentFileCachePath = $cachePhpConfigDir . md5(time()) . ".php";
        $jsCurrentFileCachePath = str_replace("\\", "/", $jsCurrentFileCachePath);
        $jsFile =
            '<?php ' . PHP_EOL .
            '$JS_FILES = []; ' . PHP_EOL;

        foreach($data as $scriptName => $dataElement) {
            $jsFile .= '$JS_FILES["'.$scriptName.'"] = ["md5" => "'.$dataElement['md5'].'", "cachePath" => "'.$dataElement['cachePath'].'", "cacheUrl" => "'.$dataElement['cacheUrl'].'"];' . PHP_EOL;
        }
        file_put_contents($jsCurrentFileCachePath, $jsFile);

        $jsFileCachePath = $cachePhpConfigDir . CacheNCrunch::$JS_FILE_CACHE_DETAILS;
        $jsFileCachePathFile =
            '<?php ' . PHP_EOL .
            'require_once "' . $jsCurrentFileCachePath.'"; ' . PHP_EOL;

        file_put_contents($jsFileCachePath, $jsFileCachePathFile);
        CacheNCrunch::setLatestCNCDataPath($jsCurrentFileCachePath);
    }

}