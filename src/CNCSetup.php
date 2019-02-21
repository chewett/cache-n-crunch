<?php
namespace Chewett\CacheNCrunch;


class CNCSetup {

    /**
     * @param CNCSettings $cncSetting
     */
    public static function setupBaseDirs($cncSetting) {
        if(is_readable($cncSetting->getCacheDirectory() . $cncSetting->getCacheFileDir() . $cncSetting->getCacheFileFilename())) {
            return;
        }

        $cachePhpConfigDir = self::setupDirectories($cncSetting->getCacheDirectory(), $cncSetting->getCacheFileDir());
        $cacheFilePath = $cachePhpConfigDir . $cncSetting->getCacheFileFilename();
        file_put_contents($cacheFilePath, self::getStartOfAutoloadFile());
    }

    private static function setupDirectories($cacheDirectory, $cacheFileDir) {
        if(!is_dir($cacheDirectory)) {
            mkdir($cacheDirectory, 0777, true);
        }

        $cachePhpConfigDir = $cacheDirectory . $cacheFileDir;
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
     * @param CNCSettings $cncSettings
     * @param array $jsData
     * @param array $cssData
     */
    public static function storeDataToCacheFile($cncSettings, $jsData, $cssData) {
        $cachePhpConfigDir = self::setupDirectories($cncSettings->getCacheDirectory(), $cncSettings->getCacheFileDir());
        $currentFileCachePath = $cachePhpConfigDir . $cncSettings->getCacheFileFilename();
        $currentFileCachePath = str_replace("\\", "/", $currentFileCachePath);
        $cacheFile = self::getStartOfAutoloadFile();

        foreach($jsData as $scriptName => $dataElement) {
            $constituentFilesArr = [];
            foreach($dataElement['constituentFiles'] as $fileKey => $fileDetails) {
                $fixedFilePath = str_replace("\\", "/", $fileDetails['physicalPath']);
                $constituentFilesArr[] = '"'.$fileKey .'" => ["originalMd5" => "'. $fileDetails['originalMd5'] .'", "physicalPath" => "'.$fixedFilePath.'"]';
            }

            $cacheFile .= '$JS_FILES["'.$scriptName.'"] = [';
            $cacheFile .= '"cachePath" => "'.str_replace("\\", "/", $dataElement['cachePath']).'",';
            $cacheFile .= '"cacheUrl" => "'.$dataElement['cacheUrl'].'",';
            $cacheFile .= '"headerFile" => "' . str_replace("\\", "/", $dataElement['headerFile']) . '",';
            $cacheFile .= '"headerMd5" => "' . $dataElement['headerMd5'] . '",';
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
            $cacheFile .= '"headerFile" => "' . $dataElement['headerFile'] . '",';
            $cacheFile .= '"headerMd5" => "' . $dataElement['headerMd5'] . '",';
            $cacheFile .= '"constituentFiles" => ['.implode(", ", $constituentFilesArr).']];' . PHP_EOL;
        }


        file_put_contents($currentFileCachePath, $cacheFile);
    }

}