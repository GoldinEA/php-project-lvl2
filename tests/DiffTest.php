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

//    public function testGetFileData()
//    {
//
//    }
//
//    public function testGetJsonInfo()
//    {
//
//    }
//
//    public function testYamlInfo()
//    {
//
//    }
//
//    public function testDiffer()
//    {
//
//    }

    public function testCreateTree()
    {

//        $expected = [
//            'children' => [
//                [
//                    'children' => [
//                        [
//                            'children' => [], 'meta' => [], 'name' => 'NGINX', 'type' => 'directory',
//                        ],
//                        [
//                            'children' => [
//                                ['meta' => [], 'name' => 'CONFIG.JSON', 'type' => 'file']
//                            ],
//                            'meta' => [],
//                            'name' => 'CONSUL',
//                            'type' => 'directory',
//                        ],
//                    ],
//                    'meta' => [],
//                    'name' => 'ETC',
//                    'type' => 'directory',
//                ],
//                ['meta' => [], 'name' => 'HOSTS', 'type' => 'file'],
//            ],
//            'meta' => [],
//            'name' => '/',
//            'type' => 'directory',
//        ];
        // [
//    name => 'ETC',
//    children => [
//        [ name => 'CONFIG', meta => [], type => 'file' ],
//        [ name => 'HOSTS', meta => [], type => 'file' ]
//    ],
//    meta => [],
//    type => 'directory'
// ]
//{
//    common: {
//      + follow: false
//        setting1: Value 1
//      - setting2: 200
//      - setting3: true
//      + setting3: null
//      + setting4: blah blah
//      + setting5: {
//            key5: value5
//        }
//        setting6: {
//            doge: {
//              - wow:
//              + wow: so much
//            }
//            key: value
//          + ops: vops
//        }
//    }
//    group1: {
//      - baz: bas
//      + baz: bars
//        foo: bar
//      - nest: {
//            key: value
//        }
//      + nest: str
//    }
//  - group2: {
//        abc: 12345
//        deep: {
//            id: 45
//        }
//    }
//  + group3: {
//        deep: {
//            id: {
//                number: 45
//            }
//        }
//        fee: 100500
//    }
//}

        $expected = [
            ['name' => 'host', 'type' => 'no_change', 'value' => 'hexlet.io'],
            ['name' => 'timeout', 'type' => 'changed', 'value_added' => 50, 'value_deleted' => 20],
            ['name' => 'verbose', 'type' => 'added', 'value' => true],
            ['name' => 'proxy', 'type' => 'deleted', 'value' => '123.234.53.22'],
            ['name' => 'follow', 'type' => 'deleted', 'value' => false],

        ];

        $tree = createTree($this->dataFirstFile, $this->dataLastFile);
        $this->assertEquals($expected, $tree);
    }
}
