<?php
namespace Formapro\Values\Tests\Model\Github;

use Formapro\Values\ValuesTrait;

class GistFile
{
    use ValuesTrait;

    public function __construct($content)
    {
        $this->setValue('content', $content);
    }
}