<?php

declare(strict_types=1);

namespace Differ\Formatters\Plain;

const BOOL_ARRAY = [true => 'true', false => 'false'];

function format(array $tree, int $depth = 1, array $structureName = []): string
{
    $formattedTree = array_map(function ($treeElement) use ($depth, $structureName) {
        $newLevelName = [
            $depth => $treeElement['name']
        ];
        $allLevels = array_merge($structureName, $newLevelName);
        $name = count($allLevels) > 1
            ? implode('.', $allLevels)
            : $treeElement['name'];
        $status = getPlainStatus($treeElement['type']);

        switch ($treeElement['type']) {
            case 'changed':
                if (is_array($treeElement['value_two_data']) || is_array($treeElement['value_one_data'])) {
                    $strAdded = convertToString($treeElement['value_two_data']);
                    $strDeleted = convertToString($treeElement['value_one_data']);
                    return "Property '$name' was updated. From $strAdded to $strDeleted";
                }
                $deleted = convertToString($treeElement['value_one_data']);
                $added = convertToString($treeElement['value_two_data']);
                return "Property '$name' was $status. From $added to $deleted";
            case 'deleted':
                if (is_array($treeElement['value'])) {
                    return "Property '$name' was $status" ;
                }
                return "Property '$name' was $status";
            case 'added':
                if (is_array($treeElement['value'])) {
                    $startLine = " with value: [complex value]";
                    return "Property '$name' was $status" . $startLine;
                }
                return "Property '$name' was $status with value: " . convertToString($treeElement['value']);
            case 'parent':
                return format($treeElement['child'], $depth + 1, $allLevels);
        }
    }, $tree);
    return implode(PHP_EOL, $formattedTree);
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

function convertToString(mixed $value): string
{
    return match (true) {
        is_bool($value) => BOOL_ARRAY[$value],
        $value === null => 'null',
        is_array($value) => "[complex value]",
        default => "'" . (string)$value . "'",
    };
}
