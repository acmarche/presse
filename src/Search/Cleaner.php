<?php

namespace AcMarche\Presse\Search;

class Cleaner { public static function cleandata($data): string
    {
        $data = preg_replace('#&nbsp;#', ' ', (string) $data);
        $data = preg_replace('#&amp;#', ' ', $data); //&
        $data = preg_replace('#&#', ' ', $data);
        $data = preg_replace('#<#', '', $data);
        $data = preg_replace('#’#', "'", $data);
        $data = preg_replace(["#\(#", "#\)#"], '', $data);
        $special_chars = [
            '?',
            '[',
            ']',
            '/',
            '\\',
            '=',
            '<',
            '>',
            ':',
            ';',
            ',',
            '"',
            '&',
            '$',
            '#',
            '*',
            '|',
            '~',
            '`',
            '!',
            '{',
            '}',
            \chr(0),
        ];
        $data = str_replace($special_chars, ' ', $data);

        return trim($data);
    }}