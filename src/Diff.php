<?php
declare(strict_types=1);

namespace Differ\Diff;

use Exception;
use Symfony\Component\Yaml\Yaml;
use function Differ\Format\diffHandler;
use function Differ\Format\createResult;
use function Funct\Collection\flatten;


/**
 * @throws Exception Стандартное исключение.
 */
function genDiff(string $pathToFile1, string $pathToFile2, string $format): string
{
    $dataFile1 = getFileData($pathToFile1);
    $dataFile2 = getFileData($pathToFile2);
    $tree = createTree($dataFile1, $dataFile2)
    return createResult($tree, $format);
}


/**
 * @throws Exception Стандартное исключение.
 */
function getFileData(string $pathToFile): array
{
    if (!file_exists($pathToFile)) {
        throw new Exception("File $pathToFile is not found.");
    }

    $path = new \SplFileInfo($pathToFile);
    $format = $path->getExtension();

    return match ($format) {
        'yaml', 'yml' => getYamlInfo($pathToFile),
        'json' => getJsonInfo($pathToFile),
        default => throw new Exception("Format file $format not found."),
    };

}

function getJsonInfo(string $pathToFile): array
{
    return json_decode(file_get_contents($pathToFile), true) ?? [];
}

function getYamlInfo(string $pathToFile): array
{
    return Yaml::parseFile($pathToFile) ?? [];
}

function createTree(array $dataFirstFile, array $dataLastFile): array
{
    $keysFirst = array_keys($dataFirstFile);
    $keysLast = array_keys($dataLastFile);
    $allKeys = array_unique(array_merge($keysFirst, $keysLast));
    $result = array_map(function ($key) use ($dataFirstFile, $dataLastFile) {
        if (array_key_exists($key, $dataFirstFile) && array_key_exists($key, $dataLastFile)) {
            return $dataFirstFile[$key] === $dataLastFile[$key]
                ? ['name' => $key, 'type' => 'no_change', 'value' => $dataLastFile[$key]]
                : ['name' => $key, 'type' => 'changed', 'value_added' => $dataLastFile[$key], 'value_deleted' => $dataFirstFile[$key]];
        } else {
            return array_key_exists($key, $dataFirstFile)
                ? ['name' => $key, 'type' => 'deleted', 'value' => $dataFirstFile[$key]]
                : ['name' => $key, 'type' => 'added', 'value' => $dataLastFile[$key]];
        }
    }, $allKeys);
    return array_values($result) ?? [];
}
