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

        if ($treeElement['multilevel'] === true && $treeElement['multivalued'] === true && $status !== '') {
            $strAdded = is_array($treeElement['value_last_file'])
                ? "[complex value]"
                : createStringResult($treeElement['value_last_file']);
            $strDeleted = is_array($treeElement['value_first_file'])
                ? "[complex value]"
                : createStringResult($treeElement['value_first_file']);
            return "Property '$name' was updated. From $strDeleted to $strAdded";
        }

        if ($treeElement['multilevel'] === true && $treeElement['multivalued'] !== true && $status !== '') {
            $baseLine = create($treeElement['value'], $step + 1, $structureName);
            $startLine = $status === 'removed'
                ? ""
                : " with value: [complex value]";
            return $status !== 'updated'
                ? "Property '$name' was $status" . $startLine . PHP_EOL . $baseLine
                : $baseLine;
        }
        if ($status !== '') {
            switch ($treeElement['type']) {
                case 'changed':
                    $deleted = createStringResult($treeElement['value_first_file']);
                    $added = createStringResult($treeElement['value_last_file']);
                    return "Property '$name' was $status. From $deleted to $added";
                case 'deleted':
                    return "Property '$name' was $status";
                case 'added':
                    return "Property '$name' was $status with value: " . createStringResult($treeElement['value']);
            }
        }
    }, $tree);
    $clearData = clearResult($formattedTree);
    return implode(PHP_EOL, $clearData);
}

function createStringResult(mixed $value): string
{
    return !is_null($value) && !is_bool($value) ? "'" . convertToString($value) . "'" : convertToString($value);
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
