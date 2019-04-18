<?php

namespace App\Service;

class Slugger
{
    public function slugify(string $strToConvert)
    {
        return preg_replace('/[^a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*/', '-', 
        strtolower(trim(strip_tags($strToConvert))));
    }
}