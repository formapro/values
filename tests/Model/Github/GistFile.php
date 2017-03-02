<?php
namespace Makasim\Values\Tests\Model\Github;

use Makasim\Values\ValuesTrait;

class GistFile
{
    use ValuesTrait;

    public function __construct($content)
    {
        $this->setValue('content', $content);
    }
}