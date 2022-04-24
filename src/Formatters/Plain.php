<?php

declare(strict_types=1);

namespace Differ\Formatters\Plain;

const BOOL_ARRAY = [true => 'true', false => 'false'];

function format(array $tree, string $structureName = ''): string
{
    $formattedTree = array_map(function ($treeElement) use ($structureName) {
        $name = $structureName !== ''
            ? $structureName . '.' . $treeElement['name']
            : $treeElement['name'];
        $status = getPlainStatus($treeElement['type']);

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
                return format($treeElement['child'], $name);
        }
    }, $tree);
    return implode(PHP_EOL, array_filter($formattedTree));
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
        is_string($value) => "'" . $value . "'",
        default => (string)$value
    };
}
