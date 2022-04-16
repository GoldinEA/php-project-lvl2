<?php

declare(strict_types=1);

namespace Differ\Parsers;

use Exception;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

/**
 * @throws Exception Стандартное исключение.
 */
function getFileData(string $pathToFile): array
{
    if (!file_exists($pathToFile)) {
        throw new Exception("File $pathToFile is not found.");
    }

    $path = new SplFileInfo($pathToFile);
    $format = $path->getExtension();

    return match ($format) {
        'yaml', 'yml' => Yaml::parseFile($pathToFile) ?? [],
        'json' => json_decode(file_get_contents($pathToFile) ?? '', true) ?? [],
        default => throw new Exception("Format file $format not found."),
    };
}
