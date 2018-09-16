<?php

namespace Chewett\CacheNCrunch;

/**
 * Class CNCHtmlHelper
 * @package Chewett\CacheNCrunch
 * @author Christopher Hewett <chewett@hotmail.co.uk>
 */
class CNCHtmlHelper
{

    /**
     * Helper function to create a script tag which will import the given JS file
     * @param string $import Javascript file path
     * @return string HTML representing a javascript import statement
     */
    public static function createJsImportStatement($import) {
        return "<script src='{$import}'></script>";
    }

    public static function createCssImportStatement($import) {
        return "<link href='{$import}' rel='stylesheet'>";
    }

}