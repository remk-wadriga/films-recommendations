<?php

namespace App\Tests\Web;

use App\Tests\AbstractWebTestCase;

class FilmControllerTest extends AbstractWebTestCase
{
    public function testList()
    {
        // 1. Login User
        $this->logInAsUser();
        $user = $this->user;

        // 2. Get films list and check response
        $checkParams = [
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
        ];
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

        // 5 Check is first element of "limited" request and the first element of request with limit and offset are not equals
        $testKeysID = 'check the difference between first elements of "limited" request and request with limit and offset';
        $this->assertNotEquals($oldFirstFilm['id'], $newFirstFilm['id'], sprintf('Test case "%s" failed: elements area equals', $testKeysID));

        // 5 Check is last element of "limited" request and the last element of request with limit and offset are not equals
        $testKeysID = 'check the difference between last elements of "limited" request and request with limit and offset';
        $this->assertNotEquals($oldLastFilm['id'], $newLastFilm['id'], sprintf('Test case "%s" failed: elements area equals', $testKeysID));

        // 7. Check is last element of "limited" request and the first element of request with limit and offset are not equals
        $testKeysID = 'check the difference between last element of "limited" request and the first element of request with limit and offset';
        $this->assertNotEquals($oldLastFilm['id'], $newFirstFilm['id'], sprintf('Test case "%s" failed: elements area equals', $testKeysID));
    }
}