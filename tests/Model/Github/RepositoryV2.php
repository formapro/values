<?php
namespace Formapro\Values\Tests\Model\Github;

use Formapro\Values\ObjectsTrait;
use Formapro\Values\ValuesTrait;

class RepositoryV2
{
    use ValuesTrait;
    use ObjectsTrait;

    public function getId()
    {
        return $this->getValue('id');
    }

    public function getName()
    {
        return $this->getValue('name');
    }

    public function getDescription()
    {
        return $this->getValue('description');
    }

    public function getStargazersCount()
    {
        return $this->getValue('stargazers_count');
    }

    public function getForksCount()
    {
        return $this->getValue('forks_count');
    }

    public function getOwner()
    {
        return $this->getObject('owner', Owner::class);
    }
}
