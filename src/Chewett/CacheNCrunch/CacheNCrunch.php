<?php
namespace Chewett\CacheNCrunch;

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

    /** @var CachingFile[] */
    private static $jsFiles = [];
    /** @var bool */
    private static $debugMode = false;

    public static function setUpCacheDirectory($cacheDirectory) {
        self::$cacheDirectory = $cacheDirectory;
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
        foreach(self::$jsFiles as $filename => $cachingFile) {
            if(!self::$debugMode) {
                $stringImports .= "<script src='{$cachingFile->getPublicPath()}'></script>";
            }

        }
        return $stringImports;
    }

    private static function checkCacheDirectory() {
        if(is_readable(self::$cacheDirectory . self::$JS_LOADING_FILES . self::$JS_FILE_CACHE_DETAILS)) {
            return true;
        }

    }

}