<?php
declare(strict_types=1);

namespace Differ\Differ;

use Exception;
use Symfony\Component\Yaml\Yaml;
use function Differ\Format\createResult;


/**
 * @throws Exception Стандартное исключение.
 */
function genDiff(string $pathToFile1, string $pathToFile2, string $format): string
{
    $dataFile1 = getFileData($pathToFile1);
    $dataFile2 = getFileData($pathToFile2);
    $tree = createTree($dataFile1, $dataFile2);
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
        if (!array_key_exists($key, $dataFirstFile) || !array_key_exists($key, $dataLastFile)) {
            $valueFirstFile = array_key_exists($key, $dataFirstFile) ? createValueTree($dataFirstFile[$key]) : '';
            $valueLastFile = array_key_exists($key, $dataLastFile) ? createValueTree($dataLastFile[$key]) : '';
            return array_key_exists($key, $dataFirstFile)
                ? [
                    'name' => $key,
                    'multivalued' => false,
                    'type' => 'deleted',
                    'value' => $valueFirstFile['value'],
                    'multilevel' => is_array($valueFirstFile['value'])
                ]
                : [
                    'name' => $key,
                    'multivalued' => false,
                    'type' => 'added',
                    'value' => $valueLastFile['value'],
                    'multilevel' => is_array($valueLastFile['value'])
                ];
        }

        if (is_array($dataLastFile[$key]) && is_array($dataFirstFile[$key])) {
            $child = createTree($dataFirstFile[$key], $dataLastFile[$key]);
            return ['name' => $key, 'type' => 'changed', 'multivalued' => false, 'multilevel' => true, 'value' => $child];
        }

        return $dataFirstFile[$key] === $dataLastFile[$key]
            ? ['name' => $key, 'multivalued' => false, 'type' => 'no_change', 'multilevel' => false, 'value' => $dataLastFile[$key]]
            : ['name' => $key, 'multivalued' => true, 'type' => 'changed', 'multilevel' => is_array($dataLastFile[$key]) || is_array(createValueTree($dataFirstFile[$key])['value']),
                'value_added' => is_array($dataLastFile[$key])
                    ? createValueTree($dataLastFile[$key])['value']
                    : $dataLastFile[$key],
                'value_deleted' => is_array($dataFirstFile[$key])
                    ? createValueTree($dataFirstFile[$key])['value']
                    : $dataFirstFile[$key]];
    }, $allKeys);

    return array_values($result) ?? [];
}

function createValueTree($dataValue)
{
    if (is_array($dataValue)) {
        $keys = array_keys($dataValue);

        return ['value' => array_map(function ($key) use ($dataValue) {
            if (is_array($dataValue[$key])) {
                $child = createValueTree($dataValue[$key]);
                return ['name' => $key, 'type' => 'no_change', 'multivalued' => false, 'multilevel' => true, 'value' => $child['value']];
            }

            return ['name' => $key, 'type' => 'no_change', 'multivalued' => false, 'multilevel' => false, 'value' => $dataValue[$key]];
        }, $keys)];
    }

    return ['value' => $dataValue];
}
