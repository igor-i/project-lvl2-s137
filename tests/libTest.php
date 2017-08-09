<?php
/**
 * User: Inkovskiy
 * Date: 08.08.17
 * Time: 18:49
 */

namespace Differ\Tests;

//require_once 'src/lib.php';

use const Differ\differ\FILE_FORMATS;
use \PHPUnit\Framework\TestCase;

class LibTest extends TestCase
{
    /**
     * @dataProvider additionProvider1
     * @param $expected
     * @param $argument
     */
    public function testDefineFileFormat($expected, $argument)
    {
        $this->assertEquals($expected, \Differ\lib\defineFileFormat($argument));
    }

    public function additionProvider1()
    {
        return array_map(function ($item) {
            return [$item, 'somepath'  . DIRECTORY_SEPARATOR . 'somefile.' . $item];
        }, FILE_FORMATS);
    }

    /**
     * @dataProvider additionProvider2
     * @param $argument
     */
    public function testDefineFileFormatException($argument)
    {
        try {
            \Differ\lib\defineFileFormat($argument);
            $this->fail('expected exception');
        } catch (\Exception $e) {
        }
    }

    public function additionProvider2()
    {
        return [
            ['somefile.txt'],
            ['somefile']
        ];
    }
}
