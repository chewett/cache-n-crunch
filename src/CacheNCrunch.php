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

    public function setCrunchAlwaysOnDevMode($alwaysCrushInDevMode) {
        $this->cncSettings->setCrunchAlwaysOnDevMode($alwaysCrushInDevMode);
    }

    public function setDontServeCrunchedFiles($dontServeCrunchedFiles) {
        $this->cncSettings->setDontServeCrunchedFiles($dontServeCrunchedFiles);
    }

    public function setCrunchIfNotAlreadyCrunched($crunchIfNotAlreadyCrunched) {
        $this->cncSettings->setCrunchIfNotAlreadyCrunched($crunchIfNotAlreadyCrunched);
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

    public function clearJsFileImports() {
        $this->jsFilesToImport = [];
        $this->jsFileImportOrder = [];
    }

    public function clearCssFileImports() {
        $this->cssFilesToImport = [];
        $this->cssFileImportOrder = [];
    }

    public function registerJsAsArray($scriptDataArray) {
        if(count($scriptDataArray) != 3) {
            throw new \InvalidArgumentException("Incorrect number of elements in the array");
        }
        $this->registerJsFile($scriptDataArray[0], $scriptDataArray[1], $scriptDataArray[2]);
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

    public function registerCssAsArray($scriptDataArray) {
        if(count($scriptDataArray) != 3) {
            throw new \InvalidArgumentException("Incorrect number of elements in the array");
        }
        $this->registerCssFile($scriptDataArray[0], $scriptDataArray[1], $scriptDataArray[2]);
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
        }else if(!$this->cncSettings->isDontServeCrunchedFiles() && !$this->cncSettings->isDebugMode() && isset($JS_FILES[$currentJsImportsHashString])) {
            $jsFileImportString = CNCHtmlHelper::createJsImportStatement($JS_FILES[$currentJsImportsHashString]['cacheUrl']);
        }else{
            //Check to see if we want to crunch files if they havent already been crunched. If so crunch and then get the import
            if(!$this->cncSettings->isDontServeCrunchedFiles() && !$this->cncSettings->isDebugMode() && $this->cncSettings->isCrunchIfNotAlreadyCrunched()) {
                $this->crunch();
                require $this->cncSettings->getCacheFileLocation();
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
        }

        $currentCssImportsHashString = Cruncher::getHashOfImports($this->cssFileImportOrder);

        //No files to crunch, so no string
        if($currentCssImportsHashString === null) {
            $cssFileImportString = "";

        //If we are not in debug mode and we have this file already crunched then link to the crunched file
        } else if(!$this->cncSettings->isDontServeCrunchedFiles() && !$this->cncSettings->isDebugMode() && isset($CSS_FILES[$currentCssImportsHashString])) {
            $cssFileImportString = CNCHtmlHelper::createCssImportStatement($CSS_FILES[$currentCssImportsHashString]['cacheUrl']);
        }else{
            //Check to see if we want to crunch files if they havent already been crunched. If so crunch and then get the import
            if(!$this->cncSettings->isDontServeCrunchedFiles() && !$this->cncSettings->isDebugMode() && $this->cncSettings->isCrunchIfNotAlreadyCrunched()) {
                $this->crunch();
                require $this->cncSettings->getCacheFileLocation();
                $cssFileImportString = CNCHtmlHelper::createCssImportStatement($CSS_FILES[$currentCssImportsHashString]['cacheUrl']);
            }else {

                //Otherwise create the X import statements needed to import the raw CSS
                $stringImports = [];
                //Force the order of the imports
                foreach ($this->cssFileImportOrder as $scriptName) {
                    $cachingFile = $this->cssFilesToImport[$scriptName];
                    $stringImports[] = CNCHtmlHelper::createCssImportStatement($cachingFile->getPublicPath());
                }
                $cssFileImportString = implode("", $stringImports);
            }
        }

        return $cssFileImportString . $jsFileImportString;
    }

    public function crunch() {
        Cruncher::crunch($this->cncSettings, $this->jsFileImportOrder, $this->jsFilesToImport, $this->cssFileImportOrder, $this->cssFilesToImport);
    }

    private function checkCacheDirectory() {
        CNCSetup::setupBaseDirs($this->cncSettings);
    }

    /**
     * Loads up the cache and returns it in an array so that it can be used by the page
     * @return array
     */
    public function getCacheStoreData() {
        $JS_FILES = []; $CSS_FILES = [];
        require $this->cncSettings->getCacheFileLocation();

        return [
            'js' => $JS_FILES,
            'css' => $CSS_FILES
        ];
    }

    /**
     * Loop through every single crunched object and crunch them if needed.
     */
    public function crunchAnythingNeeded() {
        //Start by clearing all imports, lets make sure its clean.
        $this->clearJsFileImports();
        $this->clearCssFileImports();

        $allCurrentCacheData = $this->getCacheStoreData();
        foreach($allCurrentCacheData['js'] as $jsFileCached) {
            $this->cncSettings->setUglifyJsHeaderFile(($jsFileCached['headerFile'] ? $jsFileCached['headerFile'] : null));

            foreach($jsFileCached['constituentFiles'] as $jsFileScriptName => $jsFileDetails) {
                $this->registerJsFile($jsFileScriptName, "this_doesnt_matter", $jsFileDetails['physicalPath']);
            }

            $this->crunch();
            $this->clearJsFileImports();
        }

        foreach($allCurrentCacheData['css'] as $cssFileCached) {
            $this->cncSettings->setUglifyCssHeaderFile(($jsFileCached['headerFile'] ? $jsFileCached['headerFile'] : null));

            foreach($cssFileCached['constituentFiles'] as $cssFileScriptName => $cssFileDetails) {
                $this->registerCssFile($cssFileScriptName, "this_doesnt_matter", $cssFileDetails['physicalPath']);
            }

            $this->crunch();
            $this->clearCssFileImports();
        }
    }

    //TODO: Remove a single cached hash
    //TOOD: Remove all cached hashes

}