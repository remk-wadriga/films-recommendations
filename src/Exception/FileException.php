<?php


namespace App\Exception;


class FileException extends ServiceException
{
    const INVALID_FORMAT = 3400;

    const NOT_READABLE = 3403;

    const NOT_FOUND = 3404;

    const UNSUPPORTED_FORMAT = 3415;
}