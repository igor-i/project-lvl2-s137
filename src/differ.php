<?php
/**
 * User: Inkovskiy
 * Date: 07.08.17
 * Time: 22:28
 */

namespace Differ\differ;

//require_once 'lib.php';

use \Funct\Collection;
use function \Differ\lib\getContent;
use function \Differ\lib\defineFileFormat;

//const FILE_FORMATS = ['json', 'yaml', 'ini'];
const FILE_FORMATS = ['json'];
//const REPORT_FORMATS = ['plain', 'pretty', 'json'];
const REPORT_FORMATS = ['json'];

function genDiff(string $format, string $pathToFile1, string $pathToFile2)
{
    try {
        //определяем формат по расширению файла
        $fileFormat1 = defineFileFormat($pathToFile1);
        $fileFormat2 = defineFileFormat($pathToFile2);

        if (!array_key_exists($fileFormat1, array_flip(FILE_FORMATS))) {
            throw new \Exception("file format '{$fileFormat1}' is unsupported");
        }
        if (!array_key_exists($fileFormat2, array_flip(FILE_FORMATS))) {
            throw new \Exception("file format '{$fileFormat2}' is unsupported");
        }

        //достаём контент в array
        $content1 = getContentFromFileToArray($fileFormat1, $pathToFile1);
        $content2 = getContentFromFileToArray($fileFormat2, $pathToFile2);

        if (!is_array($content1)) {
            throw new \Exception("failed to convert data from a file '{$pathToFile1}' into an array");
        }
        if (!is_array($content2)) {
            throw new \Exception("failed to convert data from a file '{$pathToFile2}' into an array");
        }

        //сравниваем
        $result = arraysDiff($content1, $content2);

        //возвращаем результат в заданном формате
        switch ($format) {
//            case 'plain':
//                break;
//            case 'pretty':
//                break;
            case 'json':
                return json_encode($result);
            default:
                throw new \Exception("report format '{$format}' is unsupported");
        }
    } catch (\Exception $e) {
        return $e->getMessage() . PHP_EOL;
    }
}

function getContentFromFileToArray(string $fileFormat, string $pathToFile)
{
    switch ($fileFormat) {
        case 'json':
            $content = json_decode(getContent($pathToFile), true);
            break;
//        case 'yaml':
//            break;
//        case 'ini':
//            break;
        default:
            throw new \Exception("file format '{$fileFormat}' is unsupported");
    }

    if (!is_array($content)) {
        throw new \Exception("file '{$pathToFile}' does not contain {$fileFormat} data");
    }

    return $content;
}

function arraysDiff(array $array1, array $array2)
{
//    $arraysMerge = array_merge($array1, $array1);
    $union = Collection\union(array_keys($array1), array_keys($array2));
    return array_reduce($union, function ($acc, $key) use ($array1, $array2) {
        if (array_key_exists($key, $array1) && array_key_exists($key, $array2)) {
            if ($array1[$key] === $array2[$key]) {
                $acc[$key] = $array1[$key];
            } else {
                $acc["+ {$key}"] = $array2[$key];
                $acc["- {$key}"] = $array1[$key];
            }
        } elseif (array_key_exists($key, $array1)) {
            $acc["- {$key}"] = $array1[$key];
        } else {
            $acc["+ {$key}"] = $array2[$key];
        }
        return $acc;
    }, []);
}
