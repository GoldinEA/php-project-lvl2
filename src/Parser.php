<?php

declare(strict_types=1);

namespace Differ\Parsers;

use Exception;
use phpDocumentor\Reflection\Types\Boolean;
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
    $dataFile = file_get_contents($pathToFile);

    if (gettype($format) === 'boolean') {
        return [];
    }

    return match ($format) {
        'yaml', 'yml' => Yaml::parse($dataFile),
        'json' => json_decode($dataFile, true),
        default => throw new Exception("Format file $format not found."),
    };
}
