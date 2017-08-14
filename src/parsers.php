<?php
/**
 * User: Inkovskiy
 * Date: 11.08.17
 * Time: 15:21
 */

namespace Differ\parsers;

//require_once 'lib.php';

use \Symfony\Component\Yaml\Yaml;

function parseContent(string $format, string $content)
{
    switch ($format) {
        case 'json':
            $array = jsonParser($content);
            break;
        case 'yaml':
            $array = yamlParser($content);
            break;
        default:
            throw new \Exception("file format '{$format}' is unsupported");
    }

    return $array;
}


function jsonParser(string $content)
{
    return json_decode($content, true);
}

function yamlParser(string $content)
{
    return Yaml::parse($content, true);
}
