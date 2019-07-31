<?php

namespace App\Tests\Web;

use App\Entity\User;
use App\Tests\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends AbstractWebTestCase
{
    public function testLogin()
    {
        // 1. Enable test mode - make "/POST /login" request request and check response
        $this->isTestMode = true;

        // 2. Test correct login case
        $this->logInAsUser();

        // 3. Test incorrect username login test case
        $testKeysID = 'incorrect username login request';
        $response = $this->request('security_login', ['username' => 'incorrect_user@mail.to', 'password' => self::DEFAULT_PASSWORD], 'POST');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_UNAUTHORIZED);

        // 4. Test incorrect password login test case
        $testKeysID = 'incorrect password login request';
        $response = $this->request('security_login', ['username' => self::DEFAULT_USER, 'password' => 'INCORRECT_PASSWORD'], 'POST');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_UNAUTHORIZED);

        // Disable test mode
        $this->isTestMode = false;
    }

    public function testLogout()
    {
        // 1. Login user
        $this->logInAsUser();

        // 2. Try to get user info (the response must have 200 status)
        $testKeysID = 'get user info with authenticated user';
        $response = $this->request('get_user_info');
        $this->checkResponseStatus($response, $testKeysID, Response::HTTP_OK);

        // 3. Logout user
        $testKeysID = 'logout user';
        $response = $this->request('security_logout', [], 'POST');
        $this->checkResponseStatus($response, $testKeysID, Response::HTTP_OK);

        // 4. Try to get user info with incorrect access token
        $testKeysID = 'get user info after logout';
        $response = $this->request('get_user_info');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_UNAUTHORIZED);
    }

    public function testRenewToken()
    {
        // 1. Login user and remember old token
        $this->logInAsUser();

        // 2. Renew token
        $testKeysID = 'renew token';
        $requestParams = ['renew_token' => $this->renewToken];
        $response = $this->request('security_renew_token', $requestParams, 'POST');
        $this->checkResponseToken($response, $testKeysID);

        // 3. Check is token changed
        $renewTokenData = $response->getData();
        $this->assertNotEquals($renewTokenData['access_token'], $this->accessToken, sprintf('Test "%s" failed: returned token and old token are equals', $testKeysID));

        // 4. Try to get user info with old access token
        $testKeysID = 'get user info after renew token with old token';
        $response = $this->request('get_user_info');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_UNAUTHORIZED);

        // 5. Try to get user info with new token
        $this->accessToken = $renewTokenData['access_token'];
        $testKeysID = 'get user info after renew token with new token';
        $response = $this->request('get_user_info');
        $this->checkResponseStatus($response, $testKeysID, Response::HTTP_OK);

        // 6. Clear old auth params
        $this->clearUserInfo();
    }

    public function testRegistration()
    {
        // 1. Remove old auth params
        $this->clearUserInfo();

        // 2. Check registration action with correct data
        $testKeysID = 'test registration with correct data';
        $userParams = $this->createUserEntityParams();
        $response = $this->request('security_registration', ['user_form' => $userParams], 'POST');
        $this->checkResponseToken($response, $testKeysID);
        $responseParams = $response->getData();
        $decodedToken = base64_decode($responseParams['access_token']);

        // 3. Try to find user by access token
        $user = $this->em->getRepository(User::class)->findOneBy(['accessToken' => $decodedToken]);
        $this->assertNotNull($user, sprintf('Test "%s" failed: new user was not found by returned access token: %s (decoded %s)',
            $testKeysID, $responseParams['access_token'], $decodedToken));

        // 4. Check user params (must be equals to created earlier params array)
        $userParamsFromDb = $this->createUserEntityParams($user);
        $this->assertEquals($userParams, $userParamsFromDb, sprintf('Test "%s" failed: created params and params from DB area not equal', $testKeysID));

        // 5. Try to login this user
        $response = $this->request('security_login', ['username' => $userParams['email'], 'password' => $userParams['plainPassword']['first']], 'POST');
        $this->checkResponseToken($response, $testKeysID);

        // 6. Check registration action with correct data but without "firstName", "lastName" and "aboutMe" param
        $testKeysID = 'test registration without firstName, lastName and aboutMe';
        $userParams = $this->createUserEntityParams();
        unset($userParams['firstName'], $userParams['lastName'], $userParams['aboutMe']);
        $response = $this->request('security_registration', ['user_form' => $userParams], 'POST');
        $this->checkResponseToken($response, $testKeysID);

        // 7. Try to register already existed user
        $testKeysID = 'test registration with incorrect data: existed user';
        $response = $this->request('security_registration', ['user_form' => $userParams], 'POST');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, 'already registered');

        // 8. Try to register user without email
        $testKeysID = 'test registration with incorrect data: without email';
        unset($userParams['email']);
        $response = $this->request('security_registration', ['user_form' => $userParams], 'POST');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, 'email can not be blank');

        // 9. Try to register user without sex
        $testKeysID = 'test registration with incorrect data: without sex';
        $userParams = $this->createUserEntityParams();
        unset($userParams['sex']);
        $response = $this->request('security_registration', ['user_form' => $userParams], 'POST');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, 'sex can not be blank');

        // 10. Try to register user without age
        $testKeysID = 'test registration with incorrect data: without age';
        $userParams = $this->createUserEntityParams();
        unset($userParams['age']);
        $response = $this->request('security_registration', ['user_form' => $userParams], 'POST');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, 'age can not be blank');

        // 11. Try to register user with incorrect email
        $testKeysID = 'test registration with incorrect data: incorrect email';
        $userParams = $this->createUserEntityParams(['email' => 'INVALID_EMAIL']);
        $response = $this->request('security_registration', ['user_form' => $userParams], 'POST');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, 'is not a valid email');

        // 12. Try to register user without password
        $testKeysID = 'test registration with incorrect data: without password';
        $userParams = $this->createUserEntityParams([], false);
        $response = $this->request('security_registration', ['user_form' => $userParams], 'POST');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, 'password can not be blank');

        // 13. Try to register user with invalid second password
        $testKeysID = 'test registration with incorrect data: invalid second password';
        $userParams = $this->createUserEntityParams();
        $userParams['plainPassword']['second'] = 'INCORRECT_PASSWORD';
        $response = $this->request('security_registration', ['user_form' => $userParams], 'POST');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, 'password fields are not match');

        // 14. Try to register user without second password
        $testKeysID = 'test registration with incorrect data: without second password';
        unset($userParams['plainPassword']['second']);
        $response = $this->request('security_registration', ['user_form' => $userParams], 'POST');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, 'password fields are not match');

        // 15. Try to register user without first password
        $testKeysID = 'test registration with incorrect data: without first password';
        $userParams = $this->createUserEntityParams();
        unset($userParams['plainPassword']['first']);
        $response = $this->request('security_registration', ['user_form' => $userParams], 'POST');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, 'password fields are not match');

        // 16. Try to register user without first and second passwords
        $testKeysID = 'test registration with incorrect data: without first and second passwords';
        unset($userParams['plainPassword']['second']);
        $response = $this->request('security_registration', ['user_form' => $userParams], 'POST');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, 'password can not be blank');

        // 17. Try to register user with empty first and second passwords
        $testKeysID = 'test registration with incorrect data: empty first and second passwords';
        $userParams['plainPassword'] = ['first' => '', 'second' => ''];
        $response = $this->request('security_registration', ['user_form' => $userParams], 'POST');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, 'password can not be blank');
    }
}
