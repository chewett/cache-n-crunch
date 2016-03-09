<?php

namespace Chewett\CacheNCrunch\CacheNCrunch;

/**
 * Class CacheNCrunch
 * @package Chewett\CacheNCrunch\CacheNCrunch
 * @author Christopher Hewett <chewett@hotmail.co.uk>
 */
class CacheNCrunch
{

    public static $jsFiles = [];

    /**
     * Registers the javascript file with the CacheNCrunch library
     * @param string $path Path to the Javascript file to be registered with the library
     * @param string $name Reference name of the file
     */
    public static function register($path, $name) {
        self::$jsFiles[$name] = $path;
    }

    /**
     * When called all registered libaries will be returned with script tags linking to them
     */
    public static function getScriptImports() {
        $stringImports = '';
        foreach(self::$jsFiles as $filename => $path) {
            $stringImports .= "<script src='{$path}'></script>";
        }
        return $stringImports;
    }

}