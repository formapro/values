<?php
namespace Formapro\Values\Tests\Model;

use Formapro\Values\CastTrait;
use function Formapro\Values\register_cast_hooks;

class CastableObject extends EmptyObject
{
    use CastTrait;

    public function __construct()
    {
        parent::__construct();

        register_cast_hooks($this);
    }
}
