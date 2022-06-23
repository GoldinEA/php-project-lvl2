<?php

declare(strict_types=1);

namespace Differ\Formatters\Stylish;

function formatString(string $name, string $value, int $depth, string $char): string
{
    $spaces = substr(createSpaces($depth), 2);
    return "$spaces$char $name: $value";
}

function formatValue(mixed $value, int $depth): string
{
    switch (gettype($value)) {
        case 'array':
            $keys = array_keys($value);
            $values = array_values($value);
            $body = implode(
                "\n",
                array_map(function ($key, $value) use ($depth) {
                    $spaces = createSpaces($depth);
                    $convertedValue = formatValue($value, $depth + 1);
                    return "   $spaces $key: $convertedValue";
                }, $keys, $values)
            );
            $spaces = createSpaces($depth);
            return "{\n$body\n$spaces}";
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

            switch ($elementType) {
                case 'parent':
                    return formatString(
                        $elementName,
                        format($treeElement['child'], $depth + 1),
                        $depth,
                        ' '
                    );
                case 'changed':
                    $partStringOne = formatString(
                        $elementName,
                        formatValue($treeElement['value_two_data'], $depth),
                        $depth,
                        '-'
                    );
                    $partStringTwo = formatString(
                        $elementName,
                        formatValue($treeElement['value_one_data'], $depth),
                        $depth,
                        '+'
                    );
                    return "$partStringOne\n$partStringTwo";
                case 'added':
                    return formatString(
                        $elementName,
                        formatValue($treeElement['value'], $depth),
                        $depth,
                        '+'
                    );
                case 'deleted':
                    return formatString(
                        $elementName,
                        formatValue($treeElement['value'], $depth),
                        $depth,
                        '-'
                    );
                case 'no_change':
                    return formatString(
                        $elementName,
                        formatValue($treeElement['value'], $depth),
                        $depth,
                        ' '
                    );
            }
        },
        $tree
    );
    $spacesFinal = $depth === 1 ? '' : createSpaces($depth - 1);
    $convertedTree = implode("\n", $formattedTree);
    return "{\n$convertedTree\n$spacesFinal}";
}
