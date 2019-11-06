<?php


namespace App\Helpers;


class StringHelper
{
    public static function mb_ucfirst(string $str, string $encoding = "UTF-8", bool $lowerStrEnd = false): string
    {
        $firstLetter = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);
        if ($lowerStrEnd) {
            $strEnd = mb_strtolower(mb_substr($str, 1, mb_strlen($str, $encoding), $encoding), $encoding);
        } else {
            $strEnd = mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
        }
        return $firstLetter . $strEnd;
    }
}