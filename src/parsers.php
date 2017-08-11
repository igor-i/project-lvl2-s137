<?php
/**
 * User: Inkovskiy
 * Date: 11.08.17
 * Time: 15:21
 */

namespace Differ\parsers;

//require_once 'lib.php';

use \Symfony\Component\Yaml\Yaml;

use function \Differ\lib\getContent;

function jsonParser(string $pathToFile)
{
    return json_decode(getContent($pathToFile), true);
}

function yamlParser(string $pathToFile)
{
    return Yaml::parse(getContent($pathToFile), true);
}

function iniParser(string $pathToFile)
{
    return parse_ini_string(getContent($pathToFile), true);
}
