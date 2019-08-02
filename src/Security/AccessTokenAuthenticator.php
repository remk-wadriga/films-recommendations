<?php


namespace App\Security;

use App\Exception\AccessTokenAuthenticationException;
use App\Exception\HttpException;
use App\Helpers\AccessTokenEntityInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class AccessTokenAuthenticator extends AbstractGuardAuthenticator
{
    const ACCESS_TOKEN_HEADER_PARAM_NAME = 'Authorization';
    const ACCESS_TOKEN_URI_PARAM_NAME = 'access_token';
    const RENEW_TOKEN_REQUEST_PARAM_NAME = 'renew_token';
    const TOKEN_TYPE = 'Bearer';

    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public function supports(Request $request)
    {
        $urls = [
            $this->router->generate('security_login'),
            $this->router->generate('security_registration'),
        ];

        return !in_array($request->getPathInfo(), $urls);
    }

    public function getCredentials(Request $request)
    {
        // 1. Try to get token from headers or from query string:
        $token = $request->headers->get(self::ACCESS_TOKEN_HEADER_PARAM_NAME);
        if (empty($token)) {
            $token = $request->query->get(self::ACCESS_TOKEN_URI_PARAM_NAME);
        // 2. If this is token from headers - it must contain the token type
        } elseif (strpos($token, self::TOKEN_TYPE . ' ') !== 0) {
            throw new AuthenticationException(sprintf('Incorrect access token type. There are only "%s" tokes supported', self::TOKEN_TYPE),
                AccessTokenAuthenticationException::CODE_INVALID_REQUEST_PARAMS);
        }
        // 3. Remove the token type from token string
        $token = str_replace(self::TOKEN_TYPE . ' ', '', $token);

        // 4. No token - just set to null
        if (empty($token)) {
            $token = null;
        }
        // 5. What you return here will be passed to getUser() as $credentials
        $credentials = [
            'access_token' => base64_decode($token),
        ];
        if ($request->getPathInfo() === $this->router->generate('security_renew_token')) {
            try {
                $params = json_decode($request->getContent(), true);
                if ($params === null) {
                    throw new \Exception(sprintf('Request body has invalid json format: %s', $request->getContent()));
                }
            } catch (\Exception $e) {
                throw new AuthenticationException(sprintf('Can`t parse request body: %s', $e->getMessage()), AccessTokenAuthenticationException::CODE_INVALID_REQUEST_PARAMS);
            }
            $renewToken = isset($params[self::RENEW_TOKEN_REQUEST_PARAM_NAME]) ? $params[self::RENEW_TOKEN_REQUEST_PARAM_NAME] : null;
            if ($renewToken === null) {
                throw new AuthenticationException(sprintf('Param "%s" is required', self::RENEW_TOKEN_REQUEST_PARAM_NAME), AccessTokenAuthenticationException::CODE_REQUIRED_PARAM_MISSING);
            }
            $credentials['renew_token'] = base64_decode($renewToken);
        }
        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        // Check is user provider implements a correct interface
        if (!($userProvider instanceof AccessTokenUserProvider)) {
            throw new AuthenticationException(
                sprintf('User provider must instance of %s, %s given', AccessTokenUserProvider::class, get_class($userProvider)),
                AccessTokenAuthenticationException::CODE_SYSTEM_ERROR);
        }

        // Get access token from request (mustn't be null or empty string)
        $token = $credentials['access_token'];
        if (empty($token)) {
            throw new AuthenticationException('Access token missed', AccessTokenAuthenticationException::CODE_REQUIRED_PARAM_MISSING);
        }

        // Try to find user by access token
        $user = $userProvider->loadUserByAccessToken($token);
        if ($user === null) {
            throw new AuthenticationException('Invalid access token', AccessTokenAuthenticationException::CODE_INVALID_ACCESS_TOKEN);
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // Check is user implements correct interface
        if (!($user instanceof AccessTokenEntityInterface)) {
            throw new AuthenticationException(
                sprintf('User entity must instance of %s, %s given', AccessTokenEntityInterface::class, get_class($user)),
                AccessTokenAuthenticationException::CODE_SYSTEM_ERROR);
        }

        // If this is "renew token" request - check the renew token from request
        if (isset($credentials['renew_token']) && $credentials['renew_token'] !== $user->getRenewToken()) {
            throw new AuthenticationException('Invalid renew token', AccessTokenAuthenticationException::CODE_INVALID_REQUEST_PARAMS);
        // For all other requests check the access token is not expired
        } elseif (!isset($credentials['renew_token']) && $user->getAccessTokenExpiredAt()->getTimestamp() < (new \DateTime())->getTimestamp()) {
            throw new AuthenticationException('Access token expired', AccessTokenAuthenticationException::CODE_ACCESS_TOKEN_EXPIRED);
        }

        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        throw new HttpException($exception->getMessage(), $exception->getCode(), $exception);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    public function supportsRememberMe()
    {
        return false;
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            'message' => $authException->getMessage(),
            'code' => $authException->getCode(),
        ];
        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}