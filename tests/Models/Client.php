<?php

namespace EscolaLms\HeadlessH5P\Tests\Models;

class Client extends \Laravel\Passport\Client
{
    public function getIdAttribute()
    {
        return $this->attributes['id'];
    }
}
