<?php


namespace App\Tests\Web;

use App\Tests\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends AbstractWebTestCase
{
    public function testView()
    {
        // 1. Login user
        $this->logInAsUser();

        // 2. Try to get user info (the response must have 200 status and content must have all access tokens params)
        $testKeysID = 'get user info with authenticated user';
        $responseCheckingParams = $this->createUserCheckingParams();
        $response = $this->request('get_user_info');
        $this->checkResponse($response, $testKeysID, $responseCheckingParams, true);

        // 3. Try to get user info with incorrect token
        $testKeysID = 'get user info with incorrect token';
        $this->accessToken = 'INVALID_TOKEN';
        $response = $this->request('get_user_info');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_UNAUTHORIZED, 'invalid access token');

        // 4. Try to get user info without token
        $testKeysID = 'get user info without token';
        $this->accessToken = null;
        $response = $this->request('get_user_info');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_UNAUTHORIZED, 'access token missed');

        // 5. Clear all access params
        $this->clearUserInfo();
    }

    public function testUpdate()
    {
        // 1. Login user
        $this->logInAsUser();

        // 2. Remember user params
        $oldUserParams = $this->createUserEntityParams($this->user, false);

        // 3. Change user params and post it to web
        $testKeysID = 'update user params';
        $newUserParams = $oldUserParams;
        $newUserParams['firstName'] = $this->faker->firstName;
        $newUserParams['lastName'] = $this->faker->lastName;
        $newUserParams['age'] = $this->faker->numberBetween(7, 120);
        $response = $this->request('update_user_info', ['user_form' => $newUserParams], 'PUT');
        $this->checkResponseStatus($response, $testKeysID, Response::HTTP_OK);

        // 4. Get user new params from web
        $testKeysID = 'check user params after update';
        $userCheckParams = $this->createUserCheckingParams();
        $response = $this->request('get_user_info');
        $this->checkResponse($response, $testKeysID, $userCheckParams, true);
        $userWebParams = $response->getData();
        unset($userWebParams['id']);

        // 5. Check is user web params not equal to old params
        $this->assertNotEquals($oldUserParams, $userWebParams, sprintf('Testing "%s" failed: user web params must be not equal to old user params', $testKeysID));

        // 6. Check is user web params equal to new params
        $this->assertEquals($newUserParams, $userWebParams, sprintf('Testing "%s" failed: user web params must be equal to new user params', $testKeysID));

        // 7. Logout user
        $testKeysID = 'check update action after logout';
        $response = $this->request('security_logout', [], 'POST');
        $this->checkResponseStatus($response, $testKeysID, Response::HTTP_OK);

        // 8. Try to update user info
        $response = $this->request('update_user_info', ['user_form' => $newUserParams], 'PUT');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_UNAUTHORIZED, 'invalid access token');

        // 9. Login user again acd check is "POST /user" action works
        $this->clearUserInfo();
        $this->logInAsUser();
        $response = $this->request('update_user_info', ['user_form' => $newUserParams], 'PUT');
        $this->checkResponseStatus($response, 'check user action after re-login', Response::HTTP_OK);

        // 10. Try to update user with incorrect access token
        $testKeysID = 'check update action after re-login with incorrect access token';
        $this->accessToken = 'INVALID_TOKEN';
        $response = $this->request('update_user_info', ['user_form' => $newUserParams], 'PUT');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_UNAUTHORIZED, 'invalid access token');

        // 11. Try to update user without access token
        $testKeysID = 'check update action after re-login without access token';
        $this->accessToken = null;
        $response = $this->request('update_user_info', ['user_form' => $newUserParams], 'PUT');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_UNAUTHORIZED, 'access token missed');

        // 12. Clear all access params
        $this->clearUserInfo();
    }
}