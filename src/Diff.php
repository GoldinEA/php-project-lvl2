<?php
declare(strict_types=1);

namespace Differ\Diff;

use Symfony\Component\Yaml\Yaml;

const FORMAT_FILES = [
    'yml',
    'yaml',
    'json'
];

function genDiff(string $pathToFile1, string $pathToFile2, string $format): string
{
    if (!file_exists($pathToFile1) || !file_exists($pathToFile2) || !in_array($format, FORMAT_FILES)) {
        return '';
    }

    $file1 = $format == 'yaml' || $format == 'yml' ? getYamlInfo($pathToFile1) : getJsonInfo($pathToFile1);
    $file2 = $format == 'yaml' || $format == 'yml' ? getYamlInfo($pathToFile2) : getJsonInfo($pathToFile2);

    $intersect = array_intersect($file1, $file2);
    $diff1 = diffHandler(array_diff($file2, $intersect), '-');
    $diff2 = diffHandler(array_diff($file1, $intersect), '+');
    $arr = array_merge($intersect, $diff1, $diff2);
    return createResult($arr);
}

function getJsonInfo(string $pathToFile): array
{
    return (array)json_decode(file_get_contents($pathToFile)) ?? [];
}

function getYamlInfo(string $pathToFile): array
{
    return Yaml::parseFile($pathToFile) ?? [];
}

function diffHandler(array $diff, string $char): array
{
    $result = [];
    foreach ($diff as $key => $item) {
        $result["{$char} $key"] = $item;
    }
    return $result;
}


function createResult(array $diff): string
{
    $result = [];
    $result[] = '{';
    foreach ($diff as $index => $item) {
        $item = $item === false ? 'false' : $item;
        $result[] = "    $index: $item,";
    }
    $result[array_key_last($result)] = substr($result[array_key_last($result)], 0, -1);
    $result[] = '}';
    return implode(PHP_EOL, $result);
}