<?php

declare(strict_types=1);

namespace Tests;

use Exception;
use JetBrains\PhpStorm\ArrayShape;
use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

require_once __DIR__ . '/../vendor/autoload.php';

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

    #[ArrayShape(['stylish format json' => "string[]", 'stylish format yaml' => "string[]", 'stylish format combo' => "string[]", 'plain format json' => "string[]", 'plain format yaml' => "string[]", 'plain format combo' => "string[]", 'json format json' => "string[]", 'json format yaml' => "string[]", 'json format combo' => "string[]"])]
    public function filesProvider(): array
    {
        return [
            'stylish format json' => ['format' => 'stylish', 'expected' => 'result.stylish', 'file1' => 'file.json', 'file2' => 'file1.json'],
            'stylish format yaml' => ['stylish', 'expected' => 'result.stylish', 'file1' => 'file.yml', 'file2' => 'file1.yml'],
            'stylish format combo' => ['stylish', 'expected' => 'result.stylish', 'file1' => 'file.json', 'file2' => 'file1.yml'],
            'plain format json' => ['format' => 'plain', 'result.plain', 'file1' => 'file.json', 'file2' => 'file1.json'],
            'plain format yaml' => ['format' => 'plain', 'result.plain', 'file1' => 'file.yml', 'file2' => 'file1.yml'],
            'plain format combo' => ['format' => 'plain', 'result.plain', 'file1' => 'file.json', 'file2' => 'file1.yml'],
            'json format json' => ['format' => 'json', 'result.json', 'file1' => 'file.json', 'file2' => 'file1.json'],
            'json format yaml' => ['format' => 'json', 'result.json', 'file1' => 'file.yml', 'file2' => 'file1.yml'],
            'json format combo' => ['format' => 'json', 'result.json', 'file1' => 'file.json', 'file2' => 'file1.yml']
        ];
    }
    
    private function createFilePath(string $fileName): string
    {
        return realpath(implode(DIRECTORY_SEPARATOR, [__DIR__, 'fixtures', $fileName]));
    }
}
