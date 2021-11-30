<?php
declare(strict_types=1);

namespace Differ\Format;

use Exception;
use function Differ\Formatters\Plain\create;

const BOOL_ARRAY = [true => 'true', false => 'false'];

/**
 * @throws Exception
 */
function format(array $diff, string $format): string
{
    if ($format === 'stylish') {
        return \Differ\Formatters\Stylish\create($diff);
    }

    if ($format === 'json') {
        return json_encode(\Differ\Formatters\Json\format($diff));
    }

    if ($format === 'plain') {
        return create($diff);
    }

    throw new Exception("$format is not found.");
}
