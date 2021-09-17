<?php
declare(strict_types=1);

namespace Differ\Format;

const BOOL_ARRAY = [true => 'true', false => 'false'];

function diffHandler(array $diff, string $char): array
{
    $result = [];
    foreach ($diff as $key => $item) {
        $result["{$char} $key"] = $item;
    }
    return $result;
}

function createResult(array $diff, string $format): string
{
    if (empty($format) || $format === 'default') {
        return defaultFormat($diff, 1);
    }
    return '';
}

function defaultFormat(array $tree, int $step = 1): string
{
    $multiplicator = $step === 1 ? 0 : 2;
    $spaces = "\r". PHP_EOL . str_repeat(" ", $multiplicator * $step);
    $formattedTree = array_map(function ($treeElement) use ($step, $spaces) {
        $multiplicator = $step === 1 ? 4 : 2;
        if ($treeElement['multilevel'] === true) {
            if ($treeElement['multivalued'] === true) {
                $strAdded = is_array($treeElement['value_added']) ? defaultFormat($treeElement['value_added'], $step + 1) : convertToString($treeElement['value_added']);
                $strDeleted = is_array($treeElement['value_deleted']) ? defaultFormat($treeElement['value_deleted'], $step + 1) : convertToString($treeElement['value_deleted']);
                return str_repeat(" ", ($multiplicator * $step) - 2) . "- {$treeElement['name']}: " . $strDeleted . $spaces
                    . str_repeat(" ", ($multiplicator * $step) - 2) . "+ {$treeElement['name']}: " . $strAdded;
            } else {
                if ($treeElement['type'] === 'deleted') {
                    return str_repeat(" ", ($multiplicator * $step) - 2) . "- {$treeElement['name']}: " . defaultFormat($treeElement['value'], $step + 1);
                } elseif ($treeElement['type'] === 'added') {
                    return str_repeat(" ", ($multiplicator * $step) - 2) . "+ {$treeElement['name']}: " . defaultFormat($treeElement['value'], $step + 1);
                } else {
                    return str_repeat(" ", $multiplicator * $step) . "{$treeElement['name']}: " . defaultFormat($treeElement['value'], $step + 1);
                }
            }
        } else {
            switch ($treeElement['type']) {
                case 'no_change':
                    return str_repeat(" ", $multiplicator * $step) . "{$treeElement['name']}: " . convertToString($treeElement['value']);
                case 'changed':
                    return str_repeat(" ", ($multiplicator * $step) - 2) . "- {$treeElement['name']}: " . convertToString($treeElement['value_deleted']). $spaces
                        . str_repeat(" ", ($multiplicator * $step) - 2) . "+ {$treeElement['name']}: " . convertToString($treeElement['value_added']);
                case 'deleted':
                    return str_repeat(" ", ($multiplicator * $step) - 2) . "- {$treeElement['name']}: " . convertToString($treeElement['value']);
                case 'added':
                    return str_repeat(" ", ($multiplicator * $step) - 2) . "+ {$treeElement['name']}: " . convertToString($treeElement['value']);
            }
        }
    }, $tree);

    return '{' . $spaces . implode($spaces, $formattedTree) . $spaces . '}';
}

function convertToString(mixed $value): string
{
    return match (true) {
        $value === true, $value === false => BOOL_ARRAY[$value],
        $value === null => 'null',
        default => (string)$value,
    };
}
