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
    $array = parse_ini_string(getContent($pathToFile), true, INI_SCANNER_RAW);

    return array_reduce(array_keys($array), function ($acc, $section) use ($array) {
        $hierarchy = explode('.', $section);
        if (count($hierarchy) > 1) {
            //TODO вместо этого надо придумать как рекурсивно собирать многоуровневые массивы
            $acc[$hierarchy[0]][$hierarchy[1]] = $array[$section];
        } else {
            $acc[$section] = $array[$section];
        }
        return $acc;
    }, []);
}
