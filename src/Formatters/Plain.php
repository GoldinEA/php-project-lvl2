<?php
namespace Differ\Formatters\Plain;

use const Differ\Format\BOOL_ARRAY;

function format(array $tree, int $step = 1, array $structureName = []): string
{
    $formattedTree = array_map(function ($treeElement) use ($step, $structureName) {
        $structureName[$step] = $treeElement['name'];
        $name = !empty($structureName)
            ? implode('.', $structureName)
            : $treeElement['name'];
        $status = getPlainStatus($treeElement['type']);

        if ($treeElement['multilevel'] === true && $treeElement['multivalued'] === true) {
            $strAdded = is_array($treeElement['value_added'])
                ? "[complex value]"
                : convertToString($treeElement['value_added']);
            $strDeleted = is_array($treeElement['value_deleted'])
                ? "[complex value]"
                : convertToString($treeElement['value_deleted']);
            return "Property '$name' was updated. From $strDeleted to '$strAdded'";
        }

        if ($treeElement['multilevel'] === true && $treeElement['multivalued'] !== true) {
            return "Property '$name' was $status with value: [complex value]"
                . PHP_EOL
                . format($treeElement['value'], $step + 1, $structureName);
        }

        switch ($treeElement['type']) {
            case 'changed':
                $deleted = convertToString($treeElement['value_deleted']);
                $added = convertToString($treeElement['value_added']);
                return "Property '$name' was $status. From '$deleted' to '$added'";
            case 'deleted':
                return "Property '$name' was $status.";
            case 'added':
                return "Property '$name' was $status with value: " . convertToString($treeElement['value']);
        }
    }, $tree);
    $clearData = clearResult($formattedTree);
    return implode(PHP_EOL, $clearData);
}

function getPlainStatus(string $typeElement): string
{
    return match ($typeElement) {
        'deleted' => 'removed',
        'added' => 'added',
        'changed' => 'updated',
        default => 'no_change'
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
