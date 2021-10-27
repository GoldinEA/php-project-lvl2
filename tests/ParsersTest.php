<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use function Differ\Parsers\getFileData;


class ParsersTest extends TestCase
{
    public $testData = array(
        'host' => 'hexlet.io',
        'timeout' => 50,
        'proxy' => '123.234.53.22',
        'follow' => false,
    );

    public function testYaml()
    {
        $path = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'file1.yml');
        $fileData = getFileData($path);
        $this->assertEquals($this->testData, $fileData);
    }

    public function testJson()
    {
        $path = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'file3.json');
        $fileData = getFileData($path);
        $this->assertEquals($this->testData, $fileData);
    }
}
