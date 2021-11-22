<?php

namespace Differ\Formatters\Stylish;

use const Differ\Format\BOOL_ARRAY;

function createString(string $name, string $value, int $step, string $char): string
{
    return substr(str_repeat("    ", $step), 2) . "$char $name: " . $value;
}

function createChar(string $type): string
{
    return match ($type) {
        'deleted' => '-',
        'added' => '+',
        default => ' '
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
    $formattedTree = array_map(function ($treeElement) use ($step, $spaces) {

        if ($treeElement['multilevel'] === true && $treeElement['multivalued'] === true) {
            $strAdded = is_array($treeElement['value_last_file'])
                ? create($treeElement['value_last_file'], $step + 1)
                : convertToString($treeElement['value_last_file']);
            $strDeleted = is_array($treeElement['value_first_file'])
                ? create($treeElement['value_first_file'], $step + 1)
                : convertToString($treeElement['value_first_file']);
            return createString($treeElement['name'], $strDeleted, $step, '-') . PHP_EOL
                . createString($treeElement['name'], $strAdded, $step, '+');
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
                    convertToString($treeElement['value_first_file']),
                    $step,
                    '-'
                )
                    . PHP_EOL
                    . createString($treeElement['name'], convertToString($treeElement['value_last_file']), $step, '+');
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
    $spacesFinal = $step === 1 ? '' : substr(str_repeat("    ", $step), 4);
    return '{' . PHP_EOL . implode("\n", $formattedTree) . PHP_EOL  .$spacesFinal . '}';
}
