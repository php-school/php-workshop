<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\Exception\AssetsNotInitialisedException;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;

class ExerciseAssets
{
    /**
     * @var string
     */
    private static $assetPath;

    /**
     * @var array<string>
     */
    private static $assetTypes = ['solution', 'problem', 'initial'];

    /**
     * Initialise the asset helper with the workshop base
     * exercise asset directory.
     *
     * @param string $assetPath
     */
    public static function init(string $assetPath): void
    {
        if (!is_dir($assetPath)) {
            throw new InvalidArgumentException('Exercise assets path must be a directory');
        }

        static::$assetPath = realpath($assetPath);
    }


    public static function getAssetPath(ExerciseInterface $exercise, string $assetType, string $fileName = null): string
    {
        static::guardInitialised();

        if (!in_array($assetType, static::$assetTypes, true)) {
            throw InvalidArgumentException::notInArray($assetType, static::$assetTypes);
        }

        $path = sprintf(
            '%s/%s/%s',
            static::$assetPath,
            self::normaliseName($exercise->getName()),
            $assetType
        );

        return $fileName === null ? $path : "$path/$fileName";
    }

    /**
     * @param string $name
     * @return string
     */
    public static function normaliseName(string $name): string
    {
        return (string) preg_replace('/[^A-Za-z\-]+/', '', str_replace(' ', '-', strtolower($name)));
    }

    private static function guardInitialised(): void
    {
        if (!static::$assetPath) {
            throw AssetsNotInitialisedException::new();
        }
    }
}
