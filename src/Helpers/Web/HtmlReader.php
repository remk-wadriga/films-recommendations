<?php


namespace App\Helpers\Web;


class HtmlReader extends AbstractWebReader
{
    public function read($source)
    {
        return file_get_contents($source);
    }
}