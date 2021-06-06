<?php
declare(strict_types=1);

namespace Differ\Diff;

use function Funct\Object\toArray;

function genDiff(string $pathToFile1, string $pathToFile2): string
{
    if (!file_exists($pathToFile1) || !file_exists($pathToFile2)) {
        return false;
    }
    $file1 = (array)json_decode(file_get_contents($pathToFile1));
    $file2 = (array)json_decode(file_get_contents($pathToFile2));

    $intersect = array_intersect($file1, $file2);
    $diff1 = diffHandler(array_diff($file2, $intersect), '-');
    $diff2 = diffHandler(array_diff($file1, $intersect), '+');
    $arr = array_merge($intersect, $diff1, $diff2);
    return createResult($arr);
}


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