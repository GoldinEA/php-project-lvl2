<?php

declare(strict_types=1);

namespace Differ\Formatters\Plain;

const BOOL_ARRAY = [true => 'true', false => 'false'];

function format(array $tree, array $structureName = []): string
{
    $formattedTree = array_map(function ($treeElement) use ($structureName) {
        $allLevels = array_merge($structureName, [$treeElement['name']]);
        $name = implode('.', $allLevels);
        $status = getStatus($treeElement['type']);

        switch ($treeElement['type']) {
            case 'changed':
                $deleted = convertToString($treeElement['value_one_data']);
                $added = convertToString($treeElement['value_two_data']);
                return "Property '$name' was $status. From $added to $deleted";
            case 'deleted':
                return "Property '$name' was $status" ;
            case 'added':
                return "Property '$name' was $status with value: " . convertToString($treeElement['value']);
            case 'parent':
                return format($treeElement['child'], $allLevels);
        }
    }, $tree);
    return implode("\n", array_filter($formattedTree));
}

function getStatus(string $typeElement): string
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
        is_null($value) => 'null',
        is_array($value) => "[complex value]",
        is_string($value) => "'$value'",
        default => (string)$value
    };
}
