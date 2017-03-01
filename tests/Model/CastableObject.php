<?php
namespace Makasim\Values\Tests\Model;

use Makasim\Values\CastTrait;

class CastableObject extends Object
{
    use CastTrait;

    public function __construct()
    {
        parent::__construct();

        $this->registerCastHooks();
    }
}
