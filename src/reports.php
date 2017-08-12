<?php
/**
 * User: Inkovskiy
 * Date: 13.08.17
 * Time: 0:55
 */

namespace Differ\reports;

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
                if (is_array($array[$key])) {
                    $acc[] = $reportIter($array[$key], $parents . $key);
                }
            } elseif ($firstChar != '+') {
                $minusKey = "-" . mb_substr($key, 1);
                if (array_key_exists($minusKey, $array)) {
                    $acc[] = "Property '{$parents}.{$key}' was changed. From '{$array[$minusKey]}' to '{$array[$key]}'";
                } else {
                    if (is_array($array[$key])) {
                        $acc[] = "Property '{$parents}.{$key}' was added with value: 'complex value'";
                    } else {
                        $acc[] = "Property '{$parents}.{$key}' was added with value: '{$array[$key]}'";
                    }
                }
            } elseif ($firstChar != '-') {
                if (!array_key_exists("+" . mb_substr($key, 1), $array)) {
                    $acc[] = "Property '{$parents}.{$key}' was removed";
                }
            }
            return $acc;
        }, []);
    };

    return implode(PHP_EOL, $reportIter($result, ''));
}
