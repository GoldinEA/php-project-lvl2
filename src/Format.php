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
    } elseif ($format === 'json') {
        return jsonFormat($diff, 1);
    } elseif ($format === 'plain') {
        return plainFormat($diff, 1);
    }
    return '';
}

function defaultFormat(array $tree, int $step = 1): string
{
    $multiplicator = $step === 1 ? 0 : 2;
    $spaces = "\r" . PHP_EOL . str_repeat(" ", $multiplicator * $step);
    $formattedTree = array_map(function ($treeElement) use ($step, $spaces) {
        if ($treeElement['multilevel'] === true) {
            if ($treeElement['multivalued'] === true) {
                $strAdded = is_array($treeElement['value_added'])
                    ? defaultFormat($treeElement['value_added'], $step + 1)
                    : convertToString($treeElement['value_added']);
                $strDeleted = is_array($treeElement['value_deleted'])
                    ? defaultFormat($treeElement['value_deleted'], $step + 1)
                    : convertToString($treeElement['value_deleted']);
                return createString($treeElement['name'], $strDeleted, $step + 1, '-') . $spaces
                    . createString($treeElement['name'], $strAdded, $step + 1, '+');
            } else {
                $char = createChar($treeElement['type']);
                return createString(
                    $treeElement['name'],
                    defaultFormat($treeElement['value'], $step + 1),
                    $step,
                    $char
                );
            }
        } else {
            switch ($treeElement['type']) {
                case 'changed':
                    return createString(
                            $treeElement['name'],
                            convertToString($treeElement['value_deleted']),
                            $step,
                            '-'
                        ) . $spaces .
                        createString($treeElement['name'], convertToString($treeElement['value_added']), $step, '+');
                case 'deleted' || 'added':
                    $char = createChar($treeElement['type']);
                    return createString(
                        $treeElement['name'],
                        convertToString($treeElement['value']),
                        $step,
                        $char
                    );
            }
        }
    }, $tree);
    $clearData = resize($formattedTree);
    return '{' . $spaces . implode($spaces, $clearData) . $spaces . '}';
}

function resize(array $dataDefault): array
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

function jsonFormat(array $tree, int $step = 1): string
{
    $multiplicator = $step === 1 ? 0 : 2;
    $spaces = "\r" . PHP_EOL . str_repeat(" ", $multiplicator * $step);
    $formattedTree = array_map(function ($treeElement) use ($step, $spaces) {
        $multiplicator = $step === 1 ? 4 : 2;
        if ($treeElement['multilevel'] === true) {
            if ($treeElement['multivalued'] === true) {
                $strAdded = is_array($treeElement['value_added']) ? jsonFormat($treeElement['value_added'], $step + 1) : convertToString($treeElement['value_added']);
                $strDeleted = is_array($treeElement['value_deleted']) ? jsonFormat($treeElement['value_deleted'], $step + 1) : convertToString($treeElement['value_deleted']);
                return str_repeat(" ", ($multiplicator * $step) - 2) . '"-' . $treeElement['name'] . '":' . $strDeleted . $spaces
                    . str_repeat(" ", ($multiplicator * $step) - 2) . '"+' . $treeElement['name'] . '":' . $strAdded;
            } else {
                if ($treeElement['type'] === 'deleted') {
                    return str_repeat(" ", ($multiplicator * $step) - 2) . '"-' . $treeElement['name'] . '":' . jsonFormat($treeElement['value'], $step + 1);
                } elseif ($treeElement['type'] === 'added') {
                    return str_repeat(" ", ($multiplicator * $step) - 2) . '"+' . $treeElement['name'] . '":' . jsonFormat($treeElement['value'], $step + 1);
                } else {
                    return str_repeat(" ", $multiplicator * $step) . '"' . $treeElement['name'] . '":' . jsonFormat($treeElement['value'], $step + 1);
                }
            }
        } else {
            switch ($treeElement['type']) {
                case 'no_change':
                    return str_repeat(" ", $multiplicator * $step) . '"' . $treeElement['name'] . '":' . convertToString($treeElement['value']);
                case 'changed':
                    return str_repeat(" ", ($multiplicator * $step) - 2) . '"-' . $treeElement['name'] . '":' . convertToString($treeElement['value_deleted']) . $spaces
                        . str_repeat(" ", ($multiplicator * $step) - 2) . '"+' . $treeElement['name'] . '":' . convertToString($treeElement['value_added']);
                case 'deleted':
                    return str_repeat(" ", ($multiplicator * $step) - 2) . '"-' . $treeElement['name'] . '":' . convertToString($treeElement['value']);
                case 'added':
                    return str_repeat(" ", ($multiplicator * $step) - 2) . '"+' . $treeElement['name'] . '":' . convertToString($treeElement['value']);
            }
        }
    }, $tree);
    $clearData = resize($formattedTree);
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
        if ($treeElement['multilevel'] === true) {
            if ($treeElement['multivalued'] === true) {
                $strAdded = is_array($treeElement['value_added'])
                    ? "[complex value]"
                    : convertToString($treeElement['value_added']);
                $strDeleted = is_array($treeElement['value_deleted'])
                    ? "[complex value]"
                    : convertToString($treeElement['value_deleted']);
                return "Property '{$name}' was updated. From {$strDeleted} to '{$strAdded}'";
            } else {
                $spaces = $step === 1 ? '' : PHP_EOL;
                return "Property '{$name}' was {$status} with value: [complex value]"
                    . PHP_EOL
                    . plainFormat($treeElement['value'], $step + 1, $structureName);
            }
        } else {
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
        }
    }, $tree);
    $clearData = resize($formattedTree);
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
