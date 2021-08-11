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
    $dataDiff = differ($dataFile1, $dataFile2);
    return createResult($dataDiff);
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

function differ(array $dataFirstFile, array $dataLastFile)
{
    $result = [];
    $keysFirst = array_keys($dataFirstFile);
    $keysLast = array_keys($dataLastFile);
    $allKeys = array_merge($keysFirst, $keysLast);

//    $result = array_map(function () {}, $allKeys); TODO сделать через array_map.
    foreach ($allKeys as $key) {
        if (!array_key_exists($key, $dataFirstFile)) {
            $result['- ' . $key] = $dataLastFile[$key];
        } elseif (!array_key_exists($key, $dataLastFile)) {
            $result['+ ' . $key] = $dataFirstFile[$key];
        } else {
            if (is_array($dataLastFile[$key]) && is_array($dataFirstFile[$key])) {
                $result[$key] = differ($dataFirstFile[$key], $dataLastFile[$key]);
            } elseif (!is_array($dataLastFile[$key]) && !is_array($dataFirstFile[$key])) {
                if ($dataLastFile[$key] === $dataFirstFile[$key]) {
                    $result[$key] = $dataLastFile[$key];
                } else {
                    $result["+ $key"] = $dataFirstFile[$key];
                    $result["- $key"] = $dataLastFile[$key];
                }
            }
        }
    }
    return $result;
}

function createTree(array $dataFirstFile, array $dataLastFile)
{
    $keysFirst = array_keys($dataFirstFile);
    $keysLast = array_keys($dataLastFile);
    $allKeys = array_unique(array_merge($keysFirst, $keysLast));

    $result = array_map(function ($value) use ($dataFirstFile, $dataLastFile) {
        if (array_key_exists($value, $dataFirstFile) && array_key_exists($value, $dataLastFile)) {
            if ($dataFirstFile[$value] === $dataLastFile[$value]) {
                return [$value => $dataLastFile[$value]];
            } else {
                return [$value => [
                    '+' => $dataFirstFile[$value],
                    '-' => $dataLastFile[$value]
                ]];
            }
        } else {
            return array_key_exists($value, $dataFirstFile)
                ? ['+' => [$value => $dataFirstFile[$value]]]
                : ['-' => [$value => $dataLastFile[$value]]];
        }
    }, $allKeys);

     $res = [];
    foreach ($result as $item) {
        $keyFirst = array_key_first($item);
        if (array_key_exists($keyFirst, $res)) {
            $child = array_shift($item);
            $res[$keyFirst][array_key_first($child)] = array_shift($child);
        } else {
            $res[$keyFirst] = array_shift($item);
        }
    }

    return $res ?? [];
}
