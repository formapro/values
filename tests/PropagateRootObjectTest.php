<?php
namespace Formapro\Values\Tests;

use function Formapro\Values\add_object;
use function Formapro\Values\get_object;
use function Formapro\Values\get_objects;
use Formapro\Values\HookStorage;
use function Formapro\Values\register_propagate_root_hooks;
use function Formapro\Values\set_object;
use function Formapro\Values\set_objects;
use function Formapro\Values\set_values;
use Formapro\Values\Tests\Model\EmptyObject;
use Formapro\Values\Tests\Model\SubObject;
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
        $subObj = new EmptyObject();
        $obj = new EmptyObject();

        register_propagate_root_hooks($obj);

        set_object($obj, 'aKey', $subObj);

        self::assertAttributeSame($obj, 'rootObject', $subObj);
        self::assertAttributeSame('aKey', 'rootObjectKey', $subObj);
    }

    public function testShouldSetRootObjectToEverySubObjectOnSetObjects()
    {
        $fooSubObj = new SubObject();
        $barSubObj = new SubObject();
        $obj = new EmptyObject();

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
        $obj = new EmptyObject();

        register_propagate_root_hooks($obj);

        add_object($obj, 'aKey', $fooSubObj);

        self::assertAttributeSame($obj, 'rootObject', $fooSubObj);
        self::assertAttributeSame('aKey.0', 'rootObjectKey', $fooSubObj);
    }

    public function testShouldSetRootObjectToEverySubObjectOnGetObjects()
    {
        $values = ['aKey' => [[], []]];
        $obj = new EmptyObject();

        set_values($obj, $values);

        register_propagate_root_hooks($obj);

        $subObjects = get_objects($obj, 'aKey', SubObject::class);

        //guard
        self::assertInstanceOf(\Generator::class, $subObjects);
        $subObjects = iterator_to_array($subObjects);
        self::assertCount(2, $subObjects);

        self::assertAttributeSame($obj, 'rootObject', $subObjects[0]);
        self::assertAttributeSame('aKey.0', 'rootObjectKey', $subObjects[0]);

        self::assertAttributeSame($obj, 'rootObject', $subObjects[1]);
        self::assertAttributeSame('aKey.1', 'rootObjectKey', $subObjects[1]);
    }

    public function testShouldSetRootObjectToSubObjectOnGetObject()
    {
        $obj = new EmptyObject();

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
