<?php
/**
 * User: Inkovskiy
 * Date: 08.08.17
 * Time: 18:44
 */

namespace Differ\lib;

function getContent(string $pathToFile)
{
    if (file_exists($pathToFile) && is_readable($pathToFile)) {
        $content = file_get_contents($pathToFile);
    } else {
        throw new \Exception("file '{$pathToFile}' is undefined");
    }
    if ($content === false) {
        throw new \Exception("file '{$pathToFile}' is undefined");
    }

    return $content;
}

function defineFileFormat(string $pathToFile)
{
    $info = new \SplFileInfo($pathToFile);
    return $info->getExtension();
}
