<?php
namespace Formapro\Values\Tests\Model\Github;

use Formapro\Values\ObjectsTrait;
use Formapro\Values\ValuesTrait;

class Gist
{
    use ValuesTrait;
    use ObjectsTrait;

    public function setDescription($description)
    {
        $this->setValue('description', $description);
    }

    public function setPublic($bool)
    {
        $this->setValue('public', $bool);
    }

    public function addFile($fileName, GistFile $file)
    {
        $this->addObject('files', $file, $fileName);
    }
}
