<?php
declare(strict_types=1);

namespace Differ\Formatters\Stylish;

use Exception;
use JetBrains\PhpStorm\Pure;
use const Differ\Format\BOOL_ARRAY;

#[Pure]
function createString(string $name, string $value, int $depth, string $char): string
{
    return substr(createSpaces($depth), 2) . "$char $name: " . $value;
}

function convertToString(mixed $value, int $step): string
{
    return match (true) {
        is_array($value) => '{' . PHP_EOL . implode(
            PHP_EOL,
            createChild($value, $step)
        ) . PHP_EOL. createSpaces($step) . '}',
        is_bool($value) => BOOL_ARRAY[$value],
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
function format(array $tree, int $depth = 1): string
{
    $formattedTree = array_map(
        function ($treeElement) use ($depth) {
            $elementName = $treeElement['name'];
            $elementType = $treeElement['type'];
            switch ($elementType) {
                case 'parent':
                    $parentResult = createString(
                        $elementName,
                        format($treeElement['child'], $depth + 1),
                        $depth,
                        ' '
                    );
                    return str_repeat(' ', $depth) . $parentResult;
                case 'changed':
                    return createString(
                        $elementName,
                        convertToString($treeElement['value_two_data'], $depth),
                        $depth,
                        '-'
                    ) . PHP_EOL
                        . createString(
                            $elementName,
                            convertToString($treeElement['value_first_data'], $depth),
                            $depth,
                            '+'
                        );
                case 'deleted' || 'added' || 'no_change':
                    $char = createChar($elementType);
                    return createString(
                        $elementName,
                        convertToString($treeElement['value'], $depth),
                        $depth,
                        $char
                    );
                default:
                    throw new Exception("$elementType is undefined.");
            }
        },
        $tree
    );
    $spacesFinal = $depth === 1 ? '' : substr(createSpaces($depth), 4);
    return '{' . PHP_EOL . implode(PHP_EOL, $formattedTree) . PHP_EOL . $spacesFinal . '}';
}

function createChar(string $type): string
{
    return match ($type) {
        'deleted' => '-',
        'added' => '+',
        default => ' '
    };
}
