<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use function Differ\Differ\buildDiv;
use function Differ\Differ\createTree;
use function Differ\Format\format;

require_once __DIR__ . '/../vendor/autoload.php';

class FormatTest extends TestCase
{
    private $dataFirstFile = [
        'host' => 'hexlet.io',
        'timeout' => 50,
        'proxy' => '123.234.53.22',
        'follow' => false
    ];

    private $dataLastFile = [
        'host' => 'hexlet.io',
        'timeout' => 20,
        'verbose' => true
    ];

    private $dataLastFileMultilevel = [
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

    private $dataFirstFileMultilevel = [
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

    private $resultMultilevel =
'{
    common: {
      + follow: false
        setting1: Value 1
      - setting2: 200
      - setting3: true
      + setting3: null
      + setting4: blah blah
      + setting5: {
            key5: value5
        }
        setting6: {
            doge: {
              - wow: 
              + wow: so much
            }
            key: value
          + ops: vops
        }
    }
    group1: {
      - baz: bas
      + baz: bars
        foo: bar
      - nest: {
            key: value
        }
      + nest: str
    }
  - group2: {
        abc: 12345
        deep: {
            id: 45
        }
    }
  + group3: {
        deep: {
            id: {
                number: 45
            }
        }
        fee: 100500
    }
}';
    private $resultMultilevelJson =
        '{
    common: {
      + follow: false
        setting1: Value 1
      - setting2: 200
      - setting3: true
      + setting3: null
      + setting4: blah blah
      + setting5: {
            key5: value5
        }
        setting6: {
            doge: {
              - wow: 
              + wow: so much
            }
            key: value
          + ops: vops
        }
    }
    group1: {
      - baz: bas
      + baz: bars
        foo: bar
      - nest: {
            key: value
        }
      + nest: str
    }
  - group2: {
        abc: 12345
        deep: {
            id: 45
        }
    }
  + group3: {
        deep: {
            id: {
                number: 45
            }
        }
        fee: 100500
    }
}';

    private $resultSingleLevel =
        '{' . PHP_EOL .
        '- follow: false' . PHP_EOL .
        ' host: hexlet.io' . PHP_EOL .
        '- proxy: 123.234.53.22' . PHP_EOL .
        '- timeout: 20' . PHP_EOL .
        '+ timeout: 50' . PHP_EOL .
        '+ verbose: true' . PHP_EOL .
        '}';


    public function testDiffHandlerMultilevel()
    {
        $tree = buildDiv($this->dataFirstFileMultilevel, $this->dataLastFileMultilevel);
        $stringMultilevelResult = \Differ\Formatters\Stylish\create($tree);
        $this->assertEquals($this->resultMultilevel, $stringMultilevelResult);
    }

    public function testDiffHandlerMultilevelJson()
    {
        $tree = buildDiv($this->dataFirstFileMultilevel, $this->dataLastFileMultilevel);
        $stringMultilevelResult = json_encode(\Differ\Formatters\Json\create($tree));
        $this->assertEquals($this->resultMultilevel, $stringMultilevelResult);
    }

    public function testDiffHandlerMultilevelPlain()
    {
        $tree = buildDiv($this->dataFirstFileMultilevel, $this->dataLastFileMultilevel);
        $stringMultilevelResult = \Differ\Formatters\Plain\create($tree);
        $this->assertEquals($this->resultMultilevel, $stringMultilevelResult);
    }

    public function testDiffHandlerSinglelevel()
    {
        $tree = buildDiv($this->dataFirstFile, $this->dataLastFile);
        $stringSinglelevelResult = format($tree, 'stylish');
        $this->assertEquals($this->resultSingleLevel, $stringSinglelevelResult);
    }
}
