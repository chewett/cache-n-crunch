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

    public static $CSS_CACHE_DIR_OUTPUT = 'static/css/';
    public static $JS_CACHE_DIR_OUTPUT = 'static/js/';

    public static $CACHE_FILE_DIR = 'CacheNCrunch/';
    public static $TEMP_CRUNCH_DIR_PATH = 'crunchTmp/';
    //Stores the details of what we have cached in php form
    public static $FILE_CACHE_DETAILS  = 'cacheFile.php';

    public static $CRUNCH_ALWAYS_IN_DEV_MODE_ON_SCRIPT_IMPORT = true;

    private static $cacheDirectory = '';
    private static $cacheWebRoot = '';

    /** @var string Header file to use for all uglify JS calls  */
    private static $uglifyJsHeaderFile = null;
    private static $uglifyJsOptions = [];
    /** @var string Header file to use for all uglify css calls */
    private static $uglifyCssHeaderFile = null;
    private static $uglifyCssOptions = [];

    /** @var CachingFile[] */
    private static $jsFilesToImport = [];
    /** @var CachingFile[] */
    private static $cssFilesToImport = [];
    /** @var array */
    private static $jsFileImportOrder = [];
    /** @var array */
    private static $cssFileImportOrder = [];

    /** @var bool */
    private static $debugMode = false;

    public static function setUpCacheDirectory($cacheDirectory, $cachePath) {
        self::$cacheDirectory = $cacheDirectory;
        self::$cacheWebRoot = $cachePath;
        self::checkCacheDirectory();
    }

    public static function getCacheDirectory() {
        return self::$cacheDirectory;
    }

    /**
     * @return CachingFile[]
     */
    public static function getJsFilesToImport() {
        return self::$jsFilesToImport;
    }

    /**
     * @return CachingFile[]
     */
    public static function getCssFilesToImport() {
        return self::$cssFilesToImport;
    }

    /**
     * @return array
     */
    public static function getJsFileImportOrder() {
        return self::$jsFileImportOrder;
    }

    /**
     * @return array
     */
    public static function getCssFileImportOrder() {
        return self::$cssFileImportOrder;
    }

    /**
     * @return string
     */
    public static function getCacheWebRoot() {
        return self::$cacheWebRoot;
    }




    /**
     * Registers the javascript file with the CacheNCrunch library
     * @param string $path Path to the Javascript file to be registered with the library
     * @param string $name Reference name of the file
     */
    public static function registerJsFile($scriptName, $publicPath, $physicalPath) {
        self::$jsFileImportOrder[] = $scriptName;
        self::$jsFilesToImport[$scriptName] = new CachingFile($scriptName, $publicPath, $physicalPath);
    }

    public static function registerCssFile($scriptName, $publicPath, $physicalPath) {
        self::$cssFileImportOrder[] = $scriptName;
        self::$cssFilesToImport[$scriptName] = new CachingFile($scriptName, $publicPath, $physicalPath);
    }

    public static function setDebug($debug) {
        self::$debugMode = $debug;
    }

    public static function getUglifyJsOptions() {
        return self::$uglifyJsOptions;
    }

    public static function setUglifyJsOptions($options) {
        self::$uglifyJsOptions = $options;
    }

    public static function getUglifyCssOptions() {
        return self::$uglifyCssOptions;
    }

    public static function setUglifyCssOptions($uglifyCssOptions) {
        self::$uglifyCssOptions = $uglifyCssOptions;
    }

    /**
     * @return string|null
     */
    public static function getUglifyJsHeaderFile() {
        return self::$uglifyJsHeaderFile;
    }

    public static function getUglifyCssHeaderFile() {
        return self::$uglifyCssHeaderFile;
    }

    /**
     * Sets the file path to use for uglify js as a header file when compressing
     * @param string|null $uglifyJsHeaderFile
     */
    public static function setUglifyJsHeaderFile($uglifyJsHeaderFile) {
        self::$uglifyJsHeaderFile = $uglifyJsHeaderFile;
    }

    public static function setUglifyCssHeaderFile($uglifyCssHeaderFile) {
        self::$uglifyCssHeaderFile = $uglifyCssHeaderFile;
    }

    /**
     * When called all registered libraries will be returned with script tags linking to them
     */
    public static function getScriptImports() {
        if(self::$CRUNCH_ALWAYS_IN_DEV_MODE_ON_SCRIPT_IMPORT && self::$debugMode) {
            Cruncher::crunch();
        }

        $stringImports = '';
        $JS_FILES = [];
        $CSS_FILES = [];
        if(!self::$debugMode) {
            //Import the crunched list of files we know about
            require self::$cacheDirectory . self::$CACHE_FILE_DIR . self::$FILE_CACHE_DETAILS;
        }

        $currentJsImportsHashString = self::getHashOfCurrentJsImports();

        //If we are not in debug mode and we have this file already crunched then link to the crunched file
        if(!self::$debugMode && isset($JS_FILES[$currentJsImportsHashString])) {
            $jsFileImportString = CNCHtmlHelper::createJsImportStatement($JS_FILES[$currentJsImportsHashString]['cacheUrl']);
        }else{
            //Otherwise create the X import statements needed to import the raw JS
            $stringImports = [];
            //Force the order of the imports
            foreach(self::$jsFileImportOrder as $scriptName) {
                $cachingFile = self::$jsFilesToImport[$scriptName];
                $stringImports[] = CNCHtmlHelper::createJsImportStatement($cachingFile->getPublicPath());
            }
            $jsFileImportString = implode("", $stringImports);
        }

        $currentCssImportsHashString = self::getHashOfCurrentCssImports();
        //If we are not in debug mode and we have this file already crunched then link to the crunched file
        if(!self::$debugMode && isset($JS_FILES[$currentJsImportsHashString])) {
            $cssFileImportString = CNCHtmlHelper::createCssImportStatement($CSS_FILES[$currentCssImportsHashString]['cacheUrl']);
        }else{
            //Otherwise create the X import statements needed to import the raw CSS
            $stringImports = [];
            //Force the order of the imports
            foreach(self::$cssFileImportOrder as $scriptName) {
                $cachingFile = self::$cssFilesToImport[$scriptName];
                $stringImports[] = CNCHtmlHelper::createCssImportStatement($cachingFile->getPublicPath());
            }
            $cssFileImportString = implode("", $stringImports);
        }

        return $cssFileImportString . $jsFileImportString;
    }

    private static function checkCacheDirectory() {
        if(is_readable(self::$cacheDirectory . self::$CACHE_FILE_DIR . self::$FILE_CACHE_DETAILS)) {
            return;
        }
        CNCSetup::setupBaseDirs();
    }

    /**
     * This loops through the list of current imports and generates the specific hash representing
     * the cache object that will be created for this specific file set
     * @return string Hash string representing the cache object for the file set
     */
    public static function getHashOfCurrentJsImports() {
        return md5(json_encode(self::$jsFileImportOrder));
    }

    public static function getHashOfCurrentCssImports() {
        return md5(json_encode(self::$cssFileImportOrder));
    }

}