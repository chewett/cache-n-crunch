<?php
namespace Chewett\CacheNCrunch;


class CNCSetup {

    public static function setupBaseDirs() {
        $cachePhpConfigDir = self::setupDirectories();
        $jsFileCachePath = $cachePhpConfigDir . CacheNCrunch::$JS_FILE_CACHE_DETAILS;
        $internalCacheFile = $cachePhpConfigDir . CacheNCrunch::$INTERNAL_DETAILS_STORE;
        file_put_contents($jsFileCachePath, self::getStartOfAutoloadFile());
        file_put_contents($internalCacheFile, self::getStartOfInternalFile());
    }

    private static function setupDirectories() {
        if(!is_dir(CacheNCrunch::getCacheDirectory())) {
            mkdir(CacheNCrunch::getCacheDirectory());
        }

        $cachePhpConfigDir = CacheNCrunch::getCacheDirectory() . CacheNCrunch::$JS_LOADING_FILES;
        if(!is_dir($cachePhpConfigDir)) {
            mkdir($cachePhpConfigDir, 0777, true);
        }
        return $cachePhpConfigDir;
    }

    private static function getStartOfAutoloadFile() {
        return
            '<?php ' . PHP_EOL .
            '$JS_FILES = []; ' . PHP_EOL;
    }

    private static function getStartOfInternalFile() {
        return json_encode([
                "crunched_files" => []
            ], JSON_PRETTY_PRINT);
    }

    public static function storeDataToCacheFile($data) {
        $cachePhpConfigDir = self::setupDirectories();
        $jsCurrentFileCachePath = $cachePhpConfigDir . CacheNCrunch::$JS_FILE_CACHE_DETAILS;
        $jsCurrentFileCachePath = str_replace("\\", "/", $jsCurrentFileCachePath);
        $jsFile = self::getStartOfAutoloadFile();

        foreach($data as $scriptName => $dataElement) {
            $jsFile .= '$JS_FILES["'.$scriptName.'"] = ["md5" => "'.$dataElement['md5'].'", "cachePath" => "'.$dataElement['cachePath'].'", "cacheUrl" => "'.$dataElement['cacheUrl'].'"];' . PHP_EOL;
        }
        file_put_contents($jsCurrentFileCachePath, $jsFile);
    }

}