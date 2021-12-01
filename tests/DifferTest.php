<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use function Differ\Differ\createTree;
use function Differ\Differ\genDiff;

require_once __DIR__ . '/../vendor/autoload.php';

class DifferTest extends TestCase
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

    private string $testGendiff = '{
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

    private string $testPlain = "Property 'common.follow' was added with value: false
Property 'common.setting2' was removed
Property 'common.setting3' was updated. From true to null
Property 'common.setting4' was added with value: 'blah blah'
Property 'common.setting5' was added with value: [complex value]
Property 'common.setting6.doge.wow' was updated. From '' to 'so much'
Property 'common.setting6.ops' was added with value: 'vops'
Property 'group1.baz' was updated. From 'bas' to 'bars'
Property 'group1.nest' was updated. From [complex value] to 'str'
Property 'group2' was removed
Property 'group3' was added with value: [complex value]";


    public function testCreateTree()
    {
        $expected = [
            ['name' => 'follow', 'type' => 'deleted', 'value' => false, 'multilevel' => false, 'multivalued' => false],
            ['name' => 'host', 'type' => 'no_change', 'multivalued' => false, 'multilevel' => false, 'value' => 'hexlet.io'],
            ['name' => 'proxy', 'type' => 'deleted', 'value' => '123.234.53.22', 'multilevel' => false, 'multivalued' => false],
            ['name' => 'timeout', 'type' => 'changed', 'value_last_file' => 20, 'value_first_file' => 50, 'multivalued' => true, 'multilevel' => false],
            ['name' => 'verbose', 'type' => 'added', 'value' => true, 'multilevel' => false, 'multivalued' => false],
        ];

        $tree = createTree($this->dataFirstFile, $this->dataLastFile);
        $this->assertEquals($expected, $tree);
    }

    public function testCreateTreeMultilevel()
    {
        $expected = [
            [
                'name' => 'common',
                'type' => 'changed',
                'multilevel' => true,
                'value' =>
                    [
                        [
                            'name' => 'follow',
                            'type' => 'added',
                            'value' => false,
                            'multilevel' => false,
                            'multivalued' => false
                        ],
                        [
                            'name' => 'setting1',
                            'type' => 'no_change',
                            'value' => 'Value 1',
                            'multivalued' => false,
                            'multilevel' => false
                        ],
                        [
                            'name' => 'setting2',
                            'type' => 'deleted',
                            'value' => 200,
                            'multilevel' => false,
                            'multivalued' => false
                        ],
                        [
                            'name' => 'setting3',
                            'type' => 'changed',
                            'value_last_file' => null,
                            'value_first_file' => true,
                            'multilevel' => false,
                            'multivalued' => true
                        ],
                        [
                            'name' => 'setting4',
                            'type' => 'added',
                            'value' => 'blah blah',
                            'multilevel' => false,
                            'multivalued' => false
                        ],
                        [
                            'name' => 'setting5',
                            'type' => 'added',
                            'value' =>
                                [
                                    0 =>
                                        [
                                            'name' => 'key5',
                                            'type' => 'no_change',
                                            'value' => 'value5',
                                            'multivalued' => false,
                                            'multilevel' => false
                                        ],
                                ],
                            'multilevel' => true,
                            'multivalued' => false
                        ],
                        [
                            'name' => 'setting6',
                            'type' => 'changed',
                            'multilevel' => true,
                            'value' =>
                                [
                                    [
                                        'name' => 'doge',
                                        'type' => 'changed',
                                        'multilevel' => true,
                                        'value' =>
                                            [
                                                [
                                                    'name' => 'wow',
                                                    'type' => 'changed',
                                                    'value_last_file' => 'so much',
                                                    'value_first_file' => '',
                                                    'multilevel' => false,
                                                    'multivalued' => true

                                                ],
                                            ],
                                        'multivalued' => false
                                    ],
                                    [
                                        'name' => 'key',
                                        'type' => 'no_change',
                                        'value' => 'value',
                                        'multivalued' => false,
                                        'multilevel' => false
                                    ],

                                    [
                                        'name' => 'ops',
                                        'type' => 'added',
                                        'value' => 'vops',
                                        'multilevel' => false,
                                        'multivalued' => false
                                    ],
                                ],
                            'multivalued' => false
                        ],


                    ],
                'multivalued' => false
            ],
            [
                'name' => 'group1',
                'type' => 'changed',
                'multilevel' => true,
                'value' =>
                    [
                        [
                            'name' => 'baz',
                            'type' => 'changed',
                            'value_last_file' => 'bars',
                            'value_first_file' => 'bas',
                            'multilevel' => false,
                            'multivalued' => true
                        ],
                        [
                            'name' => 'foo',
                            'type' => 'no_change',
                            'value' => 'bar',
                            'multivalued' => false,
                            'multilevel' => false
                        ],
                        [
                            'name' => 'nest',
                            'type' => 'changed',
                            'value_last_file' => 'str',
                            'value_first_file' =>
                                [
                                    [
                                        'name' => 'key',
                                        'type' => 'no_change',
                                        'value' => 'value',
                                        'multivalued' => false,
                                        'multilevel' => false
                                    ],
                                ],
                            'multilevel' => true,
                            'multivalued' => true
                        ],
                    ],
                'multivalued' => false
            ],
            [
                'name' => 'group2',
                'type' => 'deleted',
                'value' =>
                    [
                        [
                            'name' => 'abc',
                            'type' => 'no_change',
                            'value' => 12345,
                            'multivalued' => false,
                            'multilevel' => false
                        ],
                        [
                            'name' => 'deep',
                            'type' => 'no_change',
                            'multilevel' => true,
                            'value' =>
                                [
                                    [
                                        'name' => 'id',
                                        'type' => 'no_change',
                                        'value' => 45,
                                        'multivalued' => false,
                                        'multilevel' => false
                                    ],
                                ],
                            'multivalued' => false
                        ],
                    ],
                'multilevel' => true,
                'multivalued' => false
            ],
            [
                'name' => 'group3',
                'type' => 'added',
                'value' =>
                    [
                        [
                            'name' => 'deep',
                            'type' => 'no_change',
                            'multilevel' => true,
                            'value' =>
                                [
                                    [
                                        'name' => 'id',
                                        'type' => 'no_change',
                                        'multilevel' => true,
                                        'value' =>
                                            [
                                                [
                                                    'name' => 'number',
                                                    'type' => 'no_change',
                                                    'value' => 45,
                                                    'multivalued' => false,
                                                    'multilevel' => false
                                                ],
                                            ],
                                        'multivalued' => false
                                    ],
                                ],
                            'multivalued' => false
                        ],

                        [
                            'name' => 'fee',
                            'type' => 'no_change',
                            'value' => 100500,
                            'multivalued' => false,
                            'multilevel' => false
                        ],
                    ],
                'multilevel' => true,
                'multivalued' => false
            ],
        ];

        $tree = createTree($this->dataFirstFileMultilevel, $this->dataLastFileMultilevel);
        $this->assertEquals($expected, $tree);
    }

    public function testGendiff()
    {
        $result = genDiff(
            realpath(__DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'file.json'),
            realpath(__DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'file1.json'),
        );
        $this->assertEquals($this->testGendiff, $result);
    }

    public function testGendiffPlain()
    {
        $result = genDiff(
            realpath(__DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'file.json'),
            realpath(__DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'file1.json'),
            'plain'
        );
        $this->assertEquals($this->testPlain, $result);
    }
}
