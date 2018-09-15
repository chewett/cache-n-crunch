<?php
/**
 * Created by PhpStorm.
 * User: chewe
 * Date: 02/04/2018
 * Time: 12:19 AM
 */

namespace Chewett\CacheNCrunch;


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
        return "<style src='{$import}' />";
    }

}