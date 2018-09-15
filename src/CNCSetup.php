<?php
namespace Chewett\CacheNCrunch;


class CNCSetup {

    public static function setupBaseDirs() {
        $cachePhpConfigDir = self::setupDirectories();
        $cacheFilePath = $cachePhpConfigDir . CacheNCrunch::$FILE_CACHE_DETAILS;
        file_put_contents($cacheFilePath, self::getStartOfAutoloadFile());
    }

    private static function setupDirectories() {
        if(!is_dir(CacheNCrunch::getCacheDirectory())) {
            mkdir(CacheNCrunch::getCacheDirectory(), 0777, true);
        }

        $cachePhpConfigDir = CacheNCrunch::getCacheDirectory() . CacheNCrunch::$CACHE_FILE_DIR;
        if(!is_dir($cachePhpConfigDir)) {
            mkdir($cachePhpConfigDir, 0777, true);
        }
        return $cachePhpConfigDir;
    }

    private static function getStartOfAutoloadFile() {
        return
            '<?php ' . PHP_EOL .
            '$JS_FILES = []; ' . PHP_EOL .
            '$CSS_FILES = []; ' . PHP_EOL
            ;
    }

    /**
     * Take the data from the current cache and crunch autoloader and format it into the php autoloader file
     * @param $jsData
     */
    public static function storeDataToCacheFile($jsData, $cssData) {
        $cachePhpConfigDir = self::setupDirectories();
        $currentFileCachePath = $cachePhpConfigDir . CacheNCrunch::$FILE_CACHE_DETAILS;
        $currentFileCachePath = str_replace("\\", "/", $currentFileCachePath);
        $cacheFile = self::getStartOfAutoloadFile();

        foreach($jsData as $scriptName => $dataElement) {
            $constituentFilesArr = [];
            foreach($dataElement['constituentFiles'] as $fileKey => $fileDetails) {
                $fixedFilePath = str_replace("\\", "/", $fileDetails['physicalPath']);
                $constituentFilesArr[] = '"'.$fileKey .'" => ["originalMd5" => "'. $fileDetails['originalMd5'] .'", "physicalPath" => "'.$fixedFilePath.'"]';
            }

            $cacheFile .= '$JS_FILES["'.$scriptName.'"] = [';
            $cacheFile .= '"cachePath" => "'.$dataElement['cachePath'].'",';
            $cacheFile .= '"cacheUrl" => "'.$dataElement['cacheUrl'].'",';
            $cacheFile .= '"constituentFiles" => ['.implode(", ", $constituentFilesArr).']];' . PHP_EOL;
        }

        foreach($cssData as $scriptName => $dataElement) {
            $constituentFilesArr = [];
            foreach($dataElement['constituentFiles'] as $fileKey => $fileDetails) {
                $fixedFilePath = str_replace("\\", "/", $fileDetails['physicalPath']);
                $constituentFilesArr[] = '"'.$fileKey .'" => ["originalMd5" => "'. $fileDetails['originalMd5'] .'", "physicalPath" => "'.$fixedFilePath.'"]';
            }

            $cacheFile .= '$CSS_FILES["'.$scriptName.'"] = [';
            $cacheFile .= '"cachePath" => "'.$dataElement['cachePath'].'",';
            $cacheFile .= '"cacheUrl" => "'.$dataElement['cacheUrl'].'",';
            $cacheFile .= '"constituentFiles" => ['.implode(", ", $constituentFilesArr).']];' . PHP_EOL;
        }


        file_put_contents($currentFileCachePath, $cacheFile);
    }

}