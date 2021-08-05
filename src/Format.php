<?php
declare(strict_types=1);

namespace Differ\Format;

function diffHandler(array $diff, string $char): array
{
    $result = [];
    foreach ($diff as $key => $item) {
        $result["{$char} $key"] = $item;
    }
    return $result;
}

function createResult(array $diff): string
{
    $result = '{' . PHP_EOL;
    $result .= createBodyRequest($diff, 1);
    $result .= PHP_EOL. '}';
    return $result;
}

function createBodyRequest(array $data, int $step = 1): string
{
    $result = [];
    foreach ($data as $index => $item) {
        if (is_array($item)) {
            $result[] = createBodyRequest($item, $step + 1);
        } else {
            $item = $item === false ? 'false' : $item;
            $result[] = str_repeat(" ", 4 * $step) . "$index: $item,";
        }
    }
    return implode(PHP_EOL, $result);
}
