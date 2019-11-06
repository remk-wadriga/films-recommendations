<?php

namespace App\Helpers\Web;

class HtmlReader extends AbstractWebReader
{
    protected function getData(string $source, string $method): string
    {
        if (preg_match("/^.+\.(html)$/", $source)) {
            return file_get_contents($source);
        } else {
            $resp = $this->httpClient->request($method, $source);
            return $resp->getContent();
        }
    }
}