
<h2 align="center">Supporting Opensource</h2>

`formapro\values` is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become our client](http://forma-pro.com/)
- [Become a sponsor](https://www.patreon.com/makasim)

---


[![Build Status](https://travis-ci.org/makasim/values.png?branch=master)](https://travis-ci.org/makasim/values)


# Your "alter ego" objects

The approach tries to gather the best from array and object worlds. 
So, with the library you can deal with object as you are used to but internally everything is stored into array.
The array is easy to fetch out or set into of the object.

Could be used:

* interacting with [MongoDB](https://www.mongodb.com/) - [makasim/yadm](https://github.com/makasim/yadm).
* describing API clients - [formapro/telegram-bot](https://github.com/formapro/telegram-bot-php).
* describing domain models - [formapro/pvm](https://github.com/formapro/pvm)
* describing MQ messages.

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

use Formapro\Values\ValuesTrait;
use Formapro\Values\ObjectsTrait;
use function Formapro\Values\set_values;

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

## Developed by Forma Pro

Forma Pro is a senior product engineering company helping product organizations build and evolve complex, data-intensive software.

Our public open-source work reflects more than two decades of engineering history, including PHP/Symfony libraries, infrastructure tooling, workflow components, and messaging systems.

Today, our work spans marketplaces and platforms, partner and attribution systems, FinTech, logistics, EdTech, data-intensive workflows, matching and recommendation systems, and applied AI/ML.

Learn more at [forma-pro.com](https://forma-pro.com).

For questions about this project or our open-source work, contact us at opensource@forma-pro.com.

## License

It is released under the [MIT License](LICENSE).
