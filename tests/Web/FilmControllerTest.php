<?php

namespace App\Tests\Web;

use App\Entity\Actor;
use App\Entity\Company;
use App\Entity\Director;
use App\Entity\Film;
use App\Entity\Genre;
use App\Entity\Language;
use App\Entity\Premium;
use App\Entity\Producer;
use App\Entity\User;
use App\Entity\Writer;
use App\Helpers\ListedEntityInterface;
use App\Tests\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class FilmControllerTest extends AbstractWebTestCase
{
    public function testList()
    {
        // 1. Login User
        $this->logInAsUser();
        $user = $this->user;

        // 2. Get films list and check response
        $checkParams = $this->getFilmCheckParams();
        $testKeysID = 'get default count of films';
        $response = $this->request('films_list');
        $this->checkResponse($response, $testKeysID, $checkParams);
        $defaultCount = $this->getParam('default_item_limit');
        $filmsCount = count($response->getData());
        $this->assertEquals($defaultCount, $filmsCount, sprintf('Test case "%s" failed: default films list count is %s, but given %s',
            $testKeysID, $defaultCount, $filmsCount));

        // 3. Get films list with random "limit" param
        $randomCount = $this->faker->numberBetween(10, 20);
        $testKeysID = sprintf('get %s films count', $randomCount);
        $response = $this->request('films_list', ['limit' => $randomCount]);
        $this->checkResponse($response, $testKeysID, $checkParams);
        $data = $response->getData();
        $filmsCount = count($data);
        $this->assertEquals($randomCount, $filmsCount, sprintf('Test case "%s" failed: default films list count is %s, but given %s',
            $testKeysID, $randomCount, $filmsCount));
        // Remember the first and eht last films
        $oldFirstFilm = $data[0];
        $oldLastFilm = end($data);

        // 4. Get films list with random "limit" param and the same "offset"
        $testKeysID = sprintf('get %s films count with offset %s', $randomCount, $randomCount);
        $response = $this->request('films_list', ['limit' => $randomCount, 'offset' => $randomCount]);
        $this->checkResponse($response, $testKeysID, $checkParams);
        $data = $response->getData();
        $filmsCount = count($data);
        $this->assertEquals($randomCount, $filmsCount, sprintf('Test case "%s" failed: default films list count is %s, but given %s',
            $testKeysID, $randomCount, $filmsCount));
        // Remember the first and eht last films
        $newFirstFilm = $data[0];
        $newLastFilm = end($data);

        // 5. Check is first element of "limited" request and the first element of request with limit and offset are not equals
        $testKeysID = 'check the difference between first elements of "limited" request and request with limit and offset';
        $this->assertNotEquals($oldFirstFilm['id'], $newFirstFilm['id'], sprintf('Test case "%s" failed: elements area equals', $testKeysID));

        // 6. Check is last element of "limited" request and the last element of request with limit and offset are not equals
        $testKeysID = 'check the difference between last elements of "limited" request and request with limit and offset';
        $this->assertNotEquals($oldLastFilm['id'], $newLastFilm['id'], sprintf('Test case "%s" failed: elements area equals', $testKeysID));

        // 7. Check is last element of "limited" request and the first element of request with limit and offset are not equals
        $testKeysID = 'check the difference between last element of "limited" request and the first element of request with limit and offset';
        $this->assertNotEquals($oldLastFilm['id'], $newFirstFilm['id'], sprintf('Test case "%s" failed: elements area equals', $testKeysID));
    }

    public function testView()
    {
        // 1. Login User
        $this->logInAsUser();
        $user = $this->user;

        // 2. Get user film
        $film = $this->getUserFilm($user);

        // 3. Get make "GET /film/:id" request and check response
        $testKeysID = 'check "GET /film/:id" request';
        $checkParams = $this->getFilmCheckParams();
        $response = $this->request(['film_view', ['id' => $film->getId()]]);
        $this->checkResponse($response, $testKeysID, $checkParams, true);

        // 4. Check response params
        $testKeysID = 'check response data';
        $responseData = $response->getData();
        $this->assertEquals(true, $responseData['isMy'], sprintf('Test case "%s" failed: param "isMy" must be equals to true but it\'s not', $testKeysID));
        $this->compareResponseAndDbFilms($responseData, $film, $testKeysID);
    }

    public function testCreate()
    {
        // 1. Login User
        $this->logInAsUser();
        $user = $this->user;

        // 2. Try to create new film
        $testKeysID = 'create film with correct params';
        $checkParams = $this->getFilmCheckParams();
        $newFilmParams = $this->createFilmParams();
        $response = $this->request('film_create', $newFilmParams, 'POST');
        $this->checkResponse($response, $testKeysID, $checkParams, true);

        // 3. Check response params
        $testKeysID = 'check response data after successful creating film';
        $responseData = $response->getData();
        unset($newFilmParams['poster']);
        unset($responseData['poster']);
        $film = $this->em->getRepository(Film::class)->findOneById($responseData['id']);
        $this->assertNotNull($film, sprintf('Test case "%s" filed: can not find the film #%s', $testKeysID, $responseData['id']));
        $this->assertEquals(true, $responseData['isMy'], sprintf('Test case "%s" failed: param "isMy" must be equals to true but it\'s not', $testKeysID));
        unset($responseData['id'], $responseData['isMy'], $responseData['countries']);

        $responseData['genres'] = array_map(function ($item) { return $item['id']; }, $responseData['genres']);
        $responseData['companies'] = array_map(function ($item) { return $item['id']; }, $responseData['companies']);
        $responseData['directors'] = array_map(function ($item) { return $item['id']; }, $responseData['directors']);
        $responseData['actors'] = array_map(function ($item) { return $item['id']; }, $responseData['actors']);
        $responseData['producers'] = array_map(function ($item) { return $item['id']; }, $responseData['producers']);
        $responseData['writers'] = array_map(function ($item) { return $item['id']; }, $responseData['writers']);
        $responseData['premiums'] = array_map(function ($item) { return $item['id']; }, $responseData['premiums']);
        $responseData['languages'] = array_map(function ($item) { return $item['id']; }, $responseData['languages']);

        $this->assertEquals($responseData, $newFilmParams, sprintf('Test case "%s" failed: the response params are not equals to request data', $testKeysID));

        // 3. Try to create new film without description and premiums
        $testKeysID = 'create film without description and premiums';
        $newFilmParams = $this->createFilmParams();
        $newFilmParams['description'] = null;
        $newFilmParams['premiums'] = [];
        $response = $this->request('film_create', $newFilmParams, 'POST');
        $this->checkResponse($response, $testKeysID, $checkParams, true);

        // 4. Try to create film without poster
        $testKeysID = 'create film without poster';
        $phrase = '"poster" is required';
        $params = $newFilmParams;
        unset($params['poster']);
        $response = $this->request('film_create', $params, 'POST');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);

        $params['poster'] = null;
        $response = $this->request('film_create', $params, 'POST');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);

        $params['poster'] = [];
        $response = $this->request('film_create', $params, 'POST');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);

        // 5. Try to create film with incorrect params
        $this->makeIncorrectRequest('film_create', $newFilmParams, 'POST');
    }

    public function testUpdate()
    {
        // 1. Login User
        $this->logInAsUser();
        $user = $this->user;

        // 2. Get film
        $film = clone($this->getUserFilm($user));

        // 4. Try to update user film
        $testKeysID = 'update user film';
        $filmParams = $this->createFilmParams();
        $checkParams = $this->getFilmCheckParams();
        $response = $this->request(['film_update', ['id' => $film->getId()]], $filmParams, 'PUT');
        $this->checkResponse($response, $testKeysID, $checkParams, true);

        // 5. Check response params
        $testKeysID = 'check response data after successful updating user film';
        $responseData = $response->getData();
        $this->assertEquals(true, $responseData['isMy'], sprintf('Test case "%s" failed: param "isMy" must be equals to true but it\'s not', $testKeysID));
        /** @var Film $updatedFilm */
        $updatedFilm = $this->em->getRepository(Film::class)->findOneById($film->getId());
        $this->compareResponseAndDbFilms($responseData, $updatedFilm, $testKeysID);

        // 7. New film data mustn't be equals to new data, let's check it
        $testKeysID = 'compare old data and new data after successful updating user film';
        $this->assertNotEquals($film->getName(), $updatedFilm->getName(), sprintf('Test case "%s" failed: name before updating and after updating must not be equals', $testKeysID));
        $this->assertNotEquals($film->getDescription(), $updatedFilm->getDescription(), sprintf('Test case "%s" failed: description before updating and after updating must not be equals', $testKeysID));

        // 8. Try to update film without description and premiums
        $testKeysID = 'update film without description and premiums';
        $filmParams['description'] = null;
        $filmParams['premiums'] = [];
        $response = $this->request(['film_update', ['id' => $film->getId()]], $filmParams, 'PUT');
        $this->checkResponse($response, $testKeysID, $checkParams, true);

        // 9. Try to update film without poster
        $testKeysID = 'update film without poster';
        $phrase = '"poster" is required';
        $params = $filmParams;
        unset($params['poster']);
        $response = $this->request(['film_update', ['id' => $film->getId()]], $params, 'PUT');
        $this->checkResponse($response, $testKeysID, $checkParams, true);

        $params['poster'] = null;
        $response = $this->request(['film_update', ['id' => $film->getId()]], $params, 'PUT');
        $this->checkResponse($response, $testKeysID, $checkParams, true);

        $params['poster'] = [];
        $response = $this->request(['film_update', ['id' => $film->getId()]], $params, 'PUT');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);

        // 10. Try to update film with incorrect params
        $this->makeIncorrectRequest(['film_update', ['id' => $film->getId()]], $filmParams, 'PUT');
    }

    public function testDelete()
    {
        // 1. Login User
        $this->logInAsUser();
        $user = $this->user;

        // 2. Get film
        $film = clone($this->getUserFilm($user));

        // 3. Delete user's file
        $testKeysID = 'delete user file';
        $response = $this->request(['film_delete', ['id' => $film->getId()]], [], 'DELETE');
        $this->assertEquals(Response::HTTP_OK, $response->getStatus(), sprintf('Test keys "%s" failed: response status is %s', $testKeysID, $response->getStatus()));

        // 4. Check is deleted film exists in DB
        $testKeysID = 'try to find deleted film in DB';
        $existedFilm = $this->em->getRepository(Film::class)->findOneById($film->getId());
        $this->assertNull($existedFilm, sprintf('Test keys "%s" failed: film #%s already exists', $testKeysID, $film->getId()));

        // 5. Check is deleted film can be got by API
        $testKeysID = 'try to get deleted film by API';
        $response = $this->request(['film_view', ['id' => $film->getId()]], [], 'GET');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_NOT_FOUND, 'object not found');

        // 6. Try to delete not user's film
        $testKeysID = 'try to delete ton user film';
        $notUserFilm = $this->getNotUserFilm($user);
        $response = $this->request(['film_delete', ['id' => $notUserFilm->getId()]], [], 'DELETE');
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_FORBIDDEN, 'access denied');
    }

    private function getUserFilm(User $user): Film
    {
        $repository = $this->em->getRepository(Film::class);
        $count = $repository->createQueryBuilder('f')
            ->select('COUNT(f)')
            ->andWhere('f.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult()
        ;
        return $repository
            ->createQueryBuilder('f')
            ->andWhere('f.user = :user')
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->setFirstResult($this->faker->numberBetween(0, $count -1))
            ->getQuery()
            ->getSingleResult()
        ;
    }

    private function getNotUserFilm(User $user): Film
    {
        $repository = $this->em->getRepository(Film::class);
        $count = $repository->createQueryBuilder('f')
            ->select('COUNT(f)')
            ->andWhere('f.user != :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult()
        ;
        return $repository
            ->createQueryBuilder('f')
            ->andWhere('f.user != :user')
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->setFirstResult($this->faker->numberBetween(0, $count -1))
            ->getQuery()
            ->getSingleResult()
        ;
    }

    private function createFilmParams($params = [], $skipAttributes = [])
    {
        if (!isset($params['name']) && !in_array('name', $skipAttributes)) {
            $params['name'] = $this->faker->name . ' ' . $this->faker->name;
            if ($this->faker->numberBetween(1, 3) === 1) {
                $params['name'] .= ' ' . $params['name'];
            }
        }
        if (!isset($params['description']) && !in_array('description', $skipAttributes)) {
            $params['description'] = $this->faker->text;
        }
        if (!isset($params['poster']) && !in_array('poster', $skipAttributes)) {
            $imageFile = $this->getRandomImage();
            $params['poster'] = [
                'name' => basename($imageFile),
                'data' => base64_encode(file_get_contents($imageFile)),
            ];
        }
        if (!isset($params['genres']) && !in_array('genres', $skipAttributes)) {
            $items = $this->getRandomEntities(Genre::class, $this->faker->numberBetween(1, 4), $this->faker->numberBetween(0, 18));
            $params['genres'] = array_map(function (Genre $item) { return $item->getId(); }, $items);
        }
        if (!isset($params['companies']) && !in_array('companies', $skipAttributes)) {
            $items = $this->getRandomEntities(Company::class, $this->faker->numberBetween(1, 4), $this->faker->numberBetween(0, 18));
            $params['companies'] = array_map(function (Company $item) { return $item->getId(); }, $items);
        }
        if (!isset($params['directors']) && !in_array('directors', $skipAttributes)) {
            $items = $this->getRandomEntities(Director::class, $this->faker->numberBetween(1, 4), $this->faker->numberBetween(0, 18));
            $params['directors'] = array_map(function (Director $item) { return $item->getId(); }, $items);
        }
        if (!isset($params['actors']) && !in_array('actors', $skipAttributes)) {
            $items = $this->getRandomEntities(Actor::class, $this->faker->numberBetween(1, 4), $this->faker->numberBetween(0, 18));
            $params['actors'] = array_map(function (Actor $item) { return $item->getId(); }, $items);
        }
        if (!isset($params['producers']) && !in_array('producers', $skipAttributes)) {
            $items = $this->getRandomEntities(Producer::class, $this->faker->numberBetween(1, 4), $this->faker->numberBetween(0, 18));
            $params['producers'] = array_map(function (Producer $item) { return $item->getId(); }, $items);
        }
        if (!isset($params['writers']) && !in_array('writers', $skipAttributes)) {
            $items = $this->getRandomEntities(Writer::class, $this->faker->numberBetween(1, 4), $this->faker->numberBetween(0, 18));
            $params['writers'] = array_map(function (Writer $item) { return $item->getId(); }, $items);
        }
        if (!isset($params['premiums']) && !in_array('premiums', $skipAttributes)) {
            $items = $this->getRandomEntities(Premium::class, $this->faker->numberBetween(1, 4), $this->faker->numberBetween(0, 18));
            $params['premiums'] = array_map(function (Premium $item) { return $item->getId(); }, $items);
        }
        if (!isset($params['budget']) && !in_array('budget', $skipAttributes)) {
            $params['budget'] = $this->faker->numberBetween(100, 100000000);
        }
        if (!isset($params['sales']) && !in_array('sales', $skipAttributes)) {
            $params['sales'] = $this->faker->numberBetween(100, 100000000);
        }
        if (!isset($params['languages']) && !in_array('languages', $skipAttributes)) {
            $items = $this->getRandomEntities(Language::class, $this->faker->numberBetween(1, 4), $this->faker->numberBetween(0, 18));
            $params['languages'] = array_map(function (Language $item) { return $item->getId(); }, $items);
        }
        if (!isset($params['date']) && !in_array('date', $skipAttributes)) {
            $params['date'] = $this->faker->date();
        }
        if (!isset($params['duration']) && !in_array('duration', $skipAttributes)) {
            $params['duration'] = $this->faker->numberBetween(60, 340);
        }
        if (!isset($params['slogan']) && !in_array('slogan', $skipAttributes)) {
            $params['slogan'] = $this->faker->text(230);
        }
        if (!isset($params['rating']) && !in_array('rating', $skipAttributes)) {
            $params['rating'] = $this->faker->randomFloat(1, 0, 10);
        }

        return $params;
    }

    private function getFilmCheckParams()
    {
        return [
            'id' => 'integer',
            'name' => 'string',
            'description' => ['string', 'null'],
            'poster' => 'string',
            'genres' => 'array',
            'countries' => 'array',
            'companies' => 'array',
            'directors' => 'array',
            'actors' => 'array',
            'producers' => 'array',
            'writers' => 'array',
            'premiums' => 'array',
            'budget' => 'integer',
            'sales' => 'integer',
            'languages' => 'array',
            'date' => 'string',
            'duration' => 'integer',
            'isMy' => 'boolean',
            'slogan' => ['string', 'null'],
            'rating' => ['float', 'integer'],
        ];
    }

    private function getRandomImage()
    {
        $imageName = sprintf('test_image_%s.jpg', $this->faker->numberBetween(1, 5));
        return $this->getFilePath($imageName);
    }

    private function compareResponseAndDbFilms(array $responseData, Film $film, $testKeysID)
    {
        $responseDataGenres = array_map(function ($item) { return $item['id']; }, $responseData['genres']);
        $responseDataCompanies = array_map(function ($item) { return $item['id']; }, $responseData['companies']);
        $responseDataDirectors = array_map(function ($item) { return $item['id']; }, $responseData['directors']);
        $responseDataActors = array_map(function ($item) { return $item['id']; }, $responseData['actors']);
        $responseDataProducers = array_map(function ($item) { return $item['id']; }, $responseData['producers']);
        $responseDataWriters = array_map(function ($item) { return $item['id']; }, $responseData['writers']);
        $responseDataPremiums = array_map(function ($item) { return $item['id']; }, $responseData['premiums']);
        $responseDataLanguages = array_map(function ($item) { return $item['id']; }, $responseData['languages']);

        $dbDataGenres = array_map(function (ListedEntityInterface $item) { return $item->getId(); }, $film->getGenres()->toArray());
        $dbDataCompanies = array_map(function (ListedEntityInterface $item) { return $item->getId(); }, $film->getCompanies()->toArray());
        $dbDataDirectors = array_map(function (ListedEntityInterface $item) { return $item->getId(); }, $film->getDirectors()->toArray());
        $dbDataActors = array_map(function (ListedEntityInterface $item) { return $item->getId(); }, $film->getActors()->toArray());
        $dbDataProducers = array_map(function (ListedEntityInterface $item) { return $item->getId(); }, $film->getProducers()->toArray());
        $dbDataWriters = array_map(function (ListedEntityInterface $item) { return $item->getId(); }, $film->getWriters()->toArray());
        $dbDataPremiums = array_map(function (ListedEntityInterface $item) { return $item->getId(); }, $film->getPremiums()->toArray());
        $dbDataLanguages = array_map(function (ListedEntityInterface $item) { return $item->getId(); }, $film->getLanguages()->toArray());

        $this->assertEquals($responseDataGenres, array_values($dbDataGenres), sprintf('Test case "%s" failed: the response genres are not equals to DB genres', $testKeysID));
        $this->assertEquals($responseDataCompanies, array_values($dbDataCompanies), sprintf('Test case "%s" failed: the response companies are not equals to DB companies', $testKeysID));
        $this->assertEquals($responseDataDirectors, array_values($dbDataDirectors), sprintf('Test case "%s" failed: the response directors are not equals to DB directors', $testKeysID));
        $this->assertEquals($responseDataActors, array_values($dbDataActors), sprintf('Test case "%s" failed: the response actors are not equals to DB actors', $testKeysID));
        $this->assertEquals($responseDataProducers, array_values($dbDataProducers), sprintf('Test case "%s" failed: the response producers are not equals to DB producers', $testKeysID));
        $this->assertEquals($responseDataWriters, array_values($dbDataWriters), sprintf('Test case "%s" failed: the response writers are not equals to DB writers', $testKeysID));
        $this->assertEquals($responseDataPremiums, array_values($dbDataPremiums), sprintf('Test case "%s" failed: the response premiums are not equals to DB premiums', $testKeysID));
        $this->assertEquals($responseDataLanguages, array_values($dbDataLanguages), sprintf('Test case "%s" failed: the response languages are not equals to DB languages', $testKeysID));

        $this->assertEquals($responseData['id'], $film->getId(), sprintf('Test case "%s" failed: the response id is not equals to DB id', $testKeysID));
        $this->assertEquals($responseData['name'], $film->getName(), sprintf('Test case "%s" failed: the response name is not equals to DB name', $testKeysID));
        $this->assertEquals($responseData['poster'], $this->getParam('images_web_path') . '/' . $film->getPoster(), sprintf('Test case "%s" failed: the response poster is not equals to DB poster', $testKeysID));
        $this->assertEquals($responseData['description'], $film->getDescription(), sprintf('Test case "%s" failed: the response description is not equals to DB description', $testKeysID));
        $this->assertEquals($responseData['budget'], $film->getBudget(), sprintf('Test case "%s" failed: the response budget is not equals to DB budget', $testKeysID));
        $this->assertEquals($responseData['sales'], $film->getSales(), sprintf('Test case "%s" failed: the response sales is not equals to DB sales', $testKeysID));
        $this->assertEquals($responseData['date'], $this->formatDate($film->getDate()), sprintf('Test case "%s" failed: the response date is not equals to DB date', $testKeysID));
    }

    private function makeIncorrectRequest($url, $filmParams, $method)
    {
        $testKeysID = 'save film without name';
        $phrase = '"name" is required';
        $params = $filmParams;
        unset($params['name']);
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['name'] = null;
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['name'] = '';
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['name'] = [1, 2, 3];
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, '"name" must have a type string');

        $testKeysID = 'save film without poster';
        $params = $filmParams;
        $params['poster'] = 'film_poser.jpg';
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, '"poster" must have a type array');

        $params['poster'] = ['name' => 'film_poser.jpg'];
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, 'string-params "name" and "data"');

        $params['poster'] = ['data' => 'somerandomdata'];
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, 'string-params "name" and "data"');

        $params['poster'] = ['name' => '', 'data' => $filmParams['poster']['data']];
        $phrase = $method === 'POST' ? 'string-params "name" and "data"' : 'unsupported file format';
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);

        $params['poster'] = ['name' => 'test_image.err', 'data' => $filmParams['poster']['data']];
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, 'unsupported file format: err');

        $params['poster'] = ['name' => 'film_poser.jpg', 'data' => ''];
        $phrase = $method === 'POST' ? 'string-params "name" and "data"' : 'invalid poster image data';
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);

        $params['poster'] = ['name' => 'film_poser.jpg', 'data' => 'somerandomdata'];
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, 'invalid poster image data');

        $testKeysID = 'save film without genres';
        $phrase = '"genres" is required';
        $params = $filmParams;
        unset($params['genres']);
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['genres'] = null;
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['genres'] = [];
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['genres'] = 'some random string';
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, '"genres" must have a type array');

        $testKeysID = 'save film without companies';
        $phrase = '"companies" is required';
        $params = $filmParams;
        unset($params['companies']);
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['companies'] = null;
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['companies'] = [];
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['companies'] = 'some random string';
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, '"companies" must have a type array');

        $testKeysID = 'save film without directors';
        $phrase = '"directors" is required';
        $params = $filmParams;
        unset($params['directors']);
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['directors'] = null;
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['directors'] = [];
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['directors'] = 'some random string';
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, '"directors" must have a type array');

        $testKeysID = 'save film without actors';
        $phrase = '"actors" is required';
        $params = $filmParams;
        unset($params['actors']);
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['actors'] = null;
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['actors'] = [];
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['actors'] = 'some random string';
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, '"actors" must have a type array');

        $testKeysID = 'save film without producers';
        $phrase = '"producers" is required';
        $params = $filmParams;
        unset($params['producers']);
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['producers'] = null;
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['producers'] = [];
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['producers'] = 'some random string';
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, '"producers" must have a type array');

        $testKeysID = 'save film without writers';
        $phrase = '"writers" is required';
        $params = $filmParams;
        unset($params['writers']);
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['writers'] = null;
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['writers'] = [];
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['writers'] = 'some random string';
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, '"writers" must have a type array');

        $testKeysID = 'save film without budget';
        $phrase = '"budget" is required';
        $params = $filmParams;
        unset($params['budget']);
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['budget'] = null;
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['budget'] = [1, 2, 3];
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, '"budget" must have a type integer');
        $params['budget'] = 'some random string';
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, '"budget" must have a type integer');

        $testKeysID = 'save film without sales';
        $phrase = '"sales" is required';
        $params = $filmParams;
        unset($params['sales']);
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['sales'] = null;
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['sales'] = [1, 2, 3];
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, '"sales" must have a type integer');
        $params['sales'] = 'some random string';
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, '"sales" must have a type integer');

        $testKeysID = 'save film without languages';
        $phrase = '"languages" is required';
        $params = $filmParams;
        unset($params['languages']);
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['languages'] = null;
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['languages'] = [];
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['languages'] = 'some random string';
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, '"languages" must have a type array');

        $testKeysID = 'save film without date';
        $phrase = '"date" is required';
        $params = $filmParams;
        unset($params['date']);
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['date'] = null;
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['date'] = [1, 2, 3];
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, '"date" must have a type string');
        $params['date'] = 'some random string';
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, 'invalid date format');

        $testKeysID = 'save film without duration';
        $phrase = '"duration" is required';
        $params = $filmParams;
        unset($params['duration']);
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['duration'] = null;
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, $phrase);
        $params['duration'] = [1, 2, 3];
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, '"duration" must have a type integer');
        $params['duration'] = 'some random string';
        $response = $this->request($url, $params, $method);
        $this->checkIncorrectResponse($response, $testKeysID, Response::HTTP_BAD_REQUEST, '"duration" must have a type integer');
    }
}