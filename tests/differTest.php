<?php
/**
 * User: Inkovskiy
 * Date: 09.08.17
 * Time: 1:18
 */

namespace Differ\Tests;

//require_once 'src/differ.php';

use \PHPUnit\Framework\TestCase;

class DifferTest extends TestCase
{
    private $testData = ["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5];

    public function testGetContentFromJsonFileToArray()
    {
        $pathToFile = 'fixtures' . DIRECTORY_SEPARATOR . 'data.json';
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
    public function testArraysDiff($trueResult, $array1, $array2)
    {
        $this->assertEquals($trueResult, \Differ\differ\arraysDiff($array1, $array2));
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
                    '+ second' => 2,
                    '- second' => '2'
                ],
                [
                    'first' => '1',
                    'second' => '2'
                ],
                [
                    'first' => '1',
                    'second' => 2
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
