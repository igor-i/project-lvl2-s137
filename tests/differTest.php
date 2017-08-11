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
    const EXPECTED_JSON = '{"common":{"setting1":"Value 1","- setting2":"200","setting3":true,"- setting6":{"key":"value"},"+ setting4":"blah blah","+ setting5":{"key5":"value5"}},"group1":{"+ baz":"bars","- baz":"bas","foo":"bar"},"- group2":{"abc":"12345"},"+ group3":{"fee":"100500"}}';

    private $pathToFlatJsonFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'flat-before.json';
    private $pathToTreeJsonFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'tree-before.json';
    private $pathToFlatYamlFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'flat-before.yaml';
    private $pathToFlatEqualFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'flat-equal-after.json';
    private $pathToFlatPlusFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'flat-plus-after.json';
    private $pathToFlatPlusMinusFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'flat-plus-minus-after.json';
    private $pathToTreePlusMinusFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'tree-after.json';

    /**
     * @dataProvider additionProviderFlat
     * @param $expected
     * @param $pathToFile
     */
    public function testFlatJsonDiff($expected, $pathToFile)
    {
        $this->assertEquals($expected, genDiff('json', $pathToFile, $this->pathToFlatJsonFile));
    }

    /**
     * @dataProvider additionProviderFlat
     * @param $expected
     * @param $pathToFile
     */
    public function testFlatYamlDiff($expected, $pathToFile)
    {
        $this->assertEquals($expected, genDiff('json', $pathToFile, $this->pathToFlatYamlFile));
    }

    public function additionProviderFlat()
    {
        return [
            [
                '{"a":1,"b":2,"c":3,"d":4}',
                $this->pathToFlatEqualFile
            ],
            [
                '{"+ a":1,"+ b":2,"+ c":3,"+ d":4}',
                $this->pathToFlatPlusFile
            ],
            [
                '{"a":1,"+ b":2,"- b":"2","+ d":4,"- d":"new value","- new":"value","+ c":3}',
                $this->pathToFlatPlusMinusFile
            ],
        ];
    }

    public function testTreeJsonDiff()
    {
        $this->assertEquals(
            self::EXPECTED_JSON,
            genDiff('json', $this->pathToTreePlusMinusFile, $this->pathToTreeJsonFile)
        );
    }

    /**
     * @dataProvider addProvFileFormatException
     * @param $pathToFile
     */
    public function testFileFormatException($pathToFile)
    {
        try {
            genDiff('json', $pathToFile, $this->pathToFlatEqualFile);
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
            genDiff('json', $this->pathToFlatEqualFile, 'non-existent.json');
            $this->fail('expected exception');
        } catch (\Exception $e) {
        }
    }
}
