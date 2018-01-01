<?php
namespace Makasim\Values\Tests\Model;

use Makasim\Values\ChangedValuesTrait;
use Makasim\Values\ObjectsTrait;
use Makasim\Values\ValuesTrait;

class EmptyObject
{
    use ValuesTrait {
        getValue as public;
        setValue as public;
        addValue as public;
    }

    use ObjectsTrait {
        setObject as public;
        getObject as public;
        setObjects as public;
        getObjects as public;
        addObject as public;
    }

    use ChangedValuesTrait;

    public function __construct()
    {
        $this->registerChangedValuesHooks();
    }
}
