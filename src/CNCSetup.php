<?php
namespace Chewett\CacheNCrunch;


class CNCSetup {

    public static function setupBaseDirs() {
        $cachePhpConfigDir = self::setupDirectories();
        $jsFileCachePath = $cachePhpConfigDir . CacheNCrunch::$JS_FILE_CACHE_DETAILS;
        file_put_contents($jsFileCachePath, self::getStartOfAutoloadFile());
    }

    private static function setupDirectories() {
        if(!is_dir(CacheNCrunch::getCacheDirectory())) {
            mkdir(CacheNCrunch::getCacheDirectory(), 0777, true);
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

    /**
     * Take the data from the current cache and crunch autoloader and format it into the php autoloader file
     * @param $data
     */
    public static function storeDataToCacheFile($data) {
        $cachePhpConfigDir = self::setupDirectories();
        $jsCurrentFileCachePath = $cachePhpConfigDir . CacheNCrunch::$JS_FILE_CACHE_DETAILS;
        $jsCurrentFileCachePath = str_replace("\\", "/", $jsCurrentFileCachePath);
        $jsFile = self::getStartOfAutoloadFile();

        foreach($data as $scriptName => $dataElement) {
            $constituentFilesArr = [];
            foreach($dataElement['constituentFiles'] as $fileKey => $fileDetails) {
                $fixedFilePath = str_replace("\\", "/", $fileDetails['physicalPath']);
                $constituentFilesArr[$fileKey] = '["originalMd5" => "'. $fileDetails['originalMd5'] .'", "physicalPath" => "'.$fixedFilePath.'"]';
            }


            $jsFile .= '$JS_FILES["'.$scriptName.'"] = [';
            $jsFile .= '"cachePath" => "'.$dataElement['cachePath'].'",';
            $jsFile .= '"cacheUrl" => "'.$dataElement['cacheUrl'].'",';
            $jsFile .= '"constituentFiles" => ['.implode(", ", $constituentFilesArr).']];' . PHP_EOL;
        }
        file_put_contents($jsCurrentFileCachePath, $jsFile);
    }

}