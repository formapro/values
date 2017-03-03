<?php
namespace Makasim\Values\Tests;

use function Makasim\Values\add_object;
use function Makasim\Values\get_object;
use function Makasim\Values\get_objects;
use Makasim\Values\HookStorage;
use function Makasim\Values\register_propagate_root_hooks;
use function Makasim\Values\set_object;
use function Makasim\Values\set_objects;
use function Makasim\Values\set_values;
use Makasim\Values\Tests\Model\Object;
use Makasim\Values\Tests\Model\SubObject;
use PHPUnit\Framework\TestCase;

class PropagateRootObjectTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        HookStorage::clearAll();
    }

    public function tearDown()
    {
        parent::tearDown();

        HookStorage::clearAll();
    }

    public function testShouldSetRootObjectToSubObjectOnSetObject()
    {
        $subObj = new Object();
        $obj = new Object();

        register_propagate_root_hooks($obj);

        set_object($obj, 'aKey', $subObj);

        self::assertAttributeSame($obj, 'rootObject', $subObj);
        self::assertAttributeSame('aKey', 'rootObjectKey', $subObj);
    }

    public function testShouldSetRootObjectToEverySubObjectOnSetObjects()
    {
        $fooSubObj = new SubObject();
        $barSubObj = new SubObject();
        $obj = new Object();

        register_propagate_root_hooks($obj);

        set_objects($obj, 'aKey', [$fooSubObj, $barSubObj]);

        self::assertAttributeSame($obj, 'rootObject', $fooSubObj);
        self::assertAttributeSame('aKey.0', 'rootObjectKey', $fooSubObj);

        self::assertAttributeSame($obj, 'rootObject', $barSubObj);
        self::assertAttributeSame('aKey.1', 'rootObjectKey', $barSubObj);
    }

    public function testShouldSetRootObjectToAddedObjectOnAddObject()
    {
        $fooSubObj = new SubObject();
        $obj = new Object();

        register_propagate_root_hooks($obj);

        add_object($obj, 'aKey', $fooSubObj);

        self::assertAttributeSame($obj, 'rootObject', $fooSubObj);
        self::assertAttributeSame('aKey.0', 'rootObjectKey', $fooSubObj);
    }

    public function testShouldSetRootObjectToEverySubObjectOnGetObjects()
    {
        $obj = new Object();

        $values = [
            'aKey' => [
                [],
                [],
            ]
        ];
        set_values($obj, $values);

        register_propagate_root_hooks($obj);

        $subObjects = get_objects($obj, 'aKey', SubObject::class);

        //guard
        self::assertInstanceOf(\Generator::class, $subObjects);
        $subObjects = iterator_to_array($subObjects);
        self::assertCount(2, $subObjects);

        self::assertAttributeSame($obj, 'rootObject', $subObjects[0]);
        self::assertAttributeSame('aKey', 'rootObjectKey', $subObjects[0]);

        self::assertAttributeSame($obj, 'rootObject', $subObjects[1]);
        self::assertAttributeSame('aKey', 'rootObjectKey', $subObjects[1]);
    }

    public function testShouldSetRootObjectToSubObjectOnGetObject()
    {
        $obj = new Object();

        $values = [
            'aKey' => []
        ];
        set_values($obj, $values);

        register_propagate_root_hooks($obj);

        $subObject = get_object($obj, 'aKey', SubObject::class);

        self::assertAttributeSame($obj, 'rootObject', $subObject);
        self::assertAttributeSame('aKey', 'rootObjectKey', $subObject);
    }
}
