<?php

namespace ArtisanSdk\RateLimiter\Tests\Stubs;

class User
{
    public $max = 100;
    public $rate = 10.0;
    public $duration = 1;
    protected $identifier;

    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    public function getAuthIdentifier()
    {
        return $this->identifier;
    }
}
