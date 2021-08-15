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
    $multiplicator = $step === 1 ? 4 : 2;
    $formattedTree = array_map(function ($treeElement) {
        if ($treeElement['type'] === 'no_change') {
            return "    {$treeElement['name']}: " . (string)$treeElement['value'] . PHP_EOL;
        } elseif($treeElement['type'] === 'changed') {
            return "   +{$treeElement['name']}: " . (string)$treeElement['value_added'] . PHP_EOL
                . "   -{$treeElement['name']}: ". (string)$treeElement['value_deleted'] . PHP_EOL;
        } elseif($treeElement['type'] === 'deleted') {
            return "   -{$treeElement['name']}: " . (string)$treeElement['value'] . PHP_EOL;
        } elseif ($treeElement['type'] === 'added') {
            return "   +{$treeElement['name']}: " . (string)$treeElement['value'] . PHP_EOL;
        }
    }, $tree);
    return '{' . PHP_EOL . implode('', $formattedTree) . '}';
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
