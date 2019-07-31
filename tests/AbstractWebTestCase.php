<?php


namespace App\Tests;

use App\Entity\Types\Enum\GenderEnum;
use App\Entity\User;
use App\Security\AccessTokenAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Faker\Factory;

abstract class AbstractWebTestCase extends WebTestCase
{
    const DEFAULT_USER = 'user@gmail.com';
    const DEFAULT_PASSWORD = 'test';

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client;

    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    protected $router;

    /** @var \App\Entity\User|nul */
    protected $user;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /** @var \Faker\Generator */
    protected $faker;


    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var string
     */
    protected $renewToken;

    /**
     * @var string
     */
    protected $tokenExpiredAt;

    protected $isTestMode = false;

    public function setUp()
    {
        parent::setUp();
        self::bootKernel();
        $this->client = static::createClient();
        $this->router = $this->client->getContainer()->get('router');
        $this->em = $this->client->getContainer()->get('doctrine')->getManager();
        $this->faker = Factory::create();
    }

    protected function findUser($conditions, bool $forgetUser = false): User
    {
        if (!is_array($conditions)) {
            $conditions = ['email' => $conditions];
        }
        $errorMessage = 'User not found in DB';
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->em->getRepository(User::class);
        /** @var \App\Entity\User $lastUser */
        try {
            // Find user by conditions
            $user = $userRepository->findOneBy($conditions);
            // Remove doctrine cache
            if ($forgetUser) {
                $this->em->clear(User::class);
            }
        } catch (\Exception $e) {
            $errorMessage .= ': ' . $e->getMessage();
            $user = null;
        }
        $this->assertNotNull($user, $errorMessage);
        return $user;
    }

    protected function request($routeName, array $data = [], string $method = 'GET', array $headers = [], array $files = []): ?ApiResponse
    {
        // Set default content type header
        if (!isset($headers['CONTENT_TYPE'])) {
            $headers['CONTENT_TYPE'] = 'application/json';
        }
        // If user is already logged in, add the access token in request headers
        if ($this->accessToken !== null && !isset($headers[AccessTokenAuthenticator::ACCESS_TOKEN_HEADER_PARAM_NAME])) {
            $tokenType = AccessTokenAuthenticator::TOKEN_TYPE;
            $headers[AccessTokenAuthenticator::ACCESS_TOKEN_HEADER_PARAM_NAME] = !empty($tokenType) ? $tokenType . ' ' . $this->accessToken : $this->accessToken;
        }
        // Add "HTTP_" prefix for all headers
        foreach ($headers as $name => $header) {
            if (!in_array($name, ['CONTENT_TYPE', 'HTTP_REFERER'])) {
                $headers['HTTP_' . $name] = $header;
                unset($headers[$name]);
            }
        }
        // If thit is json request - convert data to json
        if ($headers['CONTENT_TYPE'] === 'application/json') {
            $body = is_array($data) ? json_encode($data) : $data;
            $data = [];
        } else {
            // In other cases request params must be an array
            if (!is_array($data)) {
                $data = [];
            }
            $body = '';
        }

        // Create url by route name
        if (is_array($routeName)) {
            $prams = $routeName[1];
            $routeName = $routeName[0];
        } else {
            $prams = [];
        }
        $url = $this->router->generate($routeName, $prams);

        // Make request
        $crawler = $this->client->request($method, $url, $data, $files, $headers, $body);

        // Process response
        try {
            // and return it if everything is OK
            return new ApiResponse($this->client->getResponse(), $crawler);
        } catch (\Exception $e) {
            // if something is wrong - throw standard request test error and return null
            $this->assertTrue(false, sprintf('Request error: %s', $e->getMessage()));
            return null;
        }
    }

    protected function logInAsUser($username = null, $password = null, $loginID = 'user'): ?ApiResponse
    {
        if ($username === null) {
            $username = self::DEFAULT_USER;
        }
        if ($password === null) {
            $password = self::DEFAULT_PASSWORD;
        }
        return $this->logIn($username, $password, $loginID);
    }

    protected function logIn($username, $password, $loginID = 'user'): ?ApiResponse
    {
        // If this is not test mode request - just get user from DB, remember it and user's access token
        if (!$this->isTestMode) {
            if ($this->user === null) {
                $this->user = $this->em->getRepository(User::class)->findOneBy(['email' => $username]);
                // If user token is steel alive, then save this user token and return null
                if ($this->user !== null && $this->user->getAccessTokenExpiredAt()->getTimestamp() > (new \DateTime())->getTimestamp()) {
                    $this->accessToken = base64_encode($this->user->getAccessToken());
                    $this->renewToken = base64_encode($this->user->getRenewToken());
                    $this->tokenExpiredAt = $this->user->getAccessTokenExpiredAt()->format('Y-m-d H:i:s');
                    return null;
                }
            }
        }

        // Create body params for login request
        $params = [
            'username' => $username,
            'password' => $password,
        ];
        // Send response
        $response = $this->request('security_login', $params, 'POST');

        // Check status
        $this->assertEquals(200, $response->getStatus(),
            sprintf('Can`t login %s, status code is not 200, it is %s, and content is: %s', $loginID, $response->getStatus(), $response->getContent()));
        // Check token in response
        list($this->accessToken, $this->renewToken, $this->tokenExpiredAt) = $this->checkResponseToken($response);
        $this->user = $this->em->getRepository(User::class)->findOneBy(['accessToken' => base64_decode($this->accessToken)]);

        // If everything is all right or thit is test mode request, just return the API response
        return $response;
    }

