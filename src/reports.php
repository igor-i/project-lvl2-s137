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
    $iter = function (array $branch, $level) use (&$iter) {

        $printIndent = function ($level) {
            $string = '';
            for ($i = (int)$level * 4 + 2; $i > 0; $i--) {
                $string .= ' ';
            }
            return $string;
        };

        $printArray = function (array $array, $level) use ($printIndent) {
            $string = '{' . PHP_EOL;
            foreach ($array as $key => $value) {
                $string .= $printIndent((int)$level + 1) . "  \"{$key}\": {$value}" . PHP_EOL;
            }
            $string .= $printIndent($level) . '  }' . PHP_EOL;
            return $string;
        };

        return array_reduce($branch, function ($acc, $node) use ($level, $iter, $printIndent, $printArray) {
            $acc .= $printIndent($level);
            switch ($node['type']) {
                case 'nested':
                    $acc .= "  \"{$node['node']}\": {" . PHP_EOL;
                    $acc .= $iter($node['children'], (int)$level + 1);
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
        }, '');
    };

    $output = $iter($ast, 0);

    return '{' . PHP_EOL . $output . PHP_EOL . '}';
}
