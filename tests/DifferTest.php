<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use function Differ\Differ\createTree;

require_once __DIR__ . '/../vendor/autoload.php';

class DifferTest extends TestCase
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
            ['name' => 'host', 'type' => 'no_change', 'value' => 'hexlet.io', 'multivalued' => false, 'multilevel' => false],
            ['name' => 'timeout', 'type' => 'changed', 'value_added' => 20, 'value_deleted' => 50, 'multivalued' => true, 'multilevel' => false],
            ['name' => 'proxy', 'type' => 'deleted', 'value' => '123.234.53.22', 'multilevel' => false, 'multivalued' => false],
            ['name' => 'follow', 'type' => 'deleted', 'value' => false, 'multilevel' => false, 'multivalued' => false],
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
                            'value_added' => NULL,
                            'value_deleted' => true,
                            'multilevel' => false,
                            'multivalued' => true
                        ],
                        [
                            'name' => 'setting6',
                            'type' => 'changed',
                            'multilevel' => true,
                            'value' =>
                                [
                                    [
                                        'name' => 'key',
                                        'type' => 'no_change',
                                        'value' => 'value',
                                        'multivalued' => false,
                                        'multilevel' => false
                                    ],
                                    [
                                        'name' => 'doge',
                                        'type' => 'changed',
                                        'multilevel' => true,
                                        'value' =>
                                            [
                                                [
                                                    'name' => 'wow',
                                                    'type' => 'changed',
                                                    'value_added' => 'so much',
                                                    'value_deleted' => '',
                                                    'multilevel' => false,
                                                    'multivalued' => true

                                                ],
                                            ],
                                        'multivalued' => false
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
                        [
                            'name' => 'follow',
                            'type' => 'added',
                            'value' => false,
                            'multilevel' => false,
                            'multivalued' => false
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
                            'value_added' => 'bars',
                            'value_deleted' => 'bas',
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
                            'value_added' => 'str',
                            'value_deleted' =>
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
}
