<?php
namespace Makasim\Values\Tests\Model;

use Makasim\Values\CastTrait;
use function Makasim\Values\register_cast_hooks;

class CastableObject extends Object
{
    use CastTrait;

    public function __construct()
    {
        parent::__construct();

        register_cast_hooks($this);
    }
}
