<?php


namespace App\Helpers\Web;


use App\Exception\ServiceException;
use App\Exception\WebReaderException;

class WebReaderFactory
{
    const TYPE_HTML = 'html';

    private static $_classesMap = [
        self::TYPE_HTML => HtmlReader::class,
    ];

    public static function createWebReader(string $type, array $config = []): WebReaderInterface
    {
        if (!isset(self::$_classesMap[$type])) {
            throw new WebReaderException(sprintf('Unsupported reader type: %s', $type), WebReaderException::UNSUPPORTED_TYPE);
        }

        $readerClass = self::$_classesMap[$type];
        if (!is_subclass_of($readerClass, AbstractWebReader::class)) {
            throw new ServiceException(sprintf('Web reader must instance of %s abstract class, but it does not. Reader class: %s', AbstractWebReader::class, $readerClass), ServiceException::CODE_INVALID_CONFIG);
        }

        return new $readerClass($config);
    }
}