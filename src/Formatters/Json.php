<?php

declare(strict_types=1);

namespace Differ\Formatters\Json;

function format(array $tree): string
{
    return json_encode($tree);
}
