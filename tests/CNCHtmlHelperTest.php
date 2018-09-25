<?php

namespace Chewett\CacheNCrunch\Test;

use Chewett\CacheNCrunch\CNCHtmlHelper;


/**
 * Class CacheNCrunchTest
 * @package Chewett\CacheNCrunch
 * @author Christopher Hewett <chewett@hotmail.co.uk>
 */
class CNCHtmlHelperTest extends \PHPUnit_Framework_TestCase {

    public function jsImportStringProvider() {
        return [
            ["test.js"],
            ["1901239021381203981209381284319574295841095810.js"],
            [".js"],
            ["thing.js.js.js"]
        ];
    }

    public function cssImportStringProvider() {
        return [
            ["test.css"],
            ["1239i12301511456759123440850183498172382173.js"],
            [".css"],
            ["thing.css.css.css"]
        ];
    }

    /**
     * @dataProvider jsImportStringProvider
     */
    public function testJsImport($jsImport) {
        $html = CNCHtmlHelper::createJsImportStatement($jsImport);

        $this->assertEquals(1, substr_count($html, $jsImport));
        $this->assertEquals(1, substr_count($html, "script src="));
    }

    /**
     * @dataProvider cssImportStringProvider
     */
    public function testCssImport($cssImport) {
        $html = CNCHtmlHelper::createCssImportStatement($cssImport);

        $this->assertEquals(1, substr_count($html, $cssImport));
        $this->assertEquals(1, substr_count($html, "link href="));
    }


}