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

    public static $JS_CACHE_DIR_PATH = 'static/js/';
    public static $JS_LOADING_FILES = 'CacheNCrunch/js/';
    public static $JS_TEMP_DIR_PATH = 'jsTmp/';
    //Stores the details of what we have cached in php form (loaded during production use)
    public static $JS_FILE_CACHE_DETAILS  = 'jsCacheFile.php';

    private static $cacheDirectory = '';
    private static $cacheWebRoot = '';

    /** @var string Header file to use for all uglify JS calls  */
    private static $uglifyHeaderFile = null;
    private static $uglifyOptions = [];

    /** @var CachingFile[] */
    private static $filesToImport = [];
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
     * Registers the javascript file with the CacheNCrunch library
     * @param string $path Path to the Javascript file to be registered with the library
     * @param string $name Reference name of the file
     */
    public static function register($scriptName, $publicPath, $physicalPath) {
        self::$filesToImport[$scriptName] = new CachingFile($scriptName, $publicPath, $physicalPath);
    }

    public static function removeScript($scriptName) {
        unset(self::$filesToImport[$scriptName]);
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
     * @return string|null
     */
    public static function getUglifyHeaderFile() {
        return self::$uglifyHeaderFile;
    }

    /**
     * Sets the file path to use for uglify js as a header file when compressing
     * @param string|null $uglifyHeaderFile
     */
    public static function setUglifyHeaderFile($uglifyHeaderFile) {
        self::$uglifyHeaderFile = $uglifyHeaderFile;
    }

    /**
     * When called all registered libraries will be returned with script tags linking to them
     */
    public static function getScriptImports() {
        $stringImports = '';
        $JS_FILES = [];
        if(!self::$debugMode) {
            //Import the crunched list of files we know about
            require self::$cacheDirectory . self::$JS_LOADING_FILES . self::$JS_FILE_CACHE_DETAILS;
        }

        $currentImportsHashString = self::getHashOfCurrentImports();

        //If we are not in debug mode and we have this file already crunched then link to the crunched file
        if(!self::$debugMode && isset($JS_FILES[$currentImportsHashString])) {
            return CNCHtmlHelper::createJsImportStatement($JS_FILES[$currentImportsHashString]['cacheUrl']);

        }else{
            //Otherwise create the X import statements needed to import the raw JS
            $stringImports = [];
            foreach(self::$filesToImport as $scriptName => $cachingFile) {
                $stringImports[] = CNCHtmlHelper::createJsImportStatement($cachingFile->getPublicPath());
            }
            return implode("", $stringImports);
        }
    }

    private static function checkCacheDirectory() {
        if(is_readable(self::$cacheDirectory . self::$JS_LOADING_FILES . self::$JS_FILE_CACHE_DETAILS)) {
            return;
        }
        CNCSetup::setupBaseDirs();
    }

    /**
     * This loops through the list of current imports and generates the specific hash representing
     * the cache object that will be created for this specific file set
     * @return string Hash string representing the cache object for the file set
     */
    private static function getHashOfCurrentImports() {
        //Get all the script names I want to import
        $scriptNames = [];
        foreach(self::$filesToImport as $fileToImport) {
            $scriptNames[] = $fileToImport->getScriptName();
        }

        //This means that whatever order the scripts were registered if they are the same set it will be the same
        sort($scriptNames);

        //Now we get the hash of the script names, this forms the unique hash used for this combination
        return md5(json_encode($scriptNames));
    }

    /**
     * Looks through all files that have been registered to be crushed and crush them if needed
     *
     * If its found that any of these files constitituent files have changed it will recreate the combined
     * crushed file by running uglify over all of the files. If the combined files have never been crushed
     * together then they will  be crushed.
     *
     * Once crushed the fact that these have been crushed is saved to a file so we know where it has been crushed
     */
    public static function crunch() {
        if(!is_dir(self::$cacheDirectory . self::$JS_CACHE_DIR_PATH)) {
            mkdir(self::$cacheDirectory . self::$JS_CACHE_DIR_PATH, 0777, true);
        }

        $data = [];
        require self::$cacheDirectory . self::$JS_LOADING_FILES . self::$JS_FILE_CACHE_DETAILS;
        if(isset($JS_FILES)) {
            $data = $JS_FILES;
        }

        $md5HashOfScriptNames = self::getHashOfCurrentImports();
        $fileSetNeedsCrunching = false;

        if(isset($data[$md5HashOfScriptNames])) {
            //If we already have this hash, lets check each file has the right MD5
            $allMd5sTheSame = true;
            foreach(self::$filesToImport as $fileToImport) {
                $allMd5sTheSame = $allMd5sTheSame &&
                    (md5_file($fileToImport->getPhysicalPath()) ==
                        $data[$md5HashOfScriptNames][$fileToImport->getScriptName()]['originalMd5']);
            }

            if(!$allMd5sTheSame) {
                $fileSetNeedsCrunching = true;
                unlink($data[$md5HashOfScriptNames]['cachePath']);
            }
        }else{
            $fileSetNeedsCrunching = true;
        }

        if($fileSetNeedsCrunching) {

            //Get all the details of the data we are crushing in the format we expect
            $constituentFilesArr = [];
            $flatConstituentPhysicalPaths = [];
            foreach(self::$filesToImport as $fileToImport) {
                //TODO: Optimization: we are md5'ing twice, reduce duplication and calls
                $constituentFilesArr[] = [
                    'originalMd5' => md5_file($fileToImport->getPhysicalPath()),
                    'physicalPath' => $fileToImport->getPhysicalPath()
                ];
                $flatConstituentPhysicalPaths[] = $fileToImport->getPhysicalPath();
            }


            $tempFile = tempnam(self::$cacheDirectory . self::$JS_TEMP_DIR_PATH, "tmpPrefixTest");

            $ug = new JSUglify();
            $ug->uglify($flatConstituentPhysicalPaths, $tempFile, self::getUglifyOptions(), self::getUglifyHeaderFile());

            //Now get the MD5 and move the file
            $md5OfCrushedFile = md5_file($tempFile);
            $pathOfCrushedFile = str_replace("\\", "/",
                self::$cacheDirectory . self::$JS_CACHE_DIR_PATH . $md5OfCrushedFile . ".js"
            );
            rename($tempFile, $pathOfCrushedFile);

            $newCrushedFileData = [
                'cachePath' => $pathOfCrushedFile,
                'cacheUrl' => self::$cacheWebRoot . self::$JS_CACHE_DIR_PATH . $md5OfCrushedFile . ".js",
                'constituentFiles' => $constituentFilesArr
            ];

            $data[$md5HashOfScriptNames] = $newCrushedFileData;

            CNCSetup::storeDataToCacheFile($data);
        }

    }

}