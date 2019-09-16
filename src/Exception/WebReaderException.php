<?php


namespace App\Exception;


class WebReaderException extends ServiceException
{
    const INVALID_FORMAT = 4400;

    const NOT_READABLE = 4403;

    const NOT_FOUND = 4404;

    const UNSUPPORTED_TYPE = 4415;
}