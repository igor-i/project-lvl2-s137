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
    return json_encode($ast);
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

        return array_map(function ($node) use ($level, $iter) {
            switch ($node['type']) {
                case 'nested':
                    return [
                        getIndent($level) . "  \"{$node['node']}\": {",
                        $iter($node['children'], $level + 1),
                        getIndent($level) . "  }"
                    ];
                case 'unchanged':
                    if (is_array($node['to'])) {
                        return getCollection($level, $node['node'], $node['to']);
                    } else {
                        return getKeyValue($level, $node['node'], $node['to']);
                    }
                    break;
                case 'added':
                    if (is_array($node['to'])) {
                        return getCollection($level, $node['node'], $node['to'], '+');
                    } else {
                        return getKeyValue($level, $node['node'], $node['to'], '+');
                    }
                    break;
                case 'removed':
                    if (is_array($node['from'])) {
                        return getCollection($level, $node['node'], $node['from'], '-');
                    } else {
                        return getKeyValue($level, $node['node'], $node['from'], '-');
                    }
                    break;
                case 'changed':
                    if (is_array($node['to'])) {
                        $result[] = getCollection($level, $node['node'], $node['to'], '+');
                    } else {
                        $result[] = getKeyValue($level, $node['node'], $node['to'], '+');
                    }
                    if (is_array($node['from'])) {
                        $result[] = getCollection($level, $node['node'], $node['from'], '-');
                    } else {
                        $result[] = getKeyValue($level, $node['node'], $node['from'], '-');
                    }
                    return $result;
                default:
                    return '';
            }
        }, $branch);
    };

    return implode(
        PHP_EOL,
        array_merge(
            ['{'],
            Collection\flattenAll($iter($ast, 0)),
            ['}']
        )
    );
}

function getIndent(int $level)
{
    return str_repeat(' ', $level * 4 + 2);
}

function getBool($variable)
{
    if (is_bool($variable)) {
        switch ($variable) {
            case true:
                return 'true';
            case false:
                return 'false';
        }
    }
    return "\"{$variable}\"";
}

function getCollection(int $level, string $key, array $value, string $prefix = ' ')
{
    $map = array_map(function ($key) use ($value, $level) {
        return getKeyValue($level + 1, $key, $value[$key]);
    }, array_keys($value));

    return [
        getIndent($level) . "{$prefix} \"{$key}\": {",
        $map,
        getIndent($level) . "  }"
    ];
}

function getKeyValue(int $level, string $key, $value, string $prefix = ' ')
{
    return implode(
        [
            getIndent($level),
            $prefix,
            " \"{$key}\": ",
            getBool($value)
        ]
    );
}
