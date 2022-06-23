<?php

declare(strict_types=1);

namespace Differ\Formatters\Plain;

function format(array $tree, array $structureName = []): string
{
    $formattedTree = array_map(function ($treeElement) use ($structureName) {
        $allLevels = array_merge($structureName, [$treeElement['name']]);
        $name = implode('.', $allLevels);
        $status = getStatus($treeElement['type']);

        switch ($treeElement['type']) {
            case 'changed':
                $deleted = formatValue($treeElement['value_one_data']);
                $added = formatValue($treeElement['value_two_data']);
                return "Property '$name' was $status. From $added to $deleted";
            case 'deleted':
                return "Property '$name' was $status";
            case 'added':
                $stringValue = formatValue($treeElement['value']);
                return "Property '$name' was $status with value: $stringValue";
            case 'parent':
                return format($treeElement['child'], $allLevels);
        }
    }, $tree);
    return implode("\n", array_filter($formattedTree));
}

function getStatus(string $typeElement): string
{
    switch ($typeElement) {
        case 'deleted':
            return 'removed';
        case 'added':
            return 'added';
        case 'changed':
            return 'updated';
        default:
            return '';
    }
}

function formatValue(mixed $value): string
{
    switch (true) {
        case is_bool($value):
            return $value ? 'true' : 'false';
        case is_null($value):
            return 'null';
        case is_array($value):
            return "[complex value]";
        case is_string($value):
            return "'$value'";
        default:
            return (string)$value;
    }
}
