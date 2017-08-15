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
    const TEST_FIXTURES_DIR = 'tests'  . DIRECTORY_SEPARATOR . 'fixtures';
    const EXPECTED_JSON = '[{"type":"nested","node":"common","children":[{"type":"unchanged","node":"setting1","from":"Value 1","to":"Value 1"},{"type":"removed","node":"setting2","from":"200","to":""},{"type":"unchanged","node":"setting3","from":true,"to":true},{"type":"removed","node":"setting6","from":{"key":"value"},"to":""},{"type":"added","node":"setting4","from":"","to":"blah blah"},{"type":"added","node":"setting5","from":"","to":{"key5":"value5"}}]},{"type":"nested","node":"group1","children":[{"type":"changed","node":"baz","from":"bas","to":"bars"},{"type":"unchanged","node":"foo","from":"bar","to":"bar"}]},{"type":"removed","node":"group2","from":{"abc":"12345"},"to":""},{"type":"added","node":"group3","from":"","to":{"fee":"100500"}}]';
    const EXPECTED_PLAIN = <<<PLAIN
Property 'common.setting2' was removed
Property 'common.setting6' was removed
Property 'common.setting4' was added with value: 'blah blah'
Property 'common.setting5' was added with value: 'complex value'
Property 'group1.baz' was changed. From 'bas' to 'bars'
Property 'group2' was removed
Property 'group3' was added with value: 'complex value'
PLAIN;
    const EXPECTED_PRETTY = <<<PRETTY
{
    "common": {
        "setting1": "Value 1"
      - "setting2": "200"
        "setting3": true
      - "setting6": {
            "key": "value"
        }
      + "setting4": "blah blah"
      + "setting5": {
            "key5": "value5"
        }
    }
    "group1": {
      + "baz": "bars"
      - "baz": "bas"
        "foo": "bar"
    }
  - "group2": {
        "abc": "12345"
    }
  + "group3": {
        "fee": "100500"
    }
}
PRETTY;

    private function getFixturePath($fixtureName)
    {
        return self::TEST_FIXTURES_DIR . DIRECTORY_SEPARATOR . $fixtureName;
    }

    /**
     * @dataProvider additionProviderFlat
     * @param $expected
     * @param $pathToFile
     */
    public function testFlatJsonDiff($expected, $pathToFile)
    {
        $this->assertEquals($expected, genDiff('json', $this->getFixturePath('flat-before.json'), $pathToFile));
    }

    /**
     * @dataProvider additionProviderFlat
     * @param $expected
     * @param $pathToFile
     */
    public function testFlatYamlDiff($expected, $pathToFile)
    {
        $this->assertEquals($expected, genDiff('json', $this->getFixturePath('flat-before.yaml'), $pathToFile));
    }

    public function additionProviderFlat()
    {
        return [
            [
                '{"a":1,"b":2,"c":3,"d":4}',
                $this->getFixturePath('flat-equal-after.json')
            ],
            [
                '{"- a":1,"- b":2,"- c":3,"- d":4}',
                $this->getFixturePath('flat-minus-after.json')
            ],
            [
                '{"a":1,"+ b":"2","- b":2,"- c":3,"+ d":"new value","- d":4,"+ new":"value"}',
                $this->getFixturePath('flat-plus-minus-after.json')
            ],
        ];
    }

    public function testTreeJsonDiff()
    {
        $this->assertEquals(
            self::EXPECTED_JSON,
            genDiff('json', $this->getFixturePath('tree-before.json'), $this->getFixturePath('tree-after.json'))
        );
    }

    public function testTreeYamlDiff()
    {
        $this->assertEquals(
            self::EXPECTED_JSON,
            genDiff('json', $this->getFixturePath('tree-before.json'), $this->getFixturePath('tree-after.yaml'))
        );
    }

    public function testPlainReport()
    {
        $this->assertEquals(
            self::EXPECTED_PLAIN,
            genDiff('plain', $this->getFixturePath('tree-before.json'), $this->getFixturePath('tree-after.json'))
        );
    }

    public function testPrettyReport()
    {
        $this->assertEquals(
            self::EXPECTED_PRETTY,
            genDiff('pretty', $this->getFixturePath('tree-before.json'), $this->getFixturePath('tree-after.json'))
        );
    }

    /**
     * @dataProvider addProvFileFormatException
     * @param $pathToFile
     */
    public function testFileFormatException($pathToFile)
    {
        try {
            genDiff('json', $pathToFile, $this->getFixturePath('tree-after.json'));
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
            genDiff('json', 'non-existent.json', $this->getFixturePath('tree-after.json'));
            $this->fail('expected exception');
        } catch (\Exception $e) {
        }
    }

    public function testReportFormatException()
    {
        try {
            genDiff('wrong', $this->getFixturePath('tree-before.json'), $this->getFixturePath('tree-after.json'));
            $this->fail('expected exception');
        } catch (\Exception $e) {
        }
    }
}
