<?php


namespace App\Entity\Types\Enum;


class LanguagesEnum
{
    const ENGLISH = 'en';
    const FRENCH = 'fr';
    const GERMAN = 'de';
    const GREEK = 'el';
    const SPANISH = 'es';
    const PORTUGUESE = 'pt';
    const RUSSIAN = 'ru';
    const FINNISH = 'fi';
    const CHINESE = 'zh';
    const JAPANESE = 'ja';
    const KOREAN = 'ko';
    const POLISH = 'pl';
    const INDIAN = 'in';
    const CROATIAN = 'hr';
    const SERBIAN = 'sr';
    const ITALIAN = 'it';
    const MOLDAVIAN = 'md';
    const MONGOLIAN = 'mn';
    const BRAZILIAN = 'br';
    const KAZAKH = 'kk';

    protected static $typeName = [
        self::ENGLISH => 'English',
        self::FRENCH => 'French',
        self::GERMAN => 'German',
        self::GREEK => 'Greek',
        self::SPANISH => 'Spanish',
        self::PORTUGUESE => 'Portuguese',
        self::RUSSIAN => 'Russian',
        self::FINNISH => 'Finnish',
        self::CHINESE => 'Chinese',
        self::JAPANESE => 'Japanese',
        self::KOREAN => 'Korean',
        self::POLISH => 'Polish',
        self::INDIAN => 'Indian',
        self::CROATIAN => 'Croatian',
        self::SERBIAN => 'Serbian',
        self::ITALIAN => 'Italian',
        self::MOLDAVIAN => 'Moldavian',
        self::MONGOLIAN => 'Mongolian',
        self::BRAZILIAN => 'Brazilian',
        self::KAZAKH => 'Kazakh',
    ];

    public static function getTypeName(string $typeShortName): string
    {
        return isset(self::$typeName[$typeShortName]) ? self::$typeName[$typeShortName] : null;
    }

    public static function getAvailableTypes(): array
    {
        return array_keys(self::$typeName);
    }
}