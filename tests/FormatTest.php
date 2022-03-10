<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use function Differ\Differ\buildDiff;
use function Differ\Format\format;

require_once __DIR__ . '/../vendor/autoload.php';

class FormatTest extends TestCase
{
    private array $dataFirstFile = [
        'host' => 'hexlet.io',
        'timeout' => 50,
        'proxy' => '123.234.53.22',
        'follow' => false
    ];

    private array $dataLastFile = [
        'host' => 'hexlet.io',
        'timeout' => 20,
        'verbose' => true
    ];

    private array $dataLastFileMultilevel = [
        'common' => [
            'follow' => false,
            'setting1' => 'Value 1',
            'setting3' => null,
            'setting4' => "blah blah",
            "setting5" => [
                "key5" => "value5"
            ],
            "setting6" => [
                "key" => "value",
                "ops" => "vops",
                "doge" => [
                    "wow" => "so much"
                ]
            ]
        ],
        'group1' => [
            "foo" => "bar",
            "baz" => "bars",
            "nest" => "str"
        ],
        'group3' => [
            "deep" => [
                "id" => [
                    "number" => 45
                ]
            ],
            "fee" => 100500
        ],
    ];

    private array $dataFirstFileMultilevel = [
        "common" => [
            "setting1" => "Value 1",
            "setting2" => 200,
            "setting3" => true,
            "setting6" => [
                "key" => "value",
                "doge" => [
                    "wow" => ""
                ]
            ]
        ],
        "group1" => [
            "baz" => "bas",
            "foo" => "bar",
            "nest" => [
                "key" => "value"
            ]
        ],
        "group2" => [
            "abc" => 12345,
            "deep" => [
                "id" => 45
            ]
        ]
    ];

    private string $resultMultilevel =
    '{' . PHP_EOL .
    '   common: {' . PHP_EOL .
    '      + follow: false' . PHP_EOL .
    '        setting1: Value 1' . PHP_EOL .
    '      - setting2: 200' . PHP_EOL .
    '      - setting3: true' . PHP_EOL .
    '      + setting3: null' . PHP_EOL .
    '      + setting4: blah blah' . PHP_EOL .
    '      + setting5: {' . PHP_EOL .
    '          key5: value5' . PHP_EOL .
    '        }' . PHP_EOL .
    '       setting6: {' . PHP_EOL .
    '           doge: {' . PHP_EOL .
    '              - wow: ' . PHP_EOL .
    '              + wow: so much' . PHP_EOL .
    '            }' . PHP_EOL .
    '            key: value' . PHP_EOL .
    '          + ops: vops' . PHP_EOL .
    '        }' . PHP_EOL .
    '    }' . PHP_EOL .
    '   group1: {' . PHP_EOL .
    '      - baz: bas' . PHP_EOL .
    '      + baz: bars' . PHP_EOL .
    '        foo: bar' . PHP_EOL .
    '      - nest: {' . PHP_EOL .
    '          key: value' . PHP_EOL .
    '        }' . PHP_EOL .
    '      + nest: str' . PHP_EOL .
    '    }' . PHP_EOL .
    '  - group2: {' . PHP_EOL .
    '      abc: 12345' . PHP_EOL .
    '      deep: {' . PHP_EOL .
    '          id: 45' . PHP_EOL .
    '        }' . PHP_EOL .
    '    }' . PHP_EOL .
    '  + group3: {' . PHP_EOL .
    '      deep: {' . PHP_EOL .
    '          id: {' . PHP_EOL .
    '              number: 45' . PHP_EOL .
    '            }' . PHP_EOL .
    '        }' . PHP_EOL .
    '      fee: 100500' . PHP_EOL .
    '    }' . PHP_EOL .
    '}';

    private string $resultMultilevelJson =
        '[{"name":"common","type":"parent",'
        .'"child":[{"name":"follow","type":"added","value":false},'
        .'{"name":"setting1","type":"no_change","value":"Value 1"},'
        .'{"name":"setting2","type":"deleted","value":200},'
        .'{"name":"setting3","type":"changed","value_last_data":true,"value_first_data":null},'
        .'{"name":"setting4","type":"added","value":"blah blah"},'
        .'{"name":"setting5","type":"added","value":{"key5":"value5"}},'
        .'{"name":"setting6","type":"parent","child":'
        .'[{"name":"doge","type":"parent","child":'
        .'[{"name":"wow","type":"changed","value_last_data":"","value_first_data":"so much"}]},'
        .'{"name":"key","type":"no_change","value":"value"},{"name":"ops","type":"added","value":"vops"}]}]}'
        .',{"name":"group1","type":"parent","child":'
        .'[{"name":"baz","type":"changed","value_last_data":"bas","value_first_data":"bars"},'
        .'{"name":"foo","type":"no_change","value":"bar"},'
        .'{"name":"nest","type":"changed","value_last_data":{"key":"value"},"value_first_data":"str"}]},'
        .'{"name":"group2","type":"deleted","value":{"abc":12345,"deep":{"id":45}}},'
        .'{"name":"group3","type":"added","value":{"deep":{"id":{"number":45}},"fee":100500}}]';

    private string $resultSingleLevel =
        '{' . PHP_EOL .
        '  - follow: false' . PHP_EOL .
        '    host: hexlet.io' . PHP_EOL .
        '  - proxy: 123.234.53.22' . PHP_EOL .
        '  - timeout: 50' . PHP_EOL .
        '  + timeout: 20' . PHP_EOL .
        '  + verbose: true' . PHP_EOL .
        '}';

    private string $resultPlainMultilevel =
        "Property 'follow' was added with value: false" . PHP_EOL .
        "Property 'setting2' was removed" . PHP_EOL .
        "Property 'setting3' was updated. From null to true" . PHP_EOL .
        "Property 'setting4' was added with value: 'blah blah'" . PHP_EOL .
        "Property 'setting5' was added with value: [complex value]" . PHP_EOL .
        "Property 'wow' was updated. From 'so much' to ''" . PHP_EOL .
        "Property 'ops' was added with value: 'vops'" . PHP_EOL .
        "Property 'baz' was updated. From 'bars' to 'bas'" . PHP_EOL .
        "Property 'nest' was updated. From 'str' to [complex value]" . PHP_EOL .
        "Property 'group2' was removed" . PHP_EOL .
        "Property 'group3' was added with value: [complex value]";


    /**
     * @throws \Exception
     */
    public function testDiffHandlerMultilevel()
    {
        $tree = buildDiff($this->dataFirstFileMultilevel, $this->dataLastFileMultilevel);
        $stringMultilevelResult = \Differ\Formatters\Stylish\format($tree);
        $this->assertEquals($this->resultMultilevel, $stringMultilevelResult);
    }

    public function testDiffHandlerMultilevelJson()
    {
        $tree = buildDiff($this->dataFirstFileMultilevel, $this->dataLastFileMultilevel);
        $stringMultilevelResult = \Differ\Formatters\Json\format($tree);
        $this->assertEquals($this->resultMultilevelJson, $stringMultilevelResult);
    }

    public function testDiffHandlerMultilevelPlain()
    {
        $tree = buildDiff($this->dataFirstFileMultilevel, $this->dataLastFileMultilevel);
        $stringMultilevelResult = \Differ\Formatters\Plain\format($tree);
        $this->assertEquals($this->resultPlainMultilevel, $stringMultilevelResult);
    }

    /**
     * @throws \Exception
     */
    public function testDiffHandlerSinglelevel()
    {
        $tree = buildDiff($this->dataFirstFile, $this->dataLastFile);
        $stringSinglelevelResult = format($tree, 'stylish');
        $this->assertEquals($this->resultSingleLevel, $stringSinglelevelResult);
    }
}
