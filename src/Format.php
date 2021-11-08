<?php
declare(strict_types=1);

namespace Differ\Format;

use Exception;

const BOOL_ARRAY = [true => 'true', false => 'false'];

/**
 * @throws Exception
 */
function format(array $diff, string $format): string
{
    if (empty($format) || $format === 'stylish') {
        return \Differ\Formatters\Stylish\create($diff);
    }

    if ($format === 'json') {
        return json_encode(\Differ\Formatters\Json\create($diff));
    }

    if ($format === 'plain') {
        return \Differ\Formatters\Plain\create($diff);
    }

    throw new Exception("$format is not found.");
}
