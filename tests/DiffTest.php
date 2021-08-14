<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use function Differ\Diff\createTree;

require_once __DIR__ . '/../vendor/autoload.php';

class DiffTest extends TestCase
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


    public function testCreateTree()
    {
        $expected = [
            ['name' => 'host', 'type' => 'no_change', 'value' => 'hexlet.io'],
            ['name' => 'timeout', 'type' => 'changed', 'value_added' => 20, 'value_deleted' => 50],
            ['name' => 'proxy', 'type' => 'deleted', 'value' => '123.234.53.22'],
            ['name' => 'follow', 'type' => 'deleted', 'value' => false],
            ['name' => 'verbose', 'type' => 'added', 'value' => true],
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
                            'name' => 'setting1',
                            'type' => 'deleted',
                            'value' => 'Value 1',
                        ],
                        [
                            'name' => 'setting2',
                            'type' => 'deleted',
                            'value' => 200,
                        ],
                        [
                            'name' => 'setting3',
                            'type' => 'deleted',
                            'value' => true,
                        ],
                        [
                            'name' => 'setting6',
                            'type' => 'deleted',
                            'value' =>
                                [
                                    'key' => 'value',
                                    'doge' =>
                                        [
                                            'wow' => '',
                                        ],
                                ],
                        ],
                        [
                            'name' => 'common',
                            'type' => 'added',
                            'value' =>
                                [
                                    'follow' => false,
                                    'setting1' => 'Value 1',
                                    'setting3' => NULL,
                                    'setting4' => 'blah blah',
                                    'setting5' =>
                                        [
                                            'key5' => 'value5',
                                        ],
                                    'setting6' =>
                                        [
                                            'key' => 'value',
                                            'ops' => 'vops',
                                            'doge' =>
                                                [
                                                    'wow' => 'so much',
                                                ],
                                        ],
                                ],
                        ],
                        [
                            'name' => 'group1',
                            'type' => 'added',
                            'value' =>
                                [
                                    'foo' => 'bar',
                                    'baz' => 'bars',
                                    'nest' => 'str',
                                ],
                        ],
                        [
                            'name' => 'group3',
                            'type' => 'added',
                            'value' =>
                                [
                                    'deep' =>
                                        [
                                            'id' =>
                                                [
                                                    'number' => 45,
                                                ],
                                        ],
                                    'fee' => 100500,
                                ],
                        ],
                    ],
            ],
            [
                'name' => 'group1',
                'type' => 'changed',
                'multilevel' => true,
                'value' =>
                    [
                        [
                            'name' => 'baz',
                            'type' => 'deleted',
                            'value' => 'bas',
                        ],
                        [
                            'name' => 'foo',
                            'type' => 'deleted',
                            'value' => 'bar',
                        ],
                        [
                            'name' => 'nest',
                            'type' => 'deleted',
                            'value' =>
                                [
                                    'key' => 'value',
                                ],
                        ],
                        [
                            'name' => 'common',
                            'type' => 'added',
                            'value' =>
                                [
                                    'follow' => false,
                                    'setting1' => 'Value 1',
                                    'setting3' => NULL,
                                    'setting4' => 'blah blah',
                                    'setting5' =>
                                        [
                                            'key5' => 'value5',
                                        ],
                                    'setting6' =>
                                        [
                                            'key' => 'value',
                                            'ops' => 'vops',
                                            'doge' =>
                                                [
                                                    'wow' => 'so much',
                                                ],
                                        ],
                                ],
                        ],
                        [
                            'name' => 'group1',
                            'type' => 'added',
                            'value' =>
                                [
                                    'foo' => 'bar',
                                    'baz' => 'bars',
                                    'nest' => 'str',
                                ],
                        ],
                        [
                            'name' => 'group3',
                            'type' => 'added',
                            'value' =>
                                [
                                    'deep' =>
                                        [
                                            'id' =>
                                                [
                                                    'number' => 45,
                                                ],
                                        ],
                                    'fee' => 100500,
                                ],
                        ],
                    ],
            ],

            [
                'name' => 'group2',
                'type' => 'deleted',
                'value' =>
                    [
                        'abc' => 12345,
                        'deep' =>
                            [
                                'id' => 45,
                            ],
                    ],
            ],

            [
                'name' => 'group3',
                'type' => 'added',
                'value' =>
                    [
                        'deep' =>
                            [
                                'id' =>
                                    [
                                        'number' => 45,
                                    ],
                            ],
                        'fee' => 100500,
                    ],
            ],
        ];

        $tree = createTree($this->dataFirstFileMultilevel, $this->dataLastFileMultilevel);
        $this->assertEquals($expected, $tree);
    }
}
