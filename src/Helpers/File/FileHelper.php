<?php


namespace App\Helpers\File;


class FileHelper
{
    public static function getExt($file)
    {
        preg_match("/^.+\.(\w+)$/", $file, $matches);
        return is_array($matches) && isset($matches[1]) ? $matches[1] : null;
    }
}