<?php
namespace Chewett\CacheNCrunch\CacheNCrunch;


/**
 * Class CachingFile
 * @package Chewett\CacheNCrunch\CacheNCrunch
 * @author Christopher Hewett <chewett@hotmail.co.uk>
 */
class CachingFile {

    /** @var string */
    private $scriptName;
    /** @var string */
    private $publicPath;
    /** @var string */
    private $physicalPath;

    public function __construct($scriptName, $publicPath, $physicalPath) {
        $this->scriptName = $scriptName;
        $this->publicPath = $publicPath;
        $this->physicalPath = $physicalPath;
    }

    /**
     * @return string
     */
    public function getScriptName() {
        return $this->scriptName;
    }

    /**
     * @return string
     */
    public function getPublicPath() {
        return $this->publicPath;
    }

    /**
     * @return string
     */
    public function getPhysicalPath() {
        return $this->physicalPath;
    }



}