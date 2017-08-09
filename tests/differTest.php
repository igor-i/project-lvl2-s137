<?php
/**
 * User: Inkovskiy
 * Date: 09.08.17
 * Time: 1:18
 */

namespace Differ\Tests;

//require_once 'src/differ.php';

use \PHPUnit\Framework\TestCase;
use \org\bovigo\vfs\vfsStream;

class DifferTest extends TestCase
{

    private $rootDir;
    private $testData = ["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5];

    public function setUp()
    {
        $this->rootDir = vfsStream::setup('dir');
    }

    public function testGetContentFromJsonFileToArray()
    {
        $data = json_encode($this->testData);
        $pathToFile = vfsStream::url('dir') . DIRECTORY_SEPARATOR . 'temp.json';
        $file = new \SplFileObject($pathToFile, 'ab');
        $file->fwrite($data);
        $this->assertEquals($this->testData, \Differ\differ\getContentFromFileToArray('json', $pathToFile));
    }

    /**
     * @dataProvider additionProvider1
     * @param $argument
     */
    public function testGetContentFromFileToArrayException($argument)
    {
        try {
            \Differ\differ\getContentFromFileToArray($argument, 'somePathToFile');
            $this->fail('expected exception');
        } catch (\Exception $e) {
        }
    }

    public function additionProvider1()
    {
        return [
            ['txt'],
            ['']
        ];
    }

    /**
     * @dataProvider additionProvider2
     * @param $trueResult
     * @param $array1
     * @param $array2
     */
    public function testArraysDif($trueResult, $array1, $array2)
    {
        $this->assertEquals($trueResult, \Differ\differ\arraysDif($array1, $array2));
    }

    public function additionProvider2()
    {
        return [
            [
                [
                    'first' => ''
                ],
                [
                    'first' => ''
                ],
                [
                    'first' => ''
                ],
            ],
            [
                [
                    'first' => '1',
                    '+ second' => '2',
                    '- second' => '3'
                ],
                [
                    'first' => '1',
                    'second' => '3'
                ],
                [
                    'first' => '1',
                    'second' => '2'
                ]
            ],
            [
                [
                    'first' => '1',
                    '+ second' => '2'
                ],
                [
                    'first' => '1'
                ],
                [
                    'first' => '1',
                    'second' => '2'
                ]
            ],
            [
                [
                    'first' => '1',
                    '- second' => '2'
                ],
                [
                    'first' => '1',
                    'second' => '2'
                ],
                [
                    'first' => '1'
                ]
            ]
        ];
    }
}
