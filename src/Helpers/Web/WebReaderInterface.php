<?php


namespace App\Helpers\Web;


interface WebReaderInterface
{
    public function read(string $source, string $method = "GET");
}