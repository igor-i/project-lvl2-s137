<?php
/**
 * User: Inkovskiy
 * Date: 09.08.17
 * Time: 1:18
 */

namespace Differ\tests;

//require_once 'src/differ.php';

use \PHPUnit\Framework\TestCase;
use function \Differ\differ\genDiff;

class DifferTest extends TestCase
{
    const EXPECTED_JSON = '{"common":{"setting1":"Value 1","- setting2":"200","setting3":true,"- setting6":{"key":"value"},"+ setting4":"blah blah","+ setting5":{"key5":"value5"}},"group1":{"+ baz":"bars","- baz":"bas","foo":"bar"},"- group2":{"abc":"12345"},"+ group3":{"fee":"100500"}}';
    const EXPECTED_PLAIN = <<<PLAIN
Property 'common.setting2' was removed
Property 'common.setting6' was removed
Property 'common.setting4' was added with value: 'blah blah'
Property 'common.setting5' was added with value: 'complex value'
Property group1.baz was changed. From 'bas' to 'bars'
Property 'group2' was removed
Property 'group3' was added with value: 'complex value'
PLAIN;

    const TEST_FIXTURES_DIR = 'tests'  . DIRECTORY_SEPARATOR . 'fixtures';

    private $pathToFlatBeforeJsonFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'flat-before.json';
    private $pathToTreeBeforeJsonFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'tree-before.json';
    private $pathToFlatBeforeYamlFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'flat-before.yaml';
    private $pathToFlatEqualAfterJsonFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'flat-equal-after.json';
    private $pathToFlatMinusAfterJsonFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'flat-minus-after.json';
    private $pathToFlatAfterJsonFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'flat-plus-minus-after.json';
    private $pathToTreeAfterJsonFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'tree-after.json';
    private $pathToTreeAfterYamlFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'tree-after.yaml';
    private $pathToTreeAfterIniFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'tree-after.ini';

    /**
     * @dataProvider additionProviderFlat
     * @param $expected
     * @param $pathToFile
     */
    public function testFlatJsonDiff($expected, $pathToFile)
    {
        $this->assertEquals($expected, genDiff('json', $this->pathToFlatBeforeJsonFile, $pathToFile));
    }

    /**
     * @dataProvider additionProviderFlat
     * @param $expected
     * @param $pathToFile
     */
    public function testFlatYamlDiff($expected, $pathToFile)
    {
        $this->assertEquals($expected, genDiff('json', $this->pathToFlatBeforeYamlFile, $pathToFile));
    }

    public function additionProviderFlat()
    {
        return [
            [
                '{"a":1,"b":2,"c":3,"d":4}',
                $this->pathToFlatEqualAfterJsonFile
            ],
            [
                '{"- a":1,"- b":2,"- c":3,"- d":4}',
                $this->pathToFlatMinusAfterJsonFile
            ],
            [
                '{"a":1,"+ b":"2","- b":2,"- c":3,"+ d":"new value","- d":4,"+ new":"value"}',
                $this->pathToFlatAfterJsonFile
            ],
        ];
    }

    public function testTreeJsonDiff()
    {
        $this->assertEquals(
            self::EXPECTED_JSON,
            genDiff('json', $this->pathToTreeBeforeJsonFile, $this->pathToTreeAfterJsonFile)
        );
    }

    public function testTreeYamlDiff()
    {
        $this->assertEquals(
            self::EXPECTED_JSON,
            genDiff('json', $this->pathToTreeBeforeJsonFile, $this->pathToTreeAfterYamlFile)
        );
    }

    public function testTreeIniDiff()
    {
        $this->assertEquals(
            self::EXPECTED_JSON,
            genDiff('json', $this->pathToTreeBeforeJsonFile, $this->pathToTreeAfterIniFile)
        );
    }

    public function testPlainReport()
    {
        $this->assertEquals(
            self::EXPECTED_PLAIN,
            genDiff('plain', $this->pathToTreeBeforeJsonFile, $this->pathToTreeAfterJsonFile)
        );
    }

    /**
     * @dataProvider addProvFileFormatException
     * @param $pathToFile
     */
    public function testFileFormatException($pathToFile)
    {
        try {
            genDiff('json', $pathToFile, $this->pathToFlatEqualAfterJsonFile);
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
            genDiff('json', 'non-existent.json', $this->pathToFlatEqualAfterJsonFile);
            $this->fail('expected exception');
        } catch (\Exception $e) {
        }
    }

    public function testReportFormatException()
    {
        try {
            genDiff('wrong-format', $this->pathToFlatBeforeJsonFile, $this->pathToFlatEqualAfterJsonFile);
            $this->fail('expected exception');
        } catch (\Exception $e) {
        }
    }
}
