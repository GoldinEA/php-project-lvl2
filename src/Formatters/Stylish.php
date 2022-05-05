<?php

declare(strict_types=1);

namespace Differ\Formatters\Stylish;

const BOOL_ARRAY = [true => 'true', false => 'false'];

function createString(string $name, string $value, int $depth, string $char): string
{
    return substr(createSpaces($depth), 2) . "$char $name: " . $value;
}

function convertToString(mixed $value, int $depth): string
{
    return match (true) {
        is_array($value) => '{' . "\n" . implode(
            "\n",
            createValue($value, $depth)
        ) . "\n" . createSpaces($depth) . '}',
        is_bool($value) => BOOL_ARRAY[$value],
        $value === null => 'null',
        default => (string)$value,
    };
}

function createValue(array $value, int $depth): array
{
    $keys = array_keys($value);
    $values = array_values($value);
    return array_map(function ($key, $value) use ($depth) {
        $spaces = createSpaces($depth);
        $convertedValue = convertToString($value, $depth + 1);
        return "   $spaces $key: $convertedValue";
    }, $keys, $values);
}

function createSpaces(int $depth): string
{
    return str_repeat("    ", $depth);
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
            return match ($elementType) {
                'parent' => createString(
                    $elementName,
                    format($treeElement['child'], $depth + 1),
                    $depth,
                    createChar($elementType)
                ),
                'changed' => createString(
                    $elementName,
                    convertToString($treeElement['value_two_data'], $depth),
                    $depth,
                    createChar('deleted')
                ) . "\n"
                    . createString(
                        $elementName,
                        convertToString($treeElement['value_one_data'], $depth),
                        $depth,
                        createChar('added')
                    ),
                default => createString(
                    $elementName,
                    convertToString($treeElement['value'], $depth),
                    $depth,
                    createChar($elementType)
                ),
            };
        },
        $tree
    );
    $spacesFinal = $depth === 1 ? '' : createSpaces($depth - 1);
    return '{' . "\n" . implode("\n", $formattedTree) . "\n" . $spacesFinal . '}';
}

function createChar(string $type): string
{
    return match ($type) {
        'deleted' => '-',
        'added' => '+',
        default => ' '
    };
}
