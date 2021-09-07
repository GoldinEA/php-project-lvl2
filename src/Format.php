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

function defaultFormat(array $tree, int $step = 1, string $name = ''): string
{
    $multiplicator = $step === 1 ? 4 : 2;
    $formattedTree = array_map(function ($treeElement) use ($step) {
        $multiplicator = $step === 1 ? 4 : 2;
        if (is_array($treeElement['value'] ?? false) ) {
            return str_repeat(" ", $multiplicator * $step) . "{$treeElement['name']}: " . defaultFormat($treeElement['value'], $step + 1);
        } elseif (is_array($treeElement['value_added'] ?? false) || is_array($treeElement['value_deleted'] ?? false)) {
            $itemAdded = is_array($treeElement['value_added'])
                ? str_repeat(" ", $multiplicator * $step) . "+{$treeElement['name']}: " . defaultFormat($treeElement['value_added'], $step + 1)
                : str_repeat(" ", $multiplicator  * $step) . "+{$treeElement['name']}: " . $treeElement['value_added'];
            $itemDeleted = is_array($treeElement['value_deleted'])
                ? str_repeat(" ", $multiplicator * $step) . "-{$treeElement['name']}: " . defaultFormat($treeElement['value_deleted'], $step + 1)
                : str_repeat(" ", $multiplicator * $step) . "-{$treeElement['name']}: " . $treeElement['value_deleted'];
            return $itemAdded . $itemDeleted;
        } elseif ($treeElement['type'] === 'no_change') {
            return str_repeat(" ", $multiplicator * $step) . "{$treeElement['name']}: " . $treeElement['value'];
        } elseif ($treeElement['type'] === 'changed') {
            return str_repeat(" ", $multiplicator  * $step) . "+{$treeElement['name']}: " . $treeElement['value_added']
                . str_repeat(" ", $multiplicator * $step) . "-{$treeElement['name']}: " . $treeElement['value_deleted'];
        } elseif ($treeElement['type'] === 'deleted') {
            return str_repeat(" ", $multiplicator * $step) . "-{$treeElement['name']}: " . $treeElement['value'];
        } elseif ($treeElement['type'] === 'added') {
            return str_repeat(" ", $multiplicator * $step) . "+{$treeElement['name']}: " . $treeElement['value'];
        }
    }, $tree);
    return '{' . implode(PHP_EOL, $formattedTree) . '}';
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
