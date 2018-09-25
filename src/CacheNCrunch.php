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
    /** @var CNCSettings */
    private $cncSettings = null;

    /** @var CachingFile[] */
    private $jsFilesToImport = [];
    /** @var CachingFile[] */
    private $cssFilesToImport = [];
    /** @var array */
    private $jsFileImportOrder = [];
    /** @var array */
    private $cssFileImportOrder = [];

    /** @var CNCLogger */
    private $logger;
    
    public function __construct() {
        $this->logger = new CNCLogger();
        $this->cncSettings = new CNCSettings();
    }

    public function setUpCacheDirectory($cacheDirectory, $cachePath) {
        $this->cncSettings->setUpBaseCacheDirectories($cacheDirectory, $cachePath);
        $this->checkCacheDirectory();
    }

    /**
     * @return CachingFile[]
     */
    public function getJsFilesToImport() {
        return $this->jsFilesToImport;
    }

    /**
     * @return CachingFile[]
     */
    public function getCssFilesToImport() {
        return $this->cssFilesToImport;
    }

    /**
     * @return array
     */
    public function getJsFileImportOrder() {
        return $this->jsFileImportOrder;
    }

    /**
     * @return array
     */
    public function getCssFileImportOrder() {
        return $this->cssFileImportOrder;
    }

    /**
     * Registers the javascript file with the CacheNCrunch library
     * @param string $path Path to the Javascript file to be registered with the library
     * @param string $name Reference name of the file
     */
    public function registerJsFile($scriptName, $publicPath, $physicalPath) {
        if(isset($this->jsFilesToImport[$scriptName])) {
            throw new CacheNCrunchException("Trying to register a JS script name that already exists");
        }
        $this->jsFileImportOrder[] = $scriptName;
        $this->jsFilesToImport[$scriptName] = new CachingFile($scriptName, $publicPath, $physicalPath);
    }

    public function registerCssFile($scriptName, $publicPath, $physicalPath) {
        if(isset($this->cssFilesToImport[$scriptName])) {
            throw new CacheNCrunchException("Trying to register a CSS script name that already exists");
        }
        $this->cssFileImportOrder[] = $scriptName;
        $this->cssFilesToImport[$scriptName] = new CachingFile($scriptName, $publicPath, $physicalPath);
    }

    public function setDebugMode($debug) {
        $this->cncSettings->setDebugMode($debug);
    }

    public function getUglifyJsOptions() {
        return $this->cncSettings->getUglifyJsOptions();
    }

    public function setUglifyJsOptions($options) {
        $this->cncSettings->setUglifyJsOptions($options);
    }

    public function getUglifyCssOptions() {
        return $this->cncSettings->getUglifyCssOptions();
    }

    public function setUglifyCssOptions($uglifyCssOptions) {
        $this->cncSettings->setUglifyCssOptions($uglifyCssOptions);
    }

    /**
     * @return string|null
     */
    public function getUglifyJsHeaderFile() {
        return $this->cncSettings->getUglifyJsHeaderFile();
    }

    public function getUglifyCssHeaderFile() {
        return $this->cncSettings->getUglifyCssHeaderFile();
    }

    /**
     * Sets the file path to use for uglify js as a header file when compressing
     * @param string|null $uglifyJsHeaderFile
     */
    public function setUglifyJsHeaderFile($uglifyJsHeaderFile) {
        $this->cncSettings->setUglifyJsHeaderFile($uglifyJsHeaderFile);
    }

    public function setUglifyCssHeaderFile($uglifyCssHeaderFile) {
        $this->cncSettings->setUglifyCssHeaderFile($uglifyCssHeaderFile);
    }

    /**
     * When called all registered libraries will be returned with script tags linking to them
     */
    public function getScriptImports() {
        if($this->cncSettings->isCrunchAlwaysOnDevMode() && $this->cncSettings->isDebugMode()) {
            $this->crunch();
        }

        $JS_FILES = [];
        $CSS_FILES = [];
        if(!$this->cncSettings->isDebugMode()) {
            //Import the crunched list of files we know about
            require $this->cncSettings->getCacheFileLocation();
        }

        $currentJsImportsHashString = Cruncher::getHashOfImports($this->jsFileImportOrder);

        //No files to crunch, so no string
        if($currentJsImportsHashString === null) {
            $jsFileImportString = "";

        //If we are not in debug mode and we have this file already crunched then link to the crunched file
        }else if(!$this->cncSettings->isDebugMode() && isset($JS_FILES[$currentJsImportsHashString])) {
            $jsFileImportString = CNCHtmlHelper::createJsImportStatement($JS_FILES[$currentJsImportsHashString]['cacheUrl']);
        }else{
            //Otherwise create the X import statements needed to import the raw JS
            $stringImports = [];
            //Force the order of the imports
            foreach($this->jsFileImportOrder as $scriptName) {
                $cachingFile = $this->jsFilesToImport[$scriptName];
                $stringImports[] = CNCHtmlHelper::createJsImportStatement($cachingFile->getPublicPath());
            }
            $jsFileImportString = implode("", $stringImports);
        }

        $currentCssImportsHashString = Cruncher::getHashOfImports($this->cssFileImportOrder);

        //No files to crunch, so no string
        if($currentCssImportsHashString === null) {
            $cssFileImportString = "";

        //If we are not in debug mode and we have this file already crunched then link to the crunched file
        } else if(!$this->cncSettings->isDebugMode() && isset($CSS_FILES[$currentCssImportsHashString])) {
            $cssFileImportString = CNCHtmlHelper::createCssImportStatement($CSS_FILES[$currentCssImportsHashString]['cacheUrl']);
        }else{
            //Otherwise create the X import statements needed to import the raw CSS
            $stringImports = [];
            //Force the order of the imports
            foreach($this->cssFileImportOrder as $scriptName) {
                $cachingFile = $this->cssFilesToImport[$scriptName];
                $stringImports[] = CNCHtmlHelper::createCssImportStatement($cachingFile->getPublicPath());
            }
            $cssFileImportString = implode("", $stringImports);
        }

        return $cssFileImportString . $jsFileImportString;
    }

    public function crunch() {
        Cruncher::crunch($this->cncSettings, $this->jsFileImportOrder, $this->jsFilesToImport, $this->cssFileImportOrder, $this->cssFilesToImport);
    }

    private function checkCacheDirectory() {
        CNCSetup::setupBaseDirs($this->cncSettings);
    }

}