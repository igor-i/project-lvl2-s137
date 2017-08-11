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
    const EXPECTED_JSON = '{"common":{"setting1":"Value 1","setting3":true,"- setting4":"blah blah","- setting5":{"key5":"value5"},"+ setting2":"200","+ setting6":{"key":"value"}},"group1":{"foo":"bar","+ baz":"bas","- baz":"bars"},"- group3":{"fee":"100500"},"+ group2":{"abc":"12345"}}';

    private $pathToFlatBeforeJsonFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'flat-before.json';
    private $pathToTreeBeforeJsonFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'tree-before.json';
    private $pathToFlatBeforeYamlFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'flat-before.yaml';
    private $pathToTreeBeforeYamlFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'tree-before.yaml';
    private $pathToTreeBeforeIniFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'tree-before.ini';
    private $pathToFlatEqualAfterJsonFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'flat-equal-after.json';
    private $pathToFlatMinusAfterJsonFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'flat-minus-after.json';
    private $pathToFlatAfterJsonFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'flat-plus-minus-after.json';
    private $pathToTreeAfterJsonFile = self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . 'tree-after.json';

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
            genDiff('json', $this->pathToTreeBeforeYamlFile, $this->pathToTreeAfterJsonFile)
        );
    }

    public function testTreeIniDiff()
    {
        $this->assertEquals(
            self::EXPECTED_JSON,
            genDiff('json', $this->pathToTreeBeforeIniFile, $this->pathToTreeAfterJsonFile)
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
}
