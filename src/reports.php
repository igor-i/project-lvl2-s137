<?php
/**
 * User: Inkovskiy
 * Date: 13.08.17
 * Time: 0:55
 */

namespace Differ\reports;

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
    $iter = function ($ast) use (&$iter) {
        return array_reduce($ast, function ($acc, $node) use ($iter) {
            switch ($node['type']) {
                case 'nested':
                    $acc[$node['node']] = $iter($node['children']);
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
    };

    return json_encode($iter($ast));
}

function plainReport(array $ast)
{
    $iter = function ($ast, $parents) use (&$iter) {
        return array_reduce($ast, function ($acc, $node) use ($iter, $parents) {
            $parents[] = $node['node'];
            $pathToNode = implode('.', $parents);
            switch ($node['type']) {
                case 'nested':
                    $acc = array_merge($acc, $iter($node['children'], $parents));
                    break;
                case 'added':
                    if (is_array($node['to'])) {
                        $acc[] = "Property '{$pathToNode}' was added with value: 'complex value'";
                    } else {
                        $acc[] = "Property '{$pathToNode}' was added with value: '{$node['to']}'";
                    }
                    break;
                case 'removed':
                    $acc[] = "Property '{$pathToNode}' was removed";
                    break;
                case 'changed':
                    $acc[] =
                        "Property '{$pathToNode}' was changed. From '{$node['from']}' to '{$node['to']}'";
                    break;
            }
            return $acc;
        }, []);
    };

    return implode(PHP_EOL, $iter($ast, []));
}

function prettyReport(array $ast)
{
    $iter = function (array $branch, int $level) use (&$iter) {

        $printIndent = function (int $level) {
            return str_repeat(' ', $level * 4 + 2);
        };

        $printBool = function ($variable) {
            if (is_bool($variable)) {
                switch ($variable) {
                    case true:
                        return 'true';
                    case false:
                        return 'false';
                }
            }
            return "\"{$variable}\"";
        };

        $printArray = function (array $array, int $level) use ($printIndent, $printBool) {
            $result = [];
            foreach ($array as $key => $value) {
                $result[] = "{$printIndent($level + 1)}  \"{$key}\": {$printBool($value)}";
            }
            return $result;
        };

        return array_reduce($branch, function ($acc, $node) use ($level, $iter, $printIndent, $printArray, $printBool) {
            switch ($node['type']) {
                case 'nested':
                    $acc[] = "{$printIndent($level)}  \"{$node['node']}\": {";
                    $acc = array_merge($acc, $iter($node['children'], (int)$level + 1));
                    $acc[] = "{$printIndent($level)}  }";
                    break;
                case 'unchanged':
                    if (is_array($node['to'])) {
                        $acc[] = "{$printIndent($level)}  \"{$node['node']}\": {";
                        $acc = array_merge($acc, $printArray($node['to'], $level));
                        $acc[] = "{$printIndent($level)}  }";
                    } else {
                        $acc[] = "{$printIndent($level)}  \"{$node['node']}\": {$printBool($node['to'])}";
                    }
                    break;
                case 'added':
                    if (is_array($node['to'])) {
                        $acc[] = "{$printIndent($level)}+ \"{$node['node']}\": {";
                        $acc = array_merge($acc, $printArray($node['to'], $level));
                        $acc[] = "{$printIndent($level)}  }";
                    } else {
                        $acc[] = "{$printIndent($level)}+ \"{$node['node']}\": {$printBool($node['to'])}";
                    }
                    break;
                case 'removed':
                    if (is_array($node['from'])) {
                        $acc[] = "{$printIndent($level)}- \"{$node['node']}\": {";
                        $acc = array_merge($acc, $printArray($node['from'], $level));
                        $acc[] = "{$printIndent($level)}  }";
                    } else {
                        $acc[] = "{$printIndent($level)}- \"{$node['node']}\": {$printBool($node['from'])}";
                    }
                    break;
                case 'changed':
                    if (is_array($node['to'])) {
                        $acc[] = "{$printIndent($level)}+ \"{$node['node']}\": {";
                        $acc = array_merge($acc, $printArray($node['to'], $level));
                        $acc[] = "{$printIndent($level)}  }";
                    } else {
                        $acc[] = "{$printIndent($level)}+ \"{$node['node']}\": {$printBool($node['to'])}";
                    }
                    if (is_array($node['from'])) {
                        $acc[] = "{$printIndent($level)}- \"{$node['node']}\": {";
                        $acc = array_merge($acc, $printArray($node['from'], $level));
                        $acc[] = "{$printIndent($level)}  }";
                    } else {
                        $acc[] = "{$printIndent($level)}- \"{$node['node']}\": {$printBool($node['from'])}";
                    }
                    break;
            }
            return $acc;
        }, []);
    };

    return implode(PHP_EOL, array_merge(['{'], $iter($ast, 0), ['}']));
}
