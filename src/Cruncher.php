<?php

namespace Chewett\CacheNCrunch;
use Chewett\UglifyCSS\CSSUglify;
use Chewett\UglifyJS\JSUglify;


/**
 * Class Cruncher
 * @package Chewett\CacheNCrunch
 * @author Christopher Hewett <chewett@hotmail.co.uk>
 */
class Cruncher {

    private static function createDirs() {
        $dirsToCreate = [
            CacheNCrunch::getCacheDirectory() . CacheNCrunch::$JS_CACHE_DIR_OUTPUT,
            CacheNCrunch::getCacheDirectory() . CacheNCrunch::$CSS_CACHE_DIR_OUTPUT
        ];

        foreach($dirsToCreate as $dirToCreate) {
            if(!is_dir($dirToCreate)) {
                mkdir($dirToCreate, 0777, true);
            }
        }
    }


    /**
     * @param $cachedDatToCheck
     * @param CachingFile[] $filesToImport
     * @param string $md5HashOfScriptNames
     * @return bool
     */
    private static function doFilesNeedCrunching($cachedDatToCheck, $filesToImport, $md5HashOfScriptNames) {
        $fileSetNeedsCrunching = false;

        if(isset($cachedDatToCheck[$md5HashOfScriptNames])) {
            //If we already have this hash, lets check each file has the right MD5
            $allMd5sTheSame = true;
            foreach($filesToImport as $fileToImport) {
                $allMd5sTheSame = $allMd5sTheSame &&
                    (md5_file($fileToImport->getPhysicalPath()) ==
                        $cachedDatToCheck[$md5HashOfScriptNames]['constituentFiles'][$fileToImport->getScriptName()]['originalMd5']);
            }

            if(!$allMd5sTheSame) {
                $fileSetNeedsCrunching = true;
                unlink($cachedDatToCheck[$md5HashOfScriptNames]['cachePath']);
            }
        }else{
            $fileSetNeedsCrunching = true;
        }
        return $fileSetNeedsCrunching;
    }

    /**
     * @param $fileImportOrder
     * @param CachingFile[] $filesToImport
     */
    private static function getFullDetailsOfFilesToImport($fileImportOrder, $filesToImport) {
        $constituentFilesArr = [];
        //Force the order of crunching files
        foreach($fileImportOrder as $scriptName) {
            $fileToImport = $filesToImport[$scriptName];

            //TODO: Optimization: we are md5'ing twice, reduce duplication and calls
            $constituentFilesArr[$fileToImport->getScriptName()] = [
                'originalMd5' => md5_file($fileToImport->getPhysicalPath()),
                'physicalPath' => $fileToImport->getPhysicalPath()
            ];
        }

        return $constituentFilesArr;

    }

    /**
     * @param $fileImportOrder
     * @param CachingFile[] $filesToImport
     */
    private static function getPhysicalPathsOfImports($fileImportOrder, $filesToImport) {
        $flatConstituentPhysicalPaths = [];
        //Force the order of crunching files
        foreach($fileImportOrder as $scriptName) {
            $fileToImport = $filesToImport[$scriptName];
            $flatConstituentPhysicalPaths[] = $fileToImport->getPhysicalPath();
        }

        return $flatConstituentPhysicalPaths;
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
        self::createDirs();

        $jsData = []; $cssData = [];
        require CacheNCrunch::getCacheDirectory() . CacheNCrunch::$CACHE_FILE_DIR . CacheNCrunch::$FILE_CACHE_DETAILS;
        if(isset($JS_FILES)) {
            $jsData = $JS_FILES;
        }
        if(isset($CSS_FILES)) {
            $cssData = $CSS_FILES;
        }

        $md5HashOfJsScriptNames = CacheNCrunch::getHashOfCurrentJsImports();
        $jsFilesToImport = CacheNCrunch::getJsFilesToImport();
        $jsFileSetNeedsCrunching = self::doFilesNeedCrunching($jsData, $jsFilesToImport, $md5HashOfJsScriptNames);

        $md5HashOfCssScriptNames = CacheNCrunch::getHashOfCurrentCssImports();
        $cssFilesToImport = CacheNCrunch::getCssFilesToImport();
        $cssFileSetNeedsCrunching = self::doFilesNeedCrunching($cssData, $cssFilesToImport, $md5HashOfCssScriptNames);

        if($jsFileSetNeedsCrunching) {
            $flatConstituentPhysicalPaths = self::getPhysicalPathsOfImports(CacheNCrunch::getJsFileImportOrder(), $jsFilesToImport);
            $constituentFilesArr = self::getFullDetailsOfFilesToImport(CacheNCrunch::getJsFileImportOrder(), $jsFilesToImport);

            $tempFile = tempnam(CacheNCrunch::getCacheDirectory() . CacheNCrunch::$TEMP_CRUNCH_DIR_PATH, "tmpPrefixTest");

            $ugJs = new JSUglify();
            $ugJs->uglify($flatConstituentPhysicalPaths, $tempFile, CacheNCrunch::getUglifyJsOptions(), CacheNCrunch::getUglifyJsHeaderFile());

            //Now get the MD5 and move the file
            $md5OfCrushedFile = md5_file($tempFile);
            $pathOfCrushedFile = str_replace("\\", "/",
                CacheNCrunch::getCacheDirectory() . CacheNCrunch::$JS_CACHE_DIR_OUTPUT . $md5OfCrushedFile . ".js"
            );
            rename($tempFile, $pathOfCrushedFile);

            $newCrushedFileData = [
                'cachePath' => $pathOfCrushedFile,
                'cacheUrl' => CacheNCrunch::getCacheWebRoot() . CacheNCrunch::$JS_CACHE_DIR_OUTPUT . $md5OfCrushedFile . ".js",
                'constituentFiles' => $constituentFilesArr
            ];

            $md5HashOfJsScriptNames = CacheNCrunch::getHashOfCurrentJsImports();
            $jsData[$md5HashOfJsScriptNames] = $newCrushedFileData;
        }

        if($cssFileSetNeedsCrunching) {
            $flatConstituentPhysicalPaths = self::getPhysicalPathsOfImports(CacheNCrunch::getCssFileImportOrder(), $cssFilesToImport);
            $constituentFilesArr = self::getFullDetailsOfFilesToImport(CacheNCrunch::getCssFileImportOrder(), $cssFilesToImport);

            $tempFile = tempnam(CacheNCrunch::getCacheDirectory() . CacheNCrunch::$TEMP_CRUNCH_DIR_PATH, "tmpPrefixTest");

            $ugCss = new CSSUglify();
            $ugCss->uglify($flatConstituentPhysicalPaths, $tempFile, CacheNCrunch::getUglifyCssOptions(), CacheNCrunch::getUglifyCssHeaderFile());

            //Now get the MD5 and move the file
            $md5OfCrushedFile = md5_file($tempFile);
            $pathOfCrushedFile = str_replace("\\", "/",
                CacheNCrunch::getCacheDirectory() . CacheNCrunch::$CSS_CACHE_DIR_OUTPUT . $md5OfCrushedFile . ".css"
            );
            rename($tempFile, $pathOfCrushedFile);

            $newCrushedFileData = [
                'cachePath' => $pathOfCrushedFile,
                'cacheUrl' => CacheNCrunch::getCacheWebRoot() . CacheNCrunch::$CSS_CACHE_DIR_OUTPUT . $md5OfCrushedFile . ".css",
                'constituentFiles' => $constituentFilesArr
            ];

            $md5HashOfCssScriptNames = CacheNCrunch::getHashOfCurrentCssImports();
            $cssData[$md5HashOfCssScriptNames] = $newCrushedFileData;
        }

        if($jsFileSetNeedsCrunching || $cssFileSetNeedsCrunching) {
            CNCSetup::storeDataToCacheFile($jsData, $cssData);
        }
    }

}