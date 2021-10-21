<?php
declare(strict_types=1);

namespace Differ\Format;

const BOOL_ARRAY = [true => 'true', false => 'false'];

function createResult(array $diff, string $format): string
{
    if (empty($format) || $format === 'default' || $format === 'json') {
        return formatText($diff, $format);
    }

    if ($format === 'plain') {
        return plainFormat($diff, 1);
    }
    return '';
}

function clearResult(array $dataDefault): array
{
    $clearData = array_filter($dataDefault);
    return array_map(function ($elementTree) {
        return substr($elementTree, -1) === PHP_EOL ? substr($elementTree, 0, strlen($elementTree) - 1) : $elementTree;
    }, $clearData);
}

function createString(string $name, string $value, int $step, string $char = '', string $format = 'default'): string
{
    $multiplicator = $step === 1 ? 4 : 2;
    $name = $char === '' ? "$name: " : "$char $name: ";
    $repeat = $char === '' ? $multiplicator * $step : ($multiplicator * $step) - 2;
    return $format === 'default'
        ? str_repeat(" ", $repeat) . $name . $value
        : str_repeat(" ", $repeat) . '"' . $name . '":' . $value;
}

function createChar(string $type): string
{
    return match ($type) {
        'deleted' => '-',
        'added' => '+',
        default => ''
    };
}

function formatText(array $tree, string $type, int $step = 1): string
{
    $multiplicator = $step === 1 ? 0 : 2;
    $spaces = "\r" . PHP_EOL . str_repeat(" ", $multiplicator * $step);
    $formattedTree = array_map(function ($treeElement) use ($step, $spaces, $type) {

        if ($treeElement['multilevel'] === true && $treeElement['multivalued'] === true) {
            $strAdded = is_array($treeElement['value_added']) ? formatText($treeElement['value_added'], $type, $step + 1) : convertToString($treeElement['value_added']);
            $strDeleted = is_array($treeElement['value_deleted']) ? formatText($treeElement['value_deleted'], $type, $step + 1) : convertToString($treeElement['value_deleted']);
            return createString($treeElement['name'], $strDeleted, $step + 1, '-', $type) . $spaces
                . createString($treeElement['name'], $strAdded, $step + 1, '+', $type);
        }
        
        if ($treeElement['multilevel'] === true && $treeElement['multivalued'] !== true) {
            $char = createChar($treeElement['type']);
            return createString(
                $treeElement['name'],
                formatText($treeElement['value'], $type, $step + 1),
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
                $char = createChar($treeElement['type']);
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


function plainFormat(array $tree, int $step = 1, array $structureName = []): string
{
    $formattedTree = array_map(function ($treeElement) use ($step, $structureName) {
        $structureName[$step] = $treeElement['name'];
        $name = !empty($structureName)
            ? implode('.', $structureName)
            : $treeElement['name'];
        $status = getPlainStatus($treeElement['type']);

        if ($treeElement['multilevel'] === true && $treeElement['multivalued'] === true) {
            $strAdded = is_array($treeElement['value_added'])
                ? "[complex value]"
                : convertToString($treeElement['value_added']);
            $strDeleted = is_array($treeElement['value_deleted'])
                ? "[complex value]"
                : convertToString($treeElement['value_deleted']);
            return "Property '{$name}' was updated. From {$strDeleted} to '{$strAdded}'";
        }

        if ($treeElement['multilevel'] === true && $treeElement['multivalued'] !== true) {
            return "Property '{$name}' was {$status} with value: [complex value]"
                . PHP_EOL
                . plainFormat($treeElement['value'], $step + 1, $structureName);
        }

        switch ($treeElement['type']) {
            case 'changed':
                $deleted = convertToString($treeElement['value_deleted']);
                $added = convertToString($treeElement['value_added']);
                return "Property '{$name}' was {$status}. From '{$deleted}' to '{$added}'";
            case 'deleted':
                return "Property '{$name}' was {$status}.";
            case 'added':
                return "Property '{$name}' was {$status} with value: " . convertToString($treeElement['value']);
        }

    }, $tree);
    $clearData = clearResult($formattedTree);
    return implode(PHP_EOL, $clearData);
}

function getPlainStatus(string $typeElement): string
{
    return match ($typeElement) {
        'deleted' => 'removed',
        'added' => 'added',
        'changed' => 'updated',
        default => 'no_change'
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
