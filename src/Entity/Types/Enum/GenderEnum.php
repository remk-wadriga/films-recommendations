<?php


namespace App\Entity\Types\Enum;


class GenderEnum
{
    const TYPE_MALE = 'male';
    const TYPE_FEMALE = 'female';

    protected static $typeName = [
        self::TYPE_MALE => 'Male',
        self::TYPE_FEMALE => 'Female',
    ];

    public static function getTypeName(string $typeShortName): string
    {
        return isset(static::$typeName[$typeShortName]) ? static::$typeName[$typeShortName] : null;
    }

    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_MALE, self::TYPE_FEMALE
        ];
    }
}