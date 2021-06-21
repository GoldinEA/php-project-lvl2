<?php
declare(strict_types=1);

namespace Differ\Diff;

use Exception;
use Symfony\Component\Yaml\Yaml;
use function Differ\Format\diffHandler;
use function Differ\Format\createResult;


/**
 * @throws Exception Стандартное исключение.
 */
function genDiff(string $pathToFile1, string $pathToFile2, string $format): string
{
    $dataFile1 = getFileData($pathToFile1);
    $dataFile2 = getFileData($pathToFile2);

    $intersect = array_intersect($dataFile1, $dataFile2);
    $diff = differ($dataFile1, $dataFile2);
    $diff1 = diffHandler(array_diff($dataFile2, $intersect), '-');
    $diff2 = diffHandler(array_diff($dataFile1, $intersect), '+');
    $arr = array_merge($intersect, $diff1, $diff2);
    return createResult($arr);
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

function differ(array $data1, array $data2): array
{
    $result = array_map(function ($elem1, $elem) {
        if (is_array($elem1) || is_array($elem)) {
            return differ($elem, $elem1);
        }
    }, $data1, $data2);

    $keys = array_keys($data1);
    $keys2 = array_keys($data2);

    return $result;
}