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
use \org\bovigo\vfs\vfsStream;

class LibTest extends TestCase
{
    private $rootDir;

    public function setUp()
    {
        $this->rootDir = vfsStream::setup('dir');
    }

    public function testGetContent()
    {
        $data = 'some data';
        $pathToFile = vfsStream::url('dir') . DIRECTORY_SEPARATOR . 'temp';
        $file = new \SplFileObject($pathToFile, 'ab');
        $file->fwrite($data);
        $this->assertEquals($data, \Differ\lib\getContent($pathToFile));
    }

    public function testGetContentException()
    {
        try {
            \Differ\lib\getContent('non-existent' . DIRECTORY_SEPARATOR . 'file'  . DIRECTORY_SEPARATOR . 'path');
            $this->fail('expected exception');
        } catch (\Exception $e) {
        }
    }

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
