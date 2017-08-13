<?php
/**
 * User: Inkovskiy
 * Date: 13.08.17
 * Time: 0:55
 */

namespace Differ\reports;

use \Funct\Collection;

function jsonReport(array $result)
{
    return json_encode($result);
}

function plainReport(array $result)
{
    $reportIter = function ($array, $parents) use (&$reportIter) {
        return array_reduce(array_keys($array), function ($acc, $key) use ($array, $parents, $reportIter) {
            $firstChar = mb_substr($key, 0, 1);
            if (($firstChar != '+') && ($firstChar != '-')) {
                $parents[] = $key;
                if (is_array($array[$key])) {
                    $acc = array_merge($acc, $reportIter($array[$key], $parents));
                }
            } elseif ($firstChar == '+') {
                $parents[] = mb_substr($key, 2);
                $pathToRoot = implode('.', $parents);
                $minusKey = "-" . mb_substr($key, 1);
                if (array_key_exists($minusKey, $array)) {
                    $acc[] = "Property '{$pathToRoot}' was changed. From '{$array[$minusKey]}' to '{$array[$key]}'";
                } else {
                    if (is_array($array[$key])) {
                        $acc[] = "Property '{$pathToRoot}' was added with value: 'complex value'";
                    } else {
                        $acc[] = "Property '{$pathToRoot}' was added with value: '{$array[$key]}'";
                    }
                }
            } elseif ($firstChar == '-') {
                $parents[] = mb_substr($key, 2);
                $pathToRoot = implode('.', $parents);
                if (!array_key_exists("+" . mb_substr($key, 1), $array)) {
                    $acc[] = "Property '{$pathToRoot}' was removed";
                }
            }
            return $acc;
        }, []);
    };

//    return implode(PHP_EOL, Collection\compact($reportIter($result, [])));
    return implode(PHP_EOL, $reportIter($result, []));
}
