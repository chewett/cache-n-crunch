<?php
namespace Chewett\CacheNCrunch;
use Chewett\UglifyJS\JSUglify;


/**
 * Class CacheNCrunch
 * @package Chewett\CacheNCrunch\CacheNCrunch
 * @author Christopher Hewett <chewett@hotmail.co.uk>
 */
class CacheNCrunch
{

    public static $JS_CACHE = 'static/js/';
    public static $JS_LOADING_FILES = 'CacheNCrunch/js/';
    //Stores the details of what we have cached in php form (loaded during production use)
    public static $JS_FILE_CACHE_DETAILS  = 'jsCacheFile.php';
    //Stores the details of precisely what files we have in json form (loaded during debug)
    public static $INTERNAL_DETAILS_STORE = "details_store.json";

    private static $cacheDirectory = '';
    private static $cachePath = '';

    private static $uglifyOptions = [];

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

    public static function removeScript($scriptName) {
        unset(self::$jsFiles[$scriptName]);
    }

    public static function setDebug($debug) {
        self::$debugMode = $debug;
    }

    public static function getUglifyOptions() {
        return self::$uglifyOptions;
    }

    public static function setUglifyOptions($options) {
        self::$uglifyOptions = $options;
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

        $data = [];
        require self::$cacheDirectory . self::$JS_LOADING_FILES . self::$JS_FILE_CACHE_DETAILS;
        if(isset($JS_FILES)) {
            $data = $JS_FILES;
        }

        foreach(self::$jsFiles as $scriptName => $file) {
            $fileContents = file_get_contents($file->getPhysicalPath());
            $md5 = md5_file($file->getPhysicalPath());
            if(array_key_exists($scriptName, $data)) {
                if($data[$scriptName]['md5'] !== $md5) {
                    unlink($scriptName['cachePath']);
                    $data[$scriptName] = self::setUpFile($md5, $file);
                }
            }else{
                $data[$scriptName] = self::setUpFile($md5, $file);
            }
        }

        CNCSetup::storeDataToCacheFile($data);
    }

    private static function setUpFile($md5OfFile, CachingFile $file) {
        $ug = new JSUglify();

        $cachePath = self::$cacheDirectory . self::$JS_CACHE . $md5OfFile . ".js";
        $cachePath = str_replace("\\", "/", $cachePath);
        $cacheUrl = self::$cachePath . self::$JS_CACHE . $md5OfFile . ".js";
        $ug->uglify([$file->getPhysicalPath()], $cachePath, self::getUglifyOptions());
        return ['md5' => $md5OfFile, 'cachePath' => $cachePath, 'cacheUrl' => $cacheUrl];
    }

}