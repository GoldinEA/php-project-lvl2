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
        $fileData = getFileData('fixures/file1.yml');
        $this->assertEquals($this->testData, $fileData);
    }

    public function testJson()
    {
        $fileData = getFileData('fixures/file3.json');
        $this->assertEquals($this->testData, $fileData);
    }
}
