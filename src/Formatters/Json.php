<?php

namespace Differ\Formatters\Json;

use function Differ\Format\createChar;
use const Differ\Format\BOOL_ARRAY;

function createName(string $name, string $char = ''): string
{
    return $char === '' ? "$name " : "$char $name ";
}

function convertToString(mixed $value): string
{
    return match (true) {
        $value === true, $value === false => BOOL_ARRAY[$value],
        $value === null => 'null',
        default => (string)$value,
    };
}

function format(array $tree): array
{
    $result = [];
    $formattedTree = array_map(function ($treeElement) use ($result) {

        if ($treeElement['multilevel'] === true && $treeElement['multivalued'] === true) {
            $added = is_array($treeElement['value_added']) ? array_values(format($treeElement['value_added'])) : convertToString($treeElement['value_added']);
            $deleted = is_array($treeElement['value_deleted']) ? array_values(format($treeElement['value_deleted'])) : convertToString($treeElement['value_deleted']);
            $result[createName($treeElement['name'], '-')] = $deleted;
            $result[createName($treeElement['name'], '+')] = $added;
            return $result;
        }

        if ($treeElement['multilevel'] === true && $treeElement['multivalued'] !== true) {
            $char = \Differ\Formatters\Stylish\createChar($treeElement['type']);
            $result[createName($treeElement['name'], $char)] = array_values(format($treeElement['value']));
            return $result;
        }

        switch ($treeElement['type']) {
            case 'changed':
                $result[createName($treeElement['name'], '-')] = convertToString($treeElement['value_deleted']);
                $result[createName($treeElement['name'], '+')] = convertToString($treeElement['value_added']);
                return $result;
            case 'deleted' || 'added' || 'no_change':
                $char = \Differ\Formatters\Stylish\createChar($treeElement['type']);
                $result[createName($treeElement['name'], $char)] = convertToString($treeElement['value']);
                return $result;
        }

    }, $tree);
    return array_values($formattedTree);
}
