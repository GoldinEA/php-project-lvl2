<?php

declare(strict_types=1);

namespace Differ\Formatters\Plain;

use const Differ\Format\BOOL_ARRAY;

function format(array $tree, int $depth = 1, array $structureName = []): string
{
    $formattedTree = array_map(function ($treeElement) use ($depth, $structureName) {
        $newLevelName = [
            $depth => $treeElement['name']
        ];
        $allLevels = array_merge($structureName, $newLevelName);
        $name = count($allLevels) > 1
            ? implode('.', $allLevels)
            : $treeElement['name'];
        $status = getPlainStatus($treeElement['type']);

        switch ($treeElement['type']) {
            case 'changed':
                if (is_array($treeElement['value_two_data']) || is_array($treeElement['value_first_data'])) {
                    $strAdded = is_array($treeElement['value_two_data'])
                        ? "[complex value]"
                        : createStringResult($treeElement['value_two_data']);
                    $strDeleted = is_array($treeElement['value_first_data'])
                        ? "[complex value]"
                        : createStringResult($treeElement['value_first_data']);
                    return "Property '$name' was updated. From $strAdded to $strDeleted";
                }
                $deleted = createStringResult($treeElement['value_first_data']);
                $added = createStringResult($treeElement['value_two_data']);
                return "Property '$name' was $status. From $added to $deleted";
            case 'deleted':
                if (is_array($treeElement['value'])) {
                    return "Property '$name' was $status" . PHP_EOL;
                }
                return "Property '$name' was $status";
            case 'added':
                if (is_array($treeElement['value'])) {
                    $startLine = " with value: [complex value]";
                    return "Property '$name' was $status" . $startLine . PHP_EOL;
                }
                return "Property '$name' was $status with value: " . createStringResult($treeElement['value']);
            case 'parent':
                return format($treeElement['child'], $depth + 1, $allLevels);
        }
    }, $tree);
    $clearData = clearResult($formattedTree);
    return implode(PHP_EOL, $clearData);
}

function createStringResult(mixed $value): string
{
    return !is_null($value) && !is_bool($value) && !is_int($value)
        ? "'" . convertToString($value) . "'"
        : convertToString($value);
}

function getPlainStatus(string $typeElement): string
{
    return match ($typeElement) {
        'deleted' => 'removed',
        'added' => 'added',
        'changed' => 'updated',
        default => ''
    };
}

function clearResult(array $dataDefault): array
{
    $clearData = array_filter($dataDefault);
    return array_map(function ($elementTree) {
        return substr($elementTree, -1) === PHP_EOL ? substr($elementTree, 0, strlen($elementTree) - 1) : $elementTree;
    }, $clearData);
}

function convertToString(mixed $value): string
{
    return match (true) {
        $value === true, $value === false => BOOL_ARRAY[$value],
        $value === null => 'null',
        default => (string)$value,
    };
}


namespace App;

class Truncater
{
    public const OPTIONS = [
        'separator' => '...',
        'length' => 200,
    ];

    private int $optionLength = 200;

    private string $optionSeparator = '...';

    public function __construct(array $options = [])
    {
        $this->optionLength = $options['length'] ?? self::OPTIONS['length'];
        $this->optionSeparator = $options['separator'] ?? self::OPTIONS['separator'];
    }

    // BEGIN (write your solution here)
    public function truncate(string $text, array $options = []): string
    {
        $this->optionLength = $options['length'] ?? self::OPTIONS['length'];
        $this->optionSeparator = $options['separator'] ?? self::OPTIONS['separator'];

        if (strlen($text) > $this->optionLength) {
            echo strlen($text) .' > ' . $this->optionLength . PHP_EOL;
            return substr($text, 0, $this->optionLength) . $this->optionSeparator;
        }
        return $text;
    }
    // END
}
$truncater = new Truncater(['length' => 3]);
$actual = $truncater->truncate('one two');
