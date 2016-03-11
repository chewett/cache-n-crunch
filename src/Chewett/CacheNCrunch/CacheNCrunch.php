<?php
namespace Chewett\CacheNCrunch;
use Chewett\UglifyJS2\JSUglify2;


/**
 * Class CacheNCrunch
 * @package Chewett\CacheNCrunch\CacheNCrunch
 * @author Christopher Hewett <chewett@hotmail.co.uk>
 */
class CacheNCrunch
{

    public static $JS_CACHE = 'static/js/';
    public static $JS_LOADING_FILES = 'CacheNCrunch/js/';
    public static $JS_FILE_CACHE_DETAILS  = 'jsCacheFile.php';

    private static $cacheDirectory = '';
    private static $cachePath = '';

    /** @var CachingFile[] */
    private static $jsFiles = [];
    /** @var bool */
    private static $debugMode = false;

    public static function setUpCacheDirectory($cacheDirectory, $cachePath) {
        self::$cacheDirectory = $cacheDirectory;
        self::$cachePath = $cachePath;
        self::checkCacheDirectory();
    }

    public static function getCacheDirectory() {
        return self::$cacheDirectory;
    }

    /**
     * Registers the javascript file with the CacheNCrunch library
     * @param string $path Path to the Javascript file to be registered with the library
     * @param string $name Reference name of the file
     */
    public static function register($scriptName, $publicPath, $physicalPath) {
        self::$jsFiles[$scriptName] = new CachingFile($scriptName, $publicPath, $physicalPath);
    }

    public static function setDebug($debug) {
        self::$debugMode = $debug;
    }

    /**
     * When called all registered libaries will be returned with script tags linking to them
     */
    public static function getScriptImports() {
        $stringImports = '';
        $JS_FILES = [];
        if(!self::$debugMode) {
            require self::$cacheDirectory . self::$JS_LOADING_FILES . self::$JS_FILE_CACHE_DETAILS;
        }

        foreach(self::$jsFiles as $scriptName => $cachingFile) {
            $filePath = '';
            if(self::$debugMode) {
                $filePath = $cachingFile->getPublicPath();
            }else{
                if(array_key_exists($scriptName, $JS_FILES)) {
                    $filePath = $JS_FILES[$scriptName]['cacheUrl'];
                }else{
                    $filePath = $cachingFile->getPublicPath();
                }
            }
            $stringImports .= "<script src='{$filePath}'></script>";
        }
        return $stringImports;
    }

    private static function checkCacheDirectory() {
        if(is_readable(self::$cacheDirectory . self::$JS_LOADING_FILES . self::$JS_FILE_CACHE_DETAILS)) {
            return;
        }
        CNCSetup::setupBaseDirs();
    }

    public static function crunch() {
        if(!is_dir(self::$cacheDirectory . self::$JS_CACHE)) {
            mkdir(self::$cacheDirectory . self::$JS_CACHE, 0777, true);
        }
        $ug = new JSUglify2();

        $data = [];
        require self::$cacheDirectory . self::$JS_LOADING_FILES . self::$JS_FILE_CACHE_DETAILS;

        foreach(self::$jsFiles as $scriptName => $file) {
            $fileContents = file_get_contents($file->getPhysicalPath());
            $md5 = md5_file($file->getPhysicalPath());
            if(array_key_exists($scriptName, $data)) {
                if($data[$scriptName]['md5'] !== $md5) {
                    unlink($scriptName['cachePath']);
                    $cachePath = self::$cacheDirectory . self::$JS_CACHE . $md5 . ".js";
                    $cachePath = str_replace("\\", "/", $cachePath);
                    $cacheUrl = self::$cachePath . self::$JS_CACHE . $md5 . ".js";
                    $ug->uglify([$file->getPhysicalPath()], $cachePath);
                    $data[$scriptName] = ['md5' => $md5, 'cachePath' => $cachePath, 'cacheUrl' => $cacheUrl];
                }
            }else{
                $cachePath = self::$cacheDirectory . self::$JS_CACHE . $md5 . ".js";
                $cachePath = str_replace("\\", "/", $cachePath);
                $cacheUrl = self::$cachePath . self::$JS_CACHE . $md5 . ".js";
                $ug->uglify([$file->getPhysicalPath()], $cachePath);
                $data[$scriptName] = ['md5' => $md5, 'cachePath' => $cachePath, 'cacheUrl' => $cacheUrl];
            }
        }

        CNCSetup::storeDataToCacheFile($data);
    }

}