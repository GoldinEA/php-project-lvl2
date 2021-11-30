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
            $added = is_array($treeElement['value_last_file']) ? array_values(format($treeElement['value_last_file'])) : convertToString($treeElement['value_last_file']);
            $deleted = is_array($treeElement['value_first_file']) ? array_values(format($treeElement['value_first_file'])) : convertToString($treeElement['value_first_file']);
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
                $result[createName($treeElement['name'], '-')] = convertToString($treeElement['value_first_file']);
                $result[createName($treeElement['name'], '+')] = convertToString($treeElement['value_last_file']);
                return $result;
            case 'deleted' || 'added' || 'no_change':
                $char = \Differ\Formatters\Stylish\createChar($treeElement['type']);
                $result[createName($treeElement['name'], $char)] = convertToString($treeElement['value']);
                return $result;
        }

    }, $tree);
    return array_values($formattedTree);
}
