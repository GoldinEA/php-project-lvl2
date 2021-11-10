<?php

namespace Differ\Formatters\Stylish;

use const Differ\Format\BOOL_ARRAY;

function clearResult(array $dataDefault): array
{
    $clearData = array_filter($dataDefault);
    return array_map(function ($elementTree) {
        return substr($elementTree, -1) === PHP_EOL ? substr($elementTree, 0, strlen($elementTree) - 1) : $elementTree;
    }, $clearData);
}

function createString(string $name, string $value, int $step, string $char = ''): string
{
    $multiplicator = $step === 1 ? 4 : 2;
    $name = $char === '' ? "$name: " : "$char $name: ";
    $repeat = $char === '' ? $multiplicator * $step : ($multiplicator * $step) - 2;
    return str_repeat(" ", $repeat) . $name . $value;
}

function createChar(string $type): string
{
    return match ($type) {
        'deleted' => '-',
        'added' => '+',
        default => ''
    };
}

function convertToString(mixed $value): string
{
    return match (true) {
        $value === true, $value === false => BOOL_ARRAY[$value],
        $value === null => 'null',
        default => (string)$value,
    };
}

function create(array $tree, int $step = 1): string
{
    $multiplicator = $step === 1 ? 0 : 2;
    $spaces = PHP_EOL . str_repeat(" ", $multiplicator * $step);
    $formattedTree = array_map(function ($treeElement) use ($step, $spaces) {

        if ($treeElement['multilevel'] === true && $treeElement['multivalued'] === true) {
            $strAdded = is_array($treeElement['value_added'])
                ? create($treeElement['value_added'], $step + 1)
                : convertToString($treeElement['value_added']);
            $strDeleted = is_array($treeElement['value_deleted'])
                ? create($treeElement['value_deleted'], $step + 1)
                : convertToString($treeElement['value_deleted']);
            return createString($treeElement['name'], $strDeleted, $step + 1, '-') . $spaces
                . createString($treeElement['name'], $strAdded, $step + 1, '+');
        }

        if ($treeElement['multilevel'] === true && $treeElement['multivalued'] !== true) {
            $char = createChar($treeElement['type']);
            return createString(
                $treeElement['name'],
                create($treeElement['value'], $step + 1),
                $step,
                $char
            );
        }

        switch ($treeElement['type']) {
            case 'changed':
                return createString(
                    $treeElement['name'],
                    convertToString($treeElement['value_deleted']),
                    $step,
                    '-'
                )
                    . $spaces .
                    createString($treeElement['name'], convertToString($treeElement['value_added']), $step, '+');
            case 'deleted' || 'added' || 'no_change':
                $char = createChar($treeElement['type']);
                return createString(
                    $treeElement['name'],
                    convertToString($treeElement['value']),
                    $step,
                    $char
                );
        }
    }, $tree);
    $clearData = clearResult($formattedTree);
    return '{' . $spaces . implode($spaces, $clearData) . $spaces . '}';
}
