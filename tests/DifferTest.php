<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use function Differ\Differ\genDiff;

require_once __DIR__ . '/../vendor/autoload.php';

class DifferTest extends TestCase
{
    private const BASE_FILE_PATH = __DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR;

    /**
     * @dataProvider filesProvider
     * @throws \Exception
     */
    public function testGendiff(string $format, string $testRes, string $file1, string $file2): void
    {
        $result = genDiff(
            self::BASE_FILE_PATH . $file1,
            self::BASE_FILE_PATH . $file2,
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
            'stylish format json' => ['stylish', realpath(__DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'result' . DIRECTORY_SEPARATOR . 'result.stylish'), 'file.json', 'file1.json'],
            'stylish format yaml' => ['stylish', realpath(__DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'result' . DIRECTORY_SEPARATOR . 'result.stylish'), 'file.yml', 'file1.yml'],
            'stylish format combo' => ['stylish', realpath(__DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'result' . DIRECTORY_SEPARATOR . 'result.stylish'), 'file.json', 'file1.yml'],
            'plain format json' => ['plain', realpath(__DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'result' . DIRECTORY_SEPARATOR . 'result.plain'), 'file.json', 'file1.json'],
            'plain format yaml' => ['plain', realpath(__DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'result' . DIRECTORY_SEPARATOR . 'result.plain'), 'file.yml', 'file1.yml'],
            'plain format combo' => ['plain', realpath(__DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'result' . DIRECTORY_SEPARATOR . 'result.plain'), 'file.json', 'file1.yml'],
            'json format json' => ['json', realpath(__DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'result' . DIRECTORY_SEPARATOR . 'result.json'), 'file.json', 'file1.json'],
            'json format yaml' => ['json', realpath(__DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'result' . DIRECTORY_SEPARATOR . 'result.json'), 'file.yml', 'file1.yml'],
            'json format combo' => ['json', realpath(__DIR__ . DIRECTORY_SEPARATOR . 'fixures' . DIRECTORY_SEPARATOR . 'result' . DIRECTORY_SEPARATOR . 'result.json'), 'file.json', 'file1.yml']
        ];
    }
}
