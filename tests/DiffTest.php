<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use function Differ\Diff\createTree as createTree;
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
        $expected = [
            'overlap' => [
                'host' => 'hexlet.io'
            ],
            'timeout' => [
                '-' => 20,
                '+' => 50
            ],
            '+' => ['proxy' => '123.234.53.22', 'follow' => false],
            '-' => ['verbose' => true]
        ];

        $tree = createTree($this->dataFirstFile, $this->dataLastFile);
        $this->assertEquals($expected, $tree);
    }
}
