<?php
/**
 * User: Inkovskiy
 * Date: 13.08.17
 * Time: 0:55
 */

namespace Differ\reports;

use \Funct\Collection;

function outputReport(string $format, array $ast)
{
    switch ($format) {
        case 'json':
            return jsonReport($ast);
        case 'plain':
            return plainReport($ast);
        case 'pretty':
            return prettyReport($ast);
        default:
            throw new \Exception("report format '{$format}' is unsupported");
    }
}

function jsonReport(array $ast)
{
    $output = array_reduce($ast, function ($acc, $node) {
        switch ($node['type']) {
            case 'nested':
                $acc[$node['node']] = jsonReport($node['children']);
                break;
            case 'unchanged':
                $acc[$node['node']] = $node['to'];
                break;
            case 'added':
                $acc["+ {$node['node']}"] = $node['to'];
                break;
            case 'removed':
                $acc["- {$node['node']}"] = $node['from'];
                break;
            case 'changed':
                $acc["+ {$node['node']}"] = $node['to'];
                $acc["- {$node['node']}"] = $node['from'];
                break;
        }
        return $acc;
    }, []);

    return json_encode($output);
}

function plainReport(array $ast)
{
    $iter = function ($ast, $parents) use (&$iter) {
        return array_reduce($ast, function ($acc, $node) use ($iter, $parents) {
            switch ($node['type']) {
                case 'nested':
                    $acc = array_merge($acc, $iter($node['children'], "{$parents}.{$node['node']}"));
                    break;
                case 'added':
                    if (is_array($node['to'])) {
                        $acc[] = "Property '{$parents}.{$node['node']}' was added with value: 'complex value'";
                    } else {
                        $acc[] = "Property '{$parents}.{$node['node']}' was added with value: '{$node['to']}'";
                    }
                    break;
                case 'removed':
                    $acc[] = "Property '{$parents}.{$node['node']}' was removed";
                    break;
                case 'changed':
                    $acc[] = "Property '{$parents}.{$node['node']}' was changed. From '{$node['from']}' to '{$node['to']}'";
                    break;
            }
            return $acc;
        }, []);
    };

    return implode(PHP_EOL, $iter($ast, ''));

//    $iter = function ($array, $parents) use (&$reportIter) {
//        return array_reduce(array_keys($array), function ($acc, $key) use ($array, $parents, $reportIter) {
//            $firstChar = mb_substr($key, 0, 1);
//            if (($firstChar != '+') && ($firstChar != '-')) {
//                $parents[] = $key;
//                if (is_array($array[$key])) {
//                    $acc = array_merge($acc, $reportIter($array[$key], $parents));
//                }
//            } elseif ($firstChar == '+') {
//                $parents[] = mb_substr($key, 2);
//                $pathToRoot = implode('.', $parents);
//                $minusKey = "-" . mb_substr($key, 1);
//                if (array_key_exists($minusKey, $array)) {
//                    $acc[] = "Property '{$pathToRoot}' was changed. From '{$array[$minusKey]}' to '{$array[$key]}'";
//                } else {
//                    if (is_array($array[$key])) {
//                        $acc[] = "Property '{$pathToRoot}' was added with value: 'complex value'";
//                    } else {
//                        $acc[] = "Property '{$pathToRoot}' was added with value: '{$array[$key]}'";
//                    }
//                }
//            } elseif ($firstChar == '-') {
//                $parents[] = mb_substr($key, 2);
//                $pathToRoot = implode('.', $parents);
//                if (!array_key_exists("+" . mb_substr($key, 1), $array)) {
//                    $acc[] = "Property '{$pathToRoot}' was removed";
//                }
//            }
//            return $acc;
//        }, []);
//    };

//    return implode(PHP_EOL, Collection\compact($reportIter($result, [])));
}

function prettyReport(array $ast)
{
    $iter = function (array $branch, integer $level) use (&$iter) {

        $printIndent = function (integer $level) {
            $string = '';
            for ($i = $level * 4 + 2; $i > 0; $i--) {
                $string .= ' ';
            }
            return $string;
        };

        $printArray = function (array $array, integer $level) use ($printIndent) {
            $string = '{' . PHP_EOL;
            foreach ($array as $key => $value) {
                $string .= $printIndent($level + 1) . "  \"{$key}\": {$value}" . PHP_EOL;
            }
            $string .= $printIndent($level) . '  }' . PHP_EOL;
            return $string;
        };

        return array_reduce($branch, function ($acc, $node) use ($level, $iter, $printIndent, $printArray) {
            $acc .= $printIndent($level);
            switch ($node['type']) {
                case 'nested':
                    $acc .= "  \"{$node['node']}\": {" . PHP_EOL;
                    $acc .= $iter($node['children'], $level + 1);
                    $acc .= $printIndent($level) . '  }' . PHP_EOL;
                    break;
                case 'unchanged':
                    $acc .= "  \"{$node['node']}\": ";
                    if (is_array($node['to'])) {
                        $acc .= $printArray($node['to'], $level);
                    } else {
                        $acc .= $node['to'] . PHP_EOL;
                    }
                    break;
                case 'added':
                    $acc .= "+ \"{$node['node']}\": ";
                    if (is_array($node['to'])) {
                        $acc .= $printArray($node['to'], $level);
                    } else {
                        $acc .= $node['to'] . PHP_EOL;
                    }
                    break;
                case 'removed':
                    $acc .= "- \"{$node['node']}\": ";
                    if (is_array($node['from'])) {
                        $acc .= $printArray($node['from'], $level);
                    } else {
                        $acc .= $node['from'] . PHP_EOL;
                    }
                    break;
                case 'changed':
                    $acc .= "+ \"{$node['node']}\": ";
                    if (is_array($node['to'])) {
                        $acc .= $printArray($node['to'], $level);
                    } else {
                        $acc .= $node['to'] . PHP_EOL;
                    }
                    $acc .= "- \"{$node['node']}\": ";
                    if (is_array($node['from'])) {
                        $acc .= $printArray($node['from'], $level);
                    } else {
                        $acc .= $node['from'] . PHP_EOL;
                    }
                    break;
            }
            $acc .= $printIndent($level) . '  ';
            return $acc;
        }, []);
    };

    $output = $iter($ast, 0);

    return '{' . PHP_EOL . $output . PHP_EOL . '}';
}
