<?php
declare(strict_types=1);

namespace Differ\Differ;

use Exception;
use function Differ\Format\format;
use function Funct\Collection\sortBy;
use function Funct\Collection\union;

/**
 * @throws Exception Стандартное исключение.
 */
function genDiff(array $dataFile1, array $dataFile2, string $format = 'stylish'): string
{
    $tree = buildDiff($dataFile1, $dataFile2);
    return format($tree, $format);
}

function buildDiff(array $dataOne, array $dataTwo): array
{
    $keysFirst = array_keys($dataOne);
    $keysLast = array_keys($dataTwo);
    $allKeys = union($keysFirst, $keysLast);
    $allKeysSorted = sortBy($allKeys, function ($num) {
        return $num;
    });

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
                'value_first_data' => $valueTwo
            ];
        }


        return [
            'name' => $key,
            'type' => 'no_change',
            'value' => $valueOne,
        ];
    }, $allKeysSorted);
    return array_values($result) ?? [];
}
