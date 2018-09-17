<?php
/**
 * User: Christopher Hewett
 * Date: 16/09/2018
 * Time: 11:09 AM
 */

namespace Chewett\CacheNCrunch;


class CNCSettings {

    /** @var string Header file to use for all uglify JS calls  */
    private $uglifyJsHeaderFile = null;
    private $uglifyJsOptions = [];
    /** @var string Header file to use for all uglify css calls */
    private $uglifyCssHeaderFile = null;
    private $uglifyCssOptions = [];

    private $cssCacheDirOutput = 'static/css/';
    private $jsCacheDirOutput = 'static/js/';

    private $cacheFileDir = 'CacheNCrunch/';
    private $tempCacheDir = 'crunchTmp/';
    //Stores the details of what we have cached in php form
    private $cacheFileFilename  = 'cacheFile.php';

    private $crunchAlwaysOnDevMode = true;

    private $cacheDirectory = '';
    private $cacheWebRoot = '';

    /** @var bool */
    private $debugMode = false;


    public function setUpBaseCacheDirectories($cacheDirectory, $cachePath) {
        $this->cacheDirectory = $cacheDirectory;
        $this->cacheWebRoot = $cachePath;
    }

    /**
     * @return string
     */
    public function getCacheFileDir() {
        return $this->cacheFileDir;
    }

    /**
     * @param string $cacheFileDir
     */
    public function setCacheFileDir($cacheFileDir) {
        $this->cacheFileDir = $cacheFileDir;
    }

    /**
     * @return string
     */
    public function getCacheFileFilename() {
        return $this->cacheFileFilename;
    }

    /**
     * Gets the location of the cached file
     * @return string
     */
    public function getCacheFileLocation() {
        return $this->cacheDirectory . $this->cacheFileDir .$this->cacheFileFilename;
    }

    /**
     * @param string $cacheFileFilename
     */
    public function setCacheFileFilename($cacheFileFilename) {
        $this->cacheFileFilename = $cacheFileFilename;
    }

    /**
     * @return string
     */
    public function getCacheDirectory() {
        return $this->cacheDirectory;
    }

    /**
     * @param string $cacheDirectory
     */
    public function setCacheDirectory($cacheDirectory) {
        $this->cacheDirectory = $cacheDirectory;
    }

    /**
     * @return bool
     */
    public function isDebugMode() {
        return $this->debugMode;
    }

    /**
     * @param bool $debugMode
     * @return CNCSettings
     */
    public function setDebugMode($debugMode) {
        $this->debugMode = $debugMode;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCrunchAlwaysOnDevMode() {
        return $this->crunchAlwaysOnDevMode;
    }

    /**
     * @param bool $crunchAlwaysOnDevMode
     * @return CNCSettings
     */
    public function setCrunchAlwaysOnDevMode($crunchAlwaysOnDevMode) {
        $this->crunchAlwaysOnDevMode = $crunchAlwaysOnDevMode;
        return $this;
    }

    /**
     * @return string
     */
    public function getTempCacheDir() {
        return $this->tempCacheDir;
    }

    /**
     * @param string $tempCacheDir
     */
    public function setTempCacheDir($tempCacheDir) {
        $this->tempCacheDir = $tempCacheDir;
    }

    public function getUglifyJsOptions() {
        return $this->uglifyJsOptions;
    }

    public function setUglifyJsOptions($options) {
        $this->uglifyJsOptions = $options;
    }

    public function getUglifyCssOptions() {
        return $this->uglifyCssOptions;
    }

    public function setUglifyCssOptions($uglifyCssOptions) {
        $this->uglifyCssOptions = $uglifyCssOptions;
    }

    /**
     * @return string|null
     */
    public function getUglifyJsHeaderFile() {
        return $this->uglifyJsHeaderFile;
    }

    public function getUglifyCssHeaderFile() {
        return $this->uglifyCssHeaderFile;
    }

    /**
     * Sets the file path to use for uglify js as a header file when compressing
     * @param string|null $uglifyJsHeaderFile
     */
    public function setUglifyJsHeaderFile($uglifyJsHeaderFile) {
        $this->uglifyJsHeaderFile = $uglifyJsHeaderFile;
    }

    public function setUglifyCssHeaderFile($uglifyCssHeaderFile) {
        $this->uglifyCssHeaderFile = $uglifyCssHeaderFile;
    }

    /**
     * @return string
     */
    public function getCssCacheDirOutput() {
        return $this->cssCacheDirOutput;
    }

    /**
     * @param string $cssCacheDirOutput
     */
    public function setCssCacheDirOutput($cssCacheDirOutput) {
        $this->cssCacheDirOutput = $cssCacheDirOutput;
    }

    /**
     * @return string
     */
    public function getJsCacheDirOutput() {
        return $this->jsCacheDirOutput;
    }

    /**
     * @param string $jsCacheDirOutput
     */
    public function setJsCacheDirOutput($jsCacheDirOutput) {
        $this->jsCacheDirOutput = $jsCacheDirOutput;
    }

    /**
     * @return string
     */
    public function getCacheWebRoot() {
        return $this->cacheWebRoot;
    }

    /**
     * @param string $cacheWebRoot
     */
    public function setCacheWebRoot($cacheWebRoot) {
        $this->cacheWebRoot = $cacheWebRoot;
    }




}