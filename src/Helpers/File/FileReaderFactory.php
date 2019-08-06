<?php


namespace App\Helpers\File;

use App\Exception\FileException;
use App\Exception\ServiceException;

class FileReaderFactory
{
    const EXT_CSV = 'csv';

    private static $_classesMap = [
        self::EXT_CSV => CsvFileReader::class
    ];

    public static function createReader(string $file): FileReaderInterface
    {
        $ext = FileHelper::getExt($file);

        if ($ext === null || !isset(self::$_classesMap[$ext])) {
            throw new FileException(sprintf('Unsupported file format: %s', $ext), FileException::UNSUPPORTED_FORMAT);
        }

        $readerClass = self::$_classesMap[$ext];
        if (!is_subclass_of($readerClass, AbstractFileReader::class)) {
            throw new ServiceException(sprintf('File reader must instance of %s abstract class, but it does not. Reader class: %s', AbstractFileReader::class, $readerClass), ServiceException::CODE_INVALID_CONFIG);
        }

        return new $readerClass($file, $ext);
    }
}