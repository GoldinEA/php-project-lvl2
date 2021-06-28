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
    $diff = differ($dataFile1, $dataFile2, true);
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

function differ(array $data1, array $data2, bool $firstStep = false): array
{
    $result = [];
    if ($firstStep) {
        $dataDiff1 = array_filter($data1, function ($value){
            return !is_array($value);
        }, ARRAY_FILTER_USE_BOTH);
        $dataDiff2 = array_filter($data1, function ($value){
            return !is_array($value);
        }, ARRAY_FILTER_USE_BOTH);
        $result = diff($dataDiff1, $dataDiff2);
    }
    foreach ($data1 as $index => $item) {
        if (!empty($data2[$index]) && is_array($item) && is_array($data2[$index])) {
            $result[$index] = diff($item, $data2[$index]);
            differ($item, $data2[$index]);
        }
    }
    return $result;
}

function diff(array $data, array $dataSecond)
{
    $intersect = array_intersect($data, $dataSecond);
    $diff1 = diffHandler(array_diff($dataSecond, $intersect), '-');
    $diff2 = diffHandler(array_diff($data, $intersect), '+');
    return array_merge($intersect, $diff1, $diff2);
}