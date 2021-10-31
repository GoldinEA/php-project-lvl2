<?php
namespace Differ\Formatters\Json;

use function Differ\Format\createChar;
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
    return str_repeat(" ", $repeat) . '"' . $name . '":' . $value;
}

function convertToString(mixed $value): string
{
    return match (true) {
        $value === true, $value === false => BOOL_ARRAY[$value],
        $value === null => 'null',
        default => (string)$value,
    };
}

function format(array $tree, string $type, int $step = 1): string
{
    $multiplicator = $step === 1 ? 0 : 2;
    $spaces = "\r" . PHP_EOL . str_repeat(" ", $multiplicator * $step);
    $formattedTree = array_map(function ($treeElement) use ($step, $spaces, $type) {

        if ($treeElement['multilevel'] === true && $treeElement['multivalued'] === true) {
            $strAdded = is_array($treeElement['value_added']) ? format($treeElement['value_added'], $type, $step + 1) : convertToString($treeElement['value_added']);
            $strDeleted = is_array($treeElement['value_deleted']) ? format($treeElement['value_deleted'], $type, $step + 1) : convertToString($treeElement['value_deleted']);
            return createString($treeElement['name'], $strDeleted, $step + 1, '-', $type) . $spaces
                . createString($treeElement['name'], $strAdded, $step + 1, '+', $type);
        }

        if ($treeElement['multilevel'] === true && $treeElement['multivalued'] !== true) {
            $char = \Differ\Formatters\Stylish\createChar($treeElement['type']);
            return createString(
                $treeElement['name'],
                format($treeElement['value'], $type, $step + 1),
                $step,
                $char,
                $type
            );
        }

        switch ($treeElement['type']) {
            case 'changed':
                return createString(
                        $treeElement['name'],
                        convertToString($treeElement['value_deleted']),
                        $step,
                        '-',
                        $type
                    )
                    . $spaces .
                    createString($treeElement['name'], convertToString($treeElement['value_added']), $step, '+', $type);
            case 'deleted' || 'added' || 'no_change':
                $char = \Differ\Formatters\Stylish\createChar($treeElement['type']);
                return createString(
                    $treeElement['name'],
                    convertToString($treeElement['value']),
                    $step,
                    $char,
                    $type
                );
        }
    }, $tree);
    $clearData = clearResult($formattedTree);
    return '{' . $spaces . implode($spaces, $clearData) . $spaces . '}';
}
