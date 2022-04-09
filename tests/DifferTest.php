<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use function Differ\Differ\genDiff;

require_once __DIR__ . '/../vendor/autoload.php';

class DifferTest extends TestCase
{

    private const FILE_PATH_JSON_1 = __DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'file.json';
    private const FILE_PATH_JSON_2 = __DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'file1.json';

    private const FILE_PATH_YAML_1 = __DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'file.yml';
    private const FILE_PATH_YAML_2 = __DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'file1.yml';

    /**
     * @dataProvider filesProvider
     */
    public function testJson($format, $testRes)
    {
        $result = genDiff(
            self::FILE_PATH_JSON_1,
            self::FILE_PATH_JSON_2,
            $format
        );
        $this->assertEquals(str_replace("\r\n", PHP_EOL, $testRes), str_replace("\r\n", PHP_EOL, $result));
    }

    /**
     * @dataProvider filesProvider
     */
    public function testYaml($format, $testRes)
    {
        $result = genDiff(
            self::FILE_PATH_YAML_1,
            self::FILE_PATH_YAML_2,
            $format
        );
        $this->assertEquals(str_replace("\r\n", PHP_EOL, $testRes), str_replace("\r\n", PHP_EOL, $result));
    }


    public function filesProvider()
    {
        return [
            'stylish format' => ['stylish', file_get_contents(realpath(__DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'result' . DIRECTORY_SEPARATOR . 'result.stylish'))],
            'plain format' => ['plain', file_get_contents(realpath(__DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'result' . DIRECTORY_SEPARATOR . 'result.plain'))],
            'json format' => ['json', file_get_contents(realpath(__DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'result' . DIRECTORY_SEPARATOR . 'result.json'))]
        ];
    }
}
