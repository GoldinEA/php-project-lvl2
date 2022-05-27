<?php

declare(strict_types=1);

namespace Differ\Formatters\Stylish;

function formatString(string $name, string $value, int $depth, string $char): string
{
    $spaces = substr(createSpaces($depth), 2);
    return sprintf("%s%s %s: %s", $spaces, $char, $name, $value);
}

function createStylishValue(mixed $value, int $depth): string
{
    switch (gettype($value)) {
        case 'array':
            $keys = array_keys($value);
            $values = array_values($value);
            return sprintf(
                "{\n%s\n%s}",
                implode(
                    "\n",
                    array_map(function ($key, $value) use ($depth) {
                        $spaces = createSpaces($depth);
                        $convertedValue = createStylishValue($value, $depth + 1);
                        return "   $spaces $key: $convertedValue";
                    }, $keys, $values)
                ),
                createSpaces($depth)
            );
        case 'boolean':
            return $value === true ? 'true' : 'false';
        case 'NULL':
            return 'null';
        default:
            return (string)$value;
    }
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
            $elementType = (string)$treeElement['type'];
            return match ($elementType) {
                'parent' => formatString(
                    $elementName,
                    format($treeElement['child'], $depth + 1),
                    $depth,
                    ' '
                ),
                'changed' => createChangedTreeElement($elementName, $treeElement, $depth),
                'added' => formatString(
                    $elementName,
                    createStylishValue($treeElement['value'], $depth),
                    $depth,
                    '+'
                ),
                'deleted' => formatString(
                    $elementName,
                    createStylishValue($treeElement['value'], $depth),
                    $depth,
                    '-'
                ),
                'no_change' => formatString(
                    $elementName,
                    createStylishValue($treeElement['value'], $depth),
                    $depth,
                    ' '
                ),
            };
        },
        $tree
    );
    $spacesFinal = $depth === 1 ? '' : createSpaces($depth - 1);
    return sprintf("{\n%s\n%s}", implode("\n", $formattedTree), $spacesFinal);
}

function createChangedTreeElement(string $elementName, array $treeElement, int $depth): string
{
    $partStringOne = formatString(
        $elementName,
        createStylishValue($treeElement['value_two_data'], $depth),
        $depth,
        '-'
    );
    $partStringTwo = formatString(
        $elementName,
        createStylishValue($treeElement['value_one_data'], $depth),
        $depth,
        '+'
    );
    return $partStringOne . "\n" . $partStringTwo;
}
