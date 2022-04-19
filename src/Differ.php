<?php

declare(strict_types=1);

namespace Differ\Differ;

use Exception;

use function Differ\Format\format;
use function Differ\Parsers\getFileData;
use function Functional\sort;

/**
 * @throws Exception Стандартное исключение.
 */
function genDiff(string $directory1, string $directory2, string $format = 'stylish'): string
{

    try {
        $fileData1 = getFileData($directory1);
    } catch (Exception $e) {
        throw new Exception("$directory1 nothing file in directory.");
    }

    try {
        $fileData2 = getFileData($directory2);
    } catch (Exception $e) {
        throw new Exception("$directory2 nothing file in directory.");
    }

    $tree = buildDiff($fileData1, $fileData2);
    return format($tree, $format);
}

function buildDiff(array $dataOne, array $dataTwo): array
{
    $keysFirst = array_keys($dataOne);
    $keysLast = array_keys($dataTwo);
    $allKeys = array_unique(array_merge($keysFirst, $keysLast));
    $allKeysSorted = sort(
        $allKeys,
        fn($left, $right) => $left <=> $right,
        true
    );

    $result = array_map(function ($key) use ($dataOne, $dataTwo) {
        $valueOne = $dataOne[$key] ?? null;
        $valueTwo = $dataTwo[$key] ?? null;

        if (!array_key_exists($key, $dataOne)) {
            return [
                'name' => $key,
                'type' => 'added',
                'value' => $valueTwo,
            ];
        }

        if (!array_key_exists($key, $dataTwo)) {
            return [
                'name' => $key,
                'type' => 'deleted',
                'value' => $valueOne,
            ];
        }

        if (is_array($valueTwo) && is_array($valueOne)) {
            return [
                'name' => $key,
                'type' => 'parent',
                'child' => buildDiff($valueOne, $valueTwo),
            ];
        }

        if ($valueTwo !== $valueOne) {
            return [
                'name' => $key,
                'type' => 'changed',
                'value_two_data' => $valueOne,
                'value_one_data' => $valueTwo
            ];
        }


        return [
            'name' => $key,
            'type' => 'no_change',
            'value' => $valueOne,
        ];
    }, $allKeysSorted);
    return array_values($result);
}
