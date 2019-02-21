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

    /**
     * @param CNCSettings $cncSettings
     */
    private static function createDirs($cncSettings) {
        $dirsToCreate = [
            $cncSettings->getCacheDirectory() . $cncSettings->getJsCacheDirOutput(),
            $cncSettings->getCacheDirectory() . $cncSettings->getCssCacheDirOutput()
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
    private static function doFilesNeedCrunching($cachedDatToCheck, $filesToImport, $md5HashOfScriptNames, $headerFile) {
        $fileSetNeedsCrunching = false;
        if($md5HashOfScriptNames === null) {
            //If the hash is null then it doesnt need crunching

        }else if(isset($cachedDatToCheck[$md5HashOfScriptNames])) {
            //If we already have this hash, lets check each file has the right MD5
            $allMd5sTheSame = true;
            foreach($filesToImport as $fileToImport) {
                $allMd5sTheSame = $allMd5sTheSame &&
                    (md5_file($fileToImport->getPhysicalPath()) ==
                        $cachedDatToCheck[$md5HashOfScriptNames]['constituentFiles'][$fileToImport->getScriptName()]['originalMd5']);
            }

            if($headerFile) {
                if($cachedDatToCheck[$md5HashOfScriptNames]['headerMd5'] != md5_file($headerFile)) {
                    $allMd5sTheSame = false;
                }
            }else{
                if($cachedDatToCheck[$md5HashOfScriptNames]['headerMd5'] != '') {
                    $allMd5sTheSame = false;
                }
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
     * @param CNCSettings $cncSettings
     */
    public static function crunch($cncSettings, $jsFileImportOrder, $jsFilesToImport, $cssFileImportOrder, $cssFilesToImport) {
        self::createDirs($cncSettings);

        $jsData = []; $cssData = [];
        require $cncSettings->getCacheFileLocation();
        if(isset($JS_FILES)) {
            $jsData = $JS_FILES;
        }
        if(isset($CSS_FILES)) {
            $cssData = $CSS_FILES;
        }

        $md5HashOfJsScriptNames = self::getHashOfImports($jsFileImportOrder);
        $jsFileSetNeedsCrunching = self::doFilesNeedCrunching($jsData, $jsFilesToImport, $md5HashOfJsScriptNames, $cncSettings->getUglifyJsHeaderFile());

        $md5HashOfCssScriptNames = self::getHashOfImports($cssFileImportOrder);
        $cssFileSetNeedsCrunching = self::doFilesNeedCrunching($cssData, $cssFilesToImport, $md5HashOfCssScriptNames, $cncSettings->getUglifyCssHeaderFile());

        if($jsFileSetNeedsCrunching) {
            $flatConstituentPhysicalPaths = self::getPhysicalPathsOfImports($jsFileImportOrder, $jsFilesToImport);
            $constituentFilesArr = self::getFullDetailsOfFilesToImport($jsFileImportOrder, $jsFilesToImport);

            $tempFile = tempnam($cncSettings->getCacheDirectory() . $cncSettings->getTempCacheDir(), "tmpJsPrefix");

            $ugJs = new JSUglify();
            $ugJs->uglify($flatConstituentPhysicalPaths, $tempFile, $cncSettings->getUglifyJsOptions(), $cncSettings->getUglifyJsHeaderFile());

            //Now get the MD5 and move the file
            $md5OfCrushedFile = md5_file($tempFile);
            $pathOfCrushedFile = str_replace("\\", "/",
                $cncSettings->getCacheDirectory() . $cncSettings->getJsCacheDirOutput() . $md5OfCrushedFile . ".js"
            );
            rename($tempFile, $pathOfCrushedFile);

            $newCrushedFileData = [
                'cachePath' => $pathOfCrushedFile,
                'cacheUrl' => $cncSettings->getCacheWebRoot() . $cncSettings->getJsCacheDirOutput() . $md5OfCrushedFile . ".js",
                'constituentFiles' => $constituentFilesArr,
                'headerFile' => $cncSettings->getUglifyJsHeaderFile(),
                'headerMd5' => ($cncSettings->getUglifyJsHeaderFile() ? md5_file($cncSettings->getUglifyJsHeaderFile()) : "")
            ];

            $md5HashOfJsScriptNames = self::getHashOfImports($jsFileImportOrder);
            $jsData[$md5HashOfJsScriptNames] = $newCrushedFileData;
        }

        //FIXME: Even when there is no css file to crunch, an empty set, it still "crunches" files.
        if($cssFileSetNeedsCrunching) {
            $flatConstituentPhysicalPaths = self::getPhysicalPathsOfImports($cssFileImportOrder, $cssFilesToImport);
            $constituentFilesArr = self::getFullDetailsOfFilesToImport($cssFileImportOrder, $cssFilesToImport);

            $tempFile = tempnam($cncSettings->getCacheDirectory() . $cncSettings->getTempCacheDir(), "tmpPrefixTest");

            $ugCss = new CSSUglify();
            $ugCss->uglify($flatConstituentPhysicalPaths, $tempFile, $cncSettings->getUglifyCssOptions(), $cncSettings->getUglifyCssHeaderFile());

            //Now get the MD5 and move the file
            $md5OfCrushedFile = md5_file($tempFile);
            $pathOfCrushedFile = str_replace("\\", "/",
                $cncSettings->getCacheDirectory() . $cncSettings->getCssCacheDirOutput() . $md5OfCrushedFile . ".css"
            );
            rename($tempFile, $pathOfCrushedFile);

            $newCrushedFileData = [
                'cachePath' => $pathOfCrushedFile,
                'cacheUrl' => $cncSettings->getCacheWebRoot() . $cncSettings->getCssCacheDirOutput() . $md5OfCrushedFile . ".css",
                'constituentFiles' => $constituentFilesArr,
                'headerFile' => $cncSettings->getUglifyCssHeaderFile(),
                'headerMd5' => ($cncSettings->getUglifyCssHeaderFile() ? md5_file($cncSettings->getUglifyCssHeaderFile()) : "")
            ];

            $md5HashOfCssScriptNames = self::getHashOfImports($cssFileImportOrder);
            $cssData[$md5HashOfCssScriptNames] = $newCrushedFileData;
        }

        if($jsFileSetNeedsCrunching || $cssFileSetNeedsCrunching) {
            CNCSetup::storeDataToCacheFile($cncSettings, $jsData, $cssData);
        }
    }

    /**
     * This loops through the list of imports and generates the specific hash representing
     * the cache object that will be created for this specific file set
     * @return string|null Hash string representing the cache object for the file set, Null if no imports are given
     */
    public static function getHashOfImports($importList) {
        if(!$importList) {
            return null;
        }
        return md5(json_encode($importList));
    }

}