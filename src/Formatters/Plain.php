<?php

namespace Differ\Formatters\Plain;

use const Differ\Format\BOOL_ARRAY;

function create(array $tree, int $step = 1, array $structureName = []): string
{
    $formattedTree = array_map(function ($treeElement) use ($step, $structureName) {
        $structureName[$step] = $treeElement['name'];
        $name = !empty($structureName)
            ? implode('.', $structureName)
            : $treeElement['name'];
        $status = getPlainStatus($treeElement['type']);

        switch ($treeElement['type']) {
            case 'changed':
                if (is_array($treeElement['value_last_data']) || is_array($treeElement['value_first_data'])) {
                    $strAdded = is_array($treeElement['value_last_data'])
                        ? "[complex value]"
                        : createStringResult($treeElement['value_last_data']);
                    $strDeleted = is_array($treeElement['value_first_data'])
                        ? "[complex value]"
                        : createStringResult($treeElement['value_first_data']);
                    return "Property '$name' was updated. From $strDeleted to $strAdded";
                }
                $deleted = createStringResult($treeElement['value_first_data']);
                $added = createStringResult($treeElement['value_last_data']);
                return "Property '$name' was $status. From $deleted to $added";
            case 'deleted':
                if (is_array($treeElement['value']) ?? false) {
                    return "Property '$name' was $status" . PHP_EOL;
                }
                return "Property '$name' was $status";
            case 'added':
                if (is_array($treeElement['value']) ?? false) {
                    $startLine = " with value: [complex value]";
                    return "Property '$name' was $status" . $startLine . PHP_EOL;
                }
                return "Property '$name' was $status with value: " . createStringResult($treeElement['value']);
            case 'parent':
                return create($treeElement['child'], $step + 1);
        }
    }, $tree);
    $clearData = clearResult($formattedTree);
    return implode(PHP_EOL, $clearData);
}

function createStringResult(mixed $value): string
{
    return !is_null($value) && !is_bool($value) && !is_int($value) ? "'" . convertToString($value) . "'" : convertToString($value);
}

function getPlainStatus(string $typeElement): string
{
    return match ($typeElement) {
        'deleted' => 'removed',
        'added' => 'added',
        'changed' => 'updated',
        default => ''
    };
}

function clearResult(array $dataDefault): array
{
    $clearData = array_filter($dataDefault);
    return array_map(function ($elementTree) {
        return substr($elementTree, -1) === PHP_EOL ? substr($elementTree, 0, strlen($elementTree) - 1) : $elementTree;
    }, $clearData);
}

function convertToString(mixed $value): string
{
    return match (true) {
        $value === true, $value === false => BOOL_ARRAY[$value],
        $value === null => 'null',
        default => (string)$value,
    };
}
