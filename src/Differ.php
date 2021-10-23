<?php
declare(strict_types=1);

namespace Differ\Differ;

use Exception;
use Symfony\Component\Yaml\Yaml;
use function Differ\Format\format;


/**
 * @throws Exception Стандартное исключение.
 */
function genDiff(string $pathToFile1, string $pathToFile2, string $format): string
{
    $dataFile1 = getFileData($pathToFile1);
    $dataFile2 = getFileData($pathToFile2);
    $tree = createTree($dataFile1, $dataFile2);
    return format($tree, $format);
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

function createTree(array $data1, array $data2): array
{
    $keysFirst = array_keys($data1);
    $keysLast = array_keys($data2);
    $allKeys = array_unique(array_merge($keysFirst, $keysLast));

    $result = array_map(function ($key) use ($data1, $data2) {
        if (!array_key_exists($key, $data1) || !array_key_exists($key, $data2)) {
            $valueFirstFile = array_key_exists($key, $data1) ? createValueTree($data1[$key]) : '';
            $valueLastFile = array_key_exists($key, $data2) ? createValueTree($data2[$key]) : '';
            return array_key_exists($key, $data1)
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

        if (is_array($data2[$key]) && is_array($data1[$key])) {
            $child = createTree($data1[$key], $data2[$key]);
            return ['name' => $key, 'type' => 'changed', 'multivalued' => false, 'multilevel' => true, 'value' => $child];
        }

        return $data1[$key] === $data2[$key]
            ? ['name' => $key, 'multivalued' => false, 'type' => 'no_change', 'multilevel' => false, 'value' => $data2[$key]]
            : ['name' => $key, 'multivalued' => true, 'type' => 'changed', 'multilevel' => is_array($data2[$key]) || is_array(createValueTree($data1[$key])['value']),
                'value_added' => is_array($data2[$key])
                    ? createValueTree($data2[$key])['value']
                    : $data2[$key],
                'value_deleted' => is_array($data1[$key])
                    ? createValueTree($data1[$key])['value']
                    : $data1[$key]];
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
