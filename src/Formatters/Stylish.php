<?php

namespace Differ\Formatters\Stylish;

use Exception;
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

function convertToString(mixed $value, int $step): string
{
    $spacesFinal = str_repeat("    ", $step);

    return match (true) {
        is_array($value) => '{' . PHP_EOL . implode(
            PHP_EOL,
            createChild($value, $step)
        ) . PHP_EOL. $spacesFinal . '}',
        $value === true, $value === false => BOOL_ARRAY[$value],
        $value === null => 'null',
        default => (string)$value,
    };
}

function createChild(array $value, int $step): array
{
    $keys = array_keys($value);
    $values = array_values($value);
    return array_map(function ($key, $value) use ($step) {
        $spaces = createSpaces($step);
        $convertedValue = convertToString($value, $step + 1);
        return "$spaces  $key: $convertedValue";
    }, $keys, $values);
}

function createSpaces(int $step): string
{
    return str_repeat("    ", $step);
}

/**
 * @throws \Exception
 */
function create(array $tree, int $step = 1): string
{
    $t= 1;
    $formattedTree = array_map(
        function ($treeElement) use ($step) {

            switch ($treeElement['type']) {
                case 'parent':
                    return createString(
                        $treeElement['name'],
                        create($treeElement['child'], $step + 1),
                        $step,
                        ''
                    );
                case 'changed':
                    return createString(
                        $treeElement['name'],
                        convertToString($treeElement['value_last_data'], $step),
                        $step,
                        '-'
                    ) . PHP_EOL
                        . createString(
                            $treeElement['name'],
                            convertToString($treeElement['value_first_data'], $step),
                            $step,
                            '+'
                        );
                case 'deleted' || 'added' || 'no_change':
                    $char = createChar($treeElement['type']);
                    return createString(
                        $treeElement['name'],
                        convertToString($treeElement['value'], $step),
                        $step,
                        $char
                    );
                default:
                    throw new Exception("{$treeElement['type']} is undefined.");
            }
        },
        $tree
    );
    $spacesFinal = $step === 1 ? '' : substr(str_repeat("    ", $step), 4);
    return '{' . PHP_EOL . implode("\n", $formattedTree) . PHP_EOL . $spacesFinal . '}';
}
