<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 07.09.2018
 * Time: 05:20
 */

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AccessTokenAuthenticationException extends AuthenticationException
{
    const CODE_SYSTEM_ERROR = 1000;
    const CODE_INVALID_ACCESS_TOKEN = 1001;
    const CODE_ACCESS_TOKEN_EXPIRED = 1002;
    const CODE_REQUIRED_PARAM_MISSING = 1003;
    const CODE_INVALID_REQUEST_PARAMS = 1004;
}