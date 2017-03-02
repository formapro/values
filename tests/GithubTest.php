<?php
namespace Makasim\Values\Tests;

use function Makasim\Values\get_values;
use function Makasim\Values\set_values;
use Makasim\Values\Tests\Model\Github\Gist;
use Makasim\Values\Tests\Model\Github\GistFile;
use Makasim\Values\Tests\Model\Github\Owner;
use Makasim\Values\Tests\Model\Github\RepositoryV1;
use Makasim\Values\Tests\Model\Github\RepositoryV2;
use PHPUnit\Framework\TestCase;

class GithubTest extends TestCase
{
    public function testOnlyValuesScenario()
    {
        $data = json_decode(file_get_contents(__DIR__.'/fixtures/github_repository_response.json'), true);

        $repo = new RepositoryV1();
        set_values($repo, $data);

        self::assertSame(458058, $repo->getId());
        self::assertSame('The Symfony PHP framework', $repo->getDescription());
        self::assertSame('symfony', $repo->getName());
        self::assertSame(13945, $repo->getStargazersCount());
        self::assertSame(5376, $repo->getForksCount());

        self::assertSame(143937, $repo->getOwnerId());
        self::assertSame('symfony', $repo->getOwnerLogin());
    }

    public function testSubObjectScenario()
    {
        $data = json_decode(file_get_contents(__DIR__.'/fixtures/github_repository_response.json'), true);

        $repo = new RepositoryV2();
        set_values($repo, $data);

        self::assertGreaterThan(1, $repo->getId());
        self::assertContains('framework', $repo->getDescription());
        self::assertSame('symfony', $repo->getName());
        self::assertGreaterThan(10000, $repo->getStargazersCount());
        self::assertGreaterThan(3000, $repo->getForksCount());

        $owner = $repo->getOwner();
        self::assertInstanceOf(Owner::class, $owner);
        self::assertSame(143937, $owner->getId());
        self::assertSame('symfony', $owner->getLogin());
    }

    public function testCreateGist()
    {
        $file = new GistFile('String file contents');
        $gist = new Gist();

        $gist->setDescription('the description for this gist');
        $gist->setPublic(true);
        $gist->addFile('file1.txt', $file);

        self::assertEquals([
            'description' => 'the description for this gist',
            'public' => true,
            'files' => [
                'file1.txt' => [
                    'content' => 'String file contents',
                ]
            ]
        ], get_values($gist));
    }
}
