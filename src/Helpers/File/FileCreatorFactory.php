<?php


namespace App\Helpers\File;

use App\Exception\FileException;
use App\Exception\ServiceException;

class FileCreatorFactory
{
    const EXT_JPG = 'jpg';
    const EXT_JPEG = 'jpeg';
    const EXT_PNG = 'png';

    private static $_classesMap = [
        self::EXT_JPG => ImageCreator::class,
        self::EXT_JPEG => ImageCreator::class,
        self::EXT_PNG => ImageCreator::class,
    ];

    public static function createFileCreator(string $directory, string $fileName, string $data): FileCreatorInterface
    {
        $ext = FileHelper::getExt($fileName);

        if ($ext === null || !isset(self::$_classesMap[$ext])) {
            throw new FileException(sprintf('Unsupported file format: %s', $ext), FileException::UNSUPPORTED_FORMAT);
        }

        $creatorClass = self::$_classesMap[$ext];
        if (!is_subclass_of($creatorClass, FileCreatorAbstract::class)) {
            throw new ServiceException(sprintf('File creator must instance of %s abstract class, but it does not. Creator class: %s', FileCreatorAbstract::class, $creatorClass), ServiceException::CODE_INVALID_CONFIG);
        }

        return new $creatorClass($directory, $fileName, $data);
    }
}