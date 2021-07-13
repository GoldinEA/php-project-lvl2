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
    $result = [];
    $result[] = '{';
    foreach ($diff as $index => $item) {
        $item = $item === false ? 'false' : $item;
        $result[] = "    $index: $item,";
    }
    $result[array_key_last($result)] = substr($result[array_key_last($result)], 0, -1);
    $result[] = '}';
    return implode(PHP_EOL, $result);
}

function createBodyRequest(array $data) {
    $result = [];
    foreach ($data as $index => $item) {
        if (is_array($item)) {
            $result[] = createBodyRequest($item);
        } else {
            $item = $item === false ? 'false' : $item;
            $result[] = "    $index: $item,";
        }
        $item = $item === false ? 'false' : $item;
        $result[] = "    $index: $item,";
    }
    return $result;
}
