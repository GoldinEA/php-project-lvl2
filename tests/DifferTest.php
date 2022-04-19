<?php

declare(strict_types=1);

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
     * @throws \Exception
     */
    public function testJson(string $format, string $testRes): void
    {
        $result = genDiff(
            self::FILE_PATH_JSON_1,
            self::FILE_PATH_JSON_2,
            $format
        );

        $this->assertFileExists(
            $testRes,
            $result
        );
    }

    /**
     * @dataProvider filesProvider
     * @throws \Exception
     */
    public function testYaml(string $format, string $testRes): void
    {
        $result = genDiff(
            self::FILE_PATH_YAML_1,
            self::FILE_PATH_YAML_2,
            $format
        );

        $this->assertFileExists(
            $testRes,
            $result
        );
    }


    /**
     * @dataProvider filesProvider
     * @throws \Exception
     */
    public function testYamlJson(string $format, string $testRes): void
    {
        $result = genDiff(
            self::FILE_PATH_YAML_1,
            self::FILE_PATH_JSON_2,
            $format
        );

        $this->assertFileExists(
            $testRes,
            $result
        );
    }


    public function filesProvider(): array
    {
        return [
            'stylish format' => ['stylish', realpath(__DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'result' . DIRECTORY_SEPARATOR . 'result.stylish')],
            'plain format' => ['plain', realpath(__DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'result' . DIRECTORY_SEPARATOR . 'result.plain')],
            'json format' => ['json', realpath(__DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'result' . DIRECTORY_SEPARATOR . 'result.json')]
        ];
    }
}
