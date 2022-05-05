<?php

declare(strict_types=1);

namespace Tests;

use Exception;
use JetBrains\PhpStorm\ArrayShape;
use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class DifferTest extends TestCase
{
    /**
     * @dataProvider filesProvider
     * @throws Exception
     */
    public function testGendiff(string $format, string $expected, string $file1, string $file2): void
    {
        $result = genDiff(
            $this->createFilePath($file1),
            $this->createFilePath($file2),
            $format
        );

        $this->assertFileExists(
            $this->createFilePath($expected),
            $result
        );
    }

    #[ArrayShape([
        'stylish format json' => "array",
        'stylish format yaml' => "array",
        'stylish format combo' => "array",
        'plain format json' => "array",
        'plain format yaml' => "array",
        'plain format combo' => "array",
        'json format json' => "array",
        'json format yaml' => "array",
        'json format combo' => "array"
    ])
    ]
    public function filesProvider(): array
    {
        return [
            'stylish format json' => [
                'format' => 'stylish',
                'expected' => 'result.stylish',
                'file1' => 'file.json',
                'file2' => 'file1.json'
            ],
            'stylish format yaml' => [
                'format' => 'stylish',
                'expected' => 'result.stylish',
                'file1' => 'file.yml',
                'file2' => 'file1.yml'
            ],
            'stylish format combo' => [
                'format' => 'stylish',
                'expected' => 'result.stylish',
                'file1' => 'file.json',
                'file2' => 'file1.yml'
            ],
            'plain format json' => [
                'format' => 'plain',
                'expected' => 'result.plain',
                'file1' => 'file.json',
                'file2' => 'file1.json'
            ],
            'plain format yaml' => [
                'format' => 'plain',
                'expected' => 'result.plain',
                'file1' => 'file.yml',
                'file2' => 'file1.yml'
            ],
            'plain format combo' => [
                'format' => 'plain',
                'expected' => 'result.plain',
                'file1' => 'file.json',
                'file2' => 'file1.yml'
            ],
            'json format json' => [
                'format' => 'json',
                'expected' => 'result.json',
                'file1' => 'file.json',
                'file2' => 'file1.json'
            ],
            'json format yaml' => [
                'format' => 'json',
                'expected' => 'result.json',
                'file1' => 'file.yml',
                'file2' => 'file1.yml'
            ],
            'json format combo' => [
                'format' => 'json',
                'expected' => 'result.json',
                'file1' => 'file.json',
                'file2' => 'file1.yml'
            ]
        ];
    }

    private function createFilePath(string $fileName): string
    {
        return realpath(
            implode(
                '/',
                [
                    __DIR__,
                    'fixtures',
                    $fileName
                ]
            )
        );
    }
}
