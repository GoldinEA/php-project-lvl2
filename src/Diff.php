<?php
declare(strict_types=1);

namespace Differ\Diff;

use Exception;
use Symfony\Component\Yaml\Yaml;
use function Format\diffHandler;
use function Differ\Format\createResult;


/**
 * @throws Exception Стандартное исключение.
 */
function genDiff(string $pathToFile1, string $pathToFile2, string $format): string
{
    $dataFile1 = getFileData($pathToFile1);
    $dataFile2 = getFileData($pathToFile2);
    $diff = differ($dataFile1, $dataFile2);

    return createResult([]);
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
    foreach ($allKeys as $key) {
        if(!isset($dataFirstFile[$key])){
           $result['-'. $key] = $dataLastFile[$key];
        } elseif (!isset($dataLastFile[$key])) {
            $result['+'. $key] = $dataLastFile[$key];
        } else {
            $result[$key] = differ($dataFirstFile[$key], $dataLastFile[$key]);
        }
    }
    return $result;
}
