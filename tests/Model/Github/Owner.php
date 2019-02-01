<?php
namespace Formapro\Values\Tests\Model\Github;

use Formapro\Values\ValuesTrait;

class Owner
{
    use ValuesTrait;

    public function getId()
    {
        return $this->getValue('id');
    }

    public function getLogin()
    {
        return $this->getValue('login');
    }
}
