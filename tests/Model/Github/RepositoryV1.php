<?php
namespace Formapro\Values\Tests\Model\Github;

use Formapro\Values\ValuesTrait;

class RepositoryV1
{
    use ValuesTrait;

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

    public function getOwnerId()
    {
        return $this->getValue('owner.id');
    }

    public function getOwnerLogin()
    {
        return $this->getValue('owner.login');
    }
}
