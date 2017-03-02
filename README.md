[![Build Status](https://travis-ci.org/makasim/values.png?branch=master)](https://travis-ci.org/makasim/values)

# Your "alter ego" objects

The approach tries to gather the best from array and object worlds. 
So, with the library you can deal with object as you are used to but internally everything is stored into array.
The array is easy to fetch out or set into of the object.

Could be used:

* with [MongoDB](https://www.mongodb.com/) as lightweight (yet powerful) ODM.
* with API by working with object but able to get arrays.
* with message queues same as API.

**An object** provide us with a contract which is easy to rely on. We can type hint a class, use auto complete on its methods.
That's a good part, but it is not easy or cheap to populate objects with data or take their current state. 
We have provide various tools like serializers, transformers and so on. Things are getting even worse when we have to deal with object trees.

**An array** on the other hand is easy store or send. Whenever you call an api or do a query to database you end up working with an array. 
That's a strong side but it does not gives any contract and code could be easily broken when array structure changes.

## Examples

### Get repository info example 

Let's use [Github API](https://developer.github.com/v3/repos/#list-organization-repositories) and get info about [Symfony](https://github.com/symfony/symfony) repository. 
Here's the [real response](https://api.github.com/repos/symfony/symfony), we will use a shortened version.

```php
<?php
namespace Acme;

use Makasim\Values\ValuesTrait;
use Makasim\Values\ObjectsTrait;
use function Makasim\Values\set_values;

class Repo
{
    use ValuesTrait;
    use ObjectsTrait;

    public function getStargazersCount()
    {
        return $this->getValue('stargazers_count');
    }

    /** @return Owner */
    public function getOwner()
    {
        return $this->getObject('owner', Owner::class);
    }
}

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

$data = json_decode('
{
  "id":458058,
  "name":"symfony",
  "full_name":"symfony/symfony",
  "owner":{
    "login":"symfony",
    "id":143937,
  },
  "stargazers_count":13945,
}
', true);

$repo = new Repo();
set_values($repo, $data);

$repo->getStargazersCount(); // 13945
$repo->getOwner()->getLogin(); // symfony
```

### Create new gist

Now, let's create a new gist. 
According to [Github API](https://developer.github.com/v3/gists/#create-a-gist) we have to send an object with files collection.
Lets create Gist and GistFile object. Populate them with data and get it as array.

```php
<?php
namespace  Acme;

use Makasim\Values\ObjectsTrait;
use Makasim\Values\ValuesTrait;

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

class GistFile
{
    use ValuesTrait;

    public function __construct($content)
    {
        $this->setValue('content', $content);
    }
}

$file = new GistFile('String file contents');

$gist = new Gist();
$gist->setDescription('the description for this gist');
$gist->setPublic(true);
$gist->addFile('file1.txt', $file);

get_values($gist);

/*
[
    'description' => 'the description for this gist',
    'public' => true,
    'files' => [
        'file1.txt' => [
            'content' => 'String file contents',
        ]
    ]
]
 */

// now you can send it to api. 
```

## License

It is released under the [MIT License](LICENSE).
