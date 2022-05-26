<?php

declare(strict_types=1);

namespace Differ\Format;

use Exception;

use function Differ\Formatters\Plain\format as formatPlain;
use function Differ\Formatters\Json\format as formatJson;
use function Differ\Formatters\Stylish\format as formatStylish;

/**
 * @throws Exception
 */
function format(array $diff, string $format): string
{
    return match ($format) {
        'stylish' => formatStylish($diff),
        'json' => formatJson($diff),
        'plain' => formatPlain($diff),
        default => throw new Exception("$format is not found."),
    };
}
