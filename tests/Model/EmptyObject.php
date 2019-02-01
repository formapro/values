<?php
namespace Formapro\Values\Tests\Model;

use Formapro\Values\ChangedValuesTrait;
use Formapro\Values\ObjectsTrait;
use Formapro\Values\ValuesTrait;

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
