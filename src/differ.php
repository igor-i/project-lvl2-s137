<?php
/**
 * User: Inkovskiy
 * Date: 07.08.17
 * Time: 22:28
 */

namespace Differ\differ;

use \Funct\Collection;

use function \Differ\lib\defineFileFormat;
use function \Differ\lib\getContent;
use function \Differ\parsers\parseContent;
use function \Differ\reports\outputReport;

const FILE_FORMATS = ['json', 'yaml'];
const REPORT_FORMATS = ['plain', 'pretty', 'json'];

function genDiff(string $outputFormat, string $pathToFile1, string $pathToFile2)
{
    try {
        $inputFileFormat1 = defineFileFormat($pathToFile1);
        $inputFileFormat2 = defineFileFormat($pathToFile2);

        if (!array_key_exists($inputFileFormat1, array_flip(FILE_FORMATS))) {
            throw new \Exception("file format '{$inputFileFormat1}' is unsupported");
        }
        if (!array_key_exists($inputFileFormat2, array_flip(FILE_FORMATS))) {
            throw new \Exception("file format '{$inputFileFormat2}' is unsupported");
        }

        $content1 = parseContent($inputFileFormat1, getContent($pathToFile1));
        $content2 = parseContent($inputFileFormat2, getContent($pathToFile2));

        if (!is_array($content1)) {
            throw new \Exception("file '{$pathToFile1}' does not contain {$inputFileFormat1} data");
        }
        if (!is_array($content2)) {
            throw new \Exception("file '{$pathToFile2}' does not contain {$inputFileFormat2} data");
        }

        $ast = arraysDiff($content1, $content2);

        return outputReport($outputFormat, $ast);
    } catch (\Exception $e) {
        return $e->getMessage() . PHP_EOL;
    }
}

function arraysDiff(array $array1, array $array2)
{
    $union = Collection\union(array_keys($array1), array_keys($array2));

    return array_reduce($union, function ($acc, $key) use ($array1, $array2) {
        if (array_key_exists($key, $array1) && array_key_exists($key, $array2)) {
            if (is_array($array1[$key]) && is_array($array2[$key])) {
                $acc[] = [
                    'type' => 'nested',
                    'node' => $key,
                    'children' => arraysDiff($array1[$key], $array2[$key])
                ];
            } else {
                if ($array1[$key] === $array2[$key]) {
                    $acc[] = [
                        'type' => 'unchanged',
                        'node' => $key,
                        'from' => $array2[$key],
                        'to' => $array2[$key]
                    ];
                } else {
                    $acc[] = [
                        'type' => 'changed',
                        'node' => $key,
                        'from' => $array1[$key],
                        'to' => $array2[$key]
                    ];
                }
            }
        } elseif (array_key_exists($key, $array1)) {
            $acc[] = [
                'type' => 'removed',
                'node' => $key,
                'from' => $array1[$key],
                'to' => ''
            ];
        } else {
            $acc[] = [
                'type' => 'added',
                'node' => $key,
                'from' => '',
                'to' => $array2[$key]
            ];
        }
        return $acc;
    }, []);
}
