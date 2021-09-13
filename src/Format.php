<?php
declare(strict_types=1);

namespace Differ\Format;

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
                $strAdded = is_array($treeElement['value_added']) ? defaultFormat($treeElement['value_added'], $step + 1) : $treeElement['value_added'];
                $strDeleted = is_array($treeElement['value_deleted']) ? defaultFormat($treeElement['value_deleted'], $step + 1) : $treeElement['value_deleted'];
                return str_repeat(" ", $multiplicator * $step) . "+ {$treeElement['name']}: " . $strAdded. $spaces
                    . str_repeat(" ", $multiplicator * $step) . "- {$treeElement['name']}: " . $strDeleted;
            } else {
                return str_repeat(" ", $multiplicator * $step) . "{$treeElement['name']}: " . defaultFormat($treeElement['value'], $step + 1);
            }
        } else {
            switch ($treeElement['type']) {
                case 'no_change':
                    return str_repeat(" ", $multiplicator * $step) . "{$treeElement['name']}: " . $treeElement['value'];
                case 'changed':
                    return str_repeat(" ", $multiplicator * $step) . "+ {$treeElement['name']}: " . $treeElement['value_added']. $spaces
                        . str_repeat(" ", $multiplicator * $step) . "- {$treeElement['name']}: " . $treeElement['value_deleted'];
                case 'deleted':
                    return str_repeat(" ", $multiplicator * $step) . "- {$treeElement['name']}: " . $treeElement['value'];
                case 'added':
                    return str_repeat(" ", $multiplicator * $step) . "+ {$treeElement['name']}: " . $treeElement['value'];
            }
        }
    }, $tree);

    return '{' . $spaces . implode($spaces, $formattedTree) . $spaces . '}';
}

function createBodyRequest(array $data, int $step = 1): string
{
    $result = [];
    foreach ($data as $index => $item) {
        if (is_array($item)) {
            $result[] = createBodyRequest($item, $step + 1);
        } else {
            $item = $item === false ? 'false' : $item;
            $result[] = str_repeat(" ", 4 * $step) . "$index: $item,";
        }
    }
    return implode(PHP_EOL, $result);
}
