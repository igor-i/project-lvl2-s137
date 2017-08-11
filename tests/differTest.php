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

    const TEST_FIXTURES_DIR = 'tests'  . DIRECTORY_SEPARATOR . 'fixtures';

    private $pathToJsonFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'test.json';
    private $pathToYamlFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'test.yaml';
    private $pathToEqualFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'equal-test.json';
    private $pathToPlusFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'plus-test.json';
    private $pathToPlusMinusFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'plus-minus-test.json';

    /**
     * @dataProvider additionProvider
     * @param $expected
     * @param $pathToFile
     */
    public function testJsonDiff($expected, $pathToFile)
    {
        $this->assertEquals($expected, genDiff('json', $pathToFile, $this->pathToJsonFile));
    }

    /**
     * @dataProvider additionProvider
     * @param $expected
     * @param $pathToFile
     */
    public function testYamlDiff($expected, $pathToFile)
    {
        $this->assertEquals($expected, genDiff('json', $pathToFile, $this->pathToYamlFile));
    }

    public function additionProvider()
    {
        return [
            [
                '{"a":1,"b":2,"c":3,"d":4}',
                $this->pathToEqualFile
            ],
            [
                '{"+ a":1,"+ b":2,"+ c":3,"+ d":4}',
                $this->pathToPlusFile
            ],
            [
                '{"a":1,"+ b":2,"- b":"2","+ d":4,"- d":"new value","- new":"value","+ c":3}',
                $this->pathToPlusMinusFile
            ],
        ];
    }

    /**
     * @dataProvider addProvFileFormatException
     * @param $pathToFile
     */
    public function testFileFormatException($pathToFile)
    {
        try {
            genDiff('json', $pathToFile, $this->pathToEqualFile);
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
