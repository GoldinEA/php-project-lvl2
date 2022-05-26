<?php

declare(strict_types=1);

namespace Differ\Parsers;

use Exception;
use Symfony\Component\Yaml\Yaml;

/**
 * @throws Exception Стандартное исключение.
 */
function parse(string $data, string $format): array
{
    return match ($format) {
        'yaml', 'yml' => Yaml::parse($data),
        'json' => json_decode($data, true),
        default => throw new Exception("Format data $format not found."),
    };
}