    protected function checkResponseToken(ApiResponse $response, string $testKeysID = 'Login action'): array
    {
        $responseCheckingParams = [
            'access_token' => 'string',
            'renew_token' => 'string',
            'expired_at' => 'string'
        ];
        $this->checkResponse($response, $testKeysID, $responseCheckingParams, true);
        $data = $response->getData();

        return [$data['access_token'], $data['renew_token'], $data['expired_at']];
    }

    protected function checkResponse(ApiResponse $response, string $testKeysID, array $params, $singleResult = false, $expectedCode = Response::HTTP_OK)
    {
        // Check status
        $this->checkResponseStatus($response, $testKeysID, $expectedCode);
        // Check data
        $data = $response->getData();
        $this->assertInternalType('array', $data, sprintf('Wrong test "%s" response. The response data must be an array, but "%s" given. Data: %s',
            $testKeysID, gettype($data), $response->getContent()));
        if ($singleResult) {
            $data = [$data];
        }
        foreach ($data as $stats) {
            $jsonData = json_encode($stats);
            $this->assertInternalType('array', $stats, sprintf('Wrong test "%s" response. Each response data item must be an array, but "%s" given. Data: %s',
                $testKeysID, gettype($stats), $jsonData));

            foreach ($params as $attr => $type) {
                if (is_array($type)) {
                    $type1 = $type[0];
                    $type2 = $type[1];
                    $type = $type1;
                    if (isset($stats[$attr]) && gettype($stats[$attr]) === $type2) {
                        settype($stats[$attr], $type1);
                    }
                }

                $this->assertArrayHasKey($attr, $stats, sprintf('Wrong test "%s" response. Each response data item have the "%s" param, but it\'s not. Data: %s',
                    $testKeysID, $attr, $jsonData));
                $this->assertInternalType($type, $stats[$attr], sprintf('Wrong test "%s" response. Each response data.%s item must be a %s, but "%s" given. Data: %s',
                    $testKeysID, $attr, $type, gettype($stats[$attr]), $jsonData));
            }
        }
    }

    protected function checkIncorrectResponse(ApiResponse $response, $testKeysID, $expectedCode = Response::HTTP_BAD_REQUEST, $expectedPhrase = null)
    {
        $requestCheckingParams = ['message' => 'string', 'code' => 'integer'];
        $this->checkResponse($response, $testKeysID, $requestCheckingParams, true, $expectedCode);
        if ($expectedPhrase !== null) {
            $expectedPhrase = strtolower($expectedPhrase);
            $responseMessage = strtolower($response->get('message'));
            $this->assertContains($expectedPhrase, $responseMessage, sprintf('Wrong test "%s" response format: the response message must contain the "%s" words. The response message is: "%s"',
                $testKeysID, $expectedPhrase, $responseMessage));
        }
    }

    protected function checkResponseStatus(ApiResponse $response, $testKeysID, $expectedCode)
    {
        $this->assertEquals($expectedCode, $response->getStatus(),
            sprintf('Wrong test "%s" response format, status code must be equal to %s, but it is not. It is: %s. The content is: %s',
                $testKeysID, $expectedCode, $response->getStatus(), $response->getContent()));
    }

    protected function clearUserInfo()
    {
        $this->accessToken = null;
        $this->renewToken = null;
        $this->tokenExpiredAt = null;
        $this->user = null;
    }

    protected function getParam($name)
    {
        $container = self::$kernel->getContainer();
        if (!$container->hasParameter($name)) {
            return null;
        }
        return str_replace('0/0', '%', $container->getParameter($name));
    }

    protected function createUserEntityParams($params = [], $needPassword = true): array
    {
        if ($params instanceof User) {
            $params = [
                'email' => $params->getEmail(),
                'firstName' => $params->getFirstName(),
                'lastName' => $params->getLastName(),
                'age' => $params->getAge(),
                'sex' => $params->getSex(),
                'aboutMe' => $params->getAboutMe(),
            ];
        }

        if (!isset($params['email'])) {
            $params['email'] = time() . '_' . $this->faker->email;
        }
        if (!isset($params['firstName'])) {
            $params['firstName'] = $this->faker->firstName;
        }
        if (!isset($params['lastName'])) {
            $params['lastName'] = $this->faker->lastName;
        }
        if (!isset($params['age'])) {
            $params['age'] = $this->faker->numberBetween(7, 120);
        }
        if (!isset($params['sex'])) {
            $params['sex'] = $this->faker->randomElement(GenderEnum::getAvailableTypes());
        }
        if (!isset($params['aboutMe'])) {
            $params['aboutMe'] = $this->faker->text;
        }
        if ($needPassword && !isset($params['plainPassword'])) {
            $params['plainPassword'] = [
                'first' => $params['email'],
                'second' => $params['email'],
            ];
        }

        return $params;
    }
}