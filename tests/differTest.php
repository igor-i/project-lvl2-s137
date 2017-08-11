<?php
/**
 * User: Inkovskiy
 * Date: 09.08.17
 * Time: 1:18
 */

namespace Differ\Tests;

//require_once 'src/differ.php';

use \PHPUnit\Framework\TestCase;
use function \Differ\differ\genDiff;

class DifferTest extends TestCase
{

    const TEST_FIXTURES_DIR = 'fixtures';

    private $pathToJsonFile;
    private $pathToYamlFile;
    private $pathToEqualFile;
    private $pathToPlusFile;
    private $pathToPlusMinusFile;

    public function setUp()
    {
        parent::setUp();
        $this->pathToEqualFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'equal-test.json';
        $this->pathToPlusFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'plus-test.json';
        $this->pathToPlusMinusFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'plus-minus-test.json';
        $this->pathToJsonFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'test.json';
        $this->pathToYamlFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'test.yaml';
    }

    /**
     * @dataProvider additionProvider
     * @param $expected
     * @param $pathToFile2
     */
    public function testJsonDiff($expected, $pathToFile2)
    {
        $this->assertEquals($expected, genDiff('json', $this->pathToJsonFile, $pathToFile2));
    }

    public function additionProvider()
    {
        return [
            [
                ["a" => 1, "b" => 2, "c" => 3, "d" => 4],
                [$this->pathToEqualFile]
            ],
            [
                ["+ a" => 1, "+ b" => 2, "+ c" => 3, "+ d" => 4],
                [$this->pathToPlusFile]
            ],
            [
                ["a" => 1, "+ b" => "2", "- b" => 2, "- c" => 3, "+ d" => "new value", "- d" => 4, "+ new" => "value"],
                [$this->pathToPlusMinusFile]
            ],
        ];
    }

    /**
     * @dataProvider addProvFileFormatException
     * @param $pathToFile2
     */
    public function testFileFormatException($pathToFile2)
    {
        try {
            genDiff('json', $this->pathToEqualFile, $pathToFile2);
            $this->fail('expected exception');
        } catch (\Exception $e) {
        }
    }

    public function addProvFileFormatException()
    {
        return [
            ['somefile.txt'],
            ['somefile']
        ];
    }

    public function testGetContentException()
    {
        try {
            genDiff('json', $this->pathToEqualFile, 'non-existent.json');
            $this->fail('expected exception');
        } catch (\Exception $e) {
        }
    }
}
