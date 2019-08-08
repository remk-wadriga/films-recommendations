<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 12.09.2018
 * Time: 18:03
 */

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException as BaseHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;

class HttpException extends BaseHttpException
{
    const CODE_BAD_REQUEST = 1400;
    const CODE_UNAUTHORIZED = 1401;
    const CODE_ACCESS_DENIED = 1403;
    const CODE_NOT_FOUND = 1404;
    const CODE_SYSTEM_ERROR = 1500;

    public $message = 'Something went wrong!';

    public function __construct(?string $message = null, int $code = 0, \Exception $previous = null, array $headers = [])
    {
        if ($previous !== null) {
            if ($code === 0) {
                $code = $previous->getCode();
            }
            if (!empty($previous->getMessage())) {
                if ($message !== null && $message !== $previous->getMessage()) {
                    $message .= ': ';
                }
                if ($message !== $previous->getMessage()) {
                    $message .= $previous->getMessage();
                }
                if ($message === null) {
                    $message = $previous->getMessage();
                }
            }
        }

        if ($message === null) {
            $message = '';
        }

        if ($previous instanceof BaseHttpException) {
            switch ($previous->getStatusCode()) {
                case Response::HTTP_FORBIDDEN:
                    $code = self::CODE_ACCESS_DENIED;
                    break;
                case Response::HTTP_BAD_REQUEST:
                    $code = self::CODE_BAD_REQUEST;
                    break;
                case Response::HTTP_NOT_FOUND:
                    $code = self::CODE_NOT_FOUND;
                    break;
            }
        } elseif ($previous instanceof AccessDeniedException) {
            $code = self::CODE_ACCESS_DENIED;
        } elseif ($previous instanceof AuthenticationException) {
            $code = self::CODE_UNAUTHORIZED;
        }

        switch ($code) {
            case self::CODE_NOT_FOUND:
            case FileException::NOT_FOUND:
                $statusCode = Response::HTTP_NOT_FOUND;
                break;
            case self::CODE_BAD_REQUEST:
                $statusCode = Response::HTTP_BAD_REQUEST;
                break;
            case self::CODE_ACCESS_DENIED:
                $statusCode = Response::HTTP_FORBIDDEN;
                break;
            case ServiceException::CODE_INVALID_PARAMS:
                $statusCode = Response::HTTP_BAD_REQUEST;
                break;
            case FileException::UNSUPPORTED_FORMAT:
                $statusCode = Response::HTTP_UNSUPPORTED_MEDIA_TYPE;
                break;
            case self::CODE_UNAUTHORIZED:
            case AccessTokenAuthenticationException::CODE_ACCESS_TOKEN_EXPIRED:
            case AccessTokenAuthenticationException::CODE_INVALID_ACCESS_TOKEN:
            case AccessTokenAuthenticationException::CODE_REQUIRED_PARAM_MISSING:
                $statusCode = Response::HTTP_UNAUTHORIZED;
                break;
            default:
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                break;
        }

        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }
}