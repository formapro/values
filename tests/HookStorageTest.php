<?php
namespace Makasim\Values\Tests;

use function Makasim\Values\add_object;
use function Makasim\Values\add_value;
use function Makasim\Values\build_object;
use function Makasim\Values\build_object_ref;
use function Makasim\Values\get_object;
use function Makasim\Values\get_value;
use Makasim\Values\HookStorage;
use function Makasim\Values\set_object;
use function Makasim\Values\set_objects;
use function Makasim\Values\set_value;
use function Makasim\Values\set_values;
use Makasim\Values\Tests\Model\Object;
use Makasim\Values\Tests\Model\SubObject;
use PHPUnit\Framework\TestCase;

class HookStorageTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        HookStorage::clearAll();
    }

    protected function tearDown()
    {
        parent::tearDown();

        HookStorage::clearAll();
    }

    public function testShouldRegisterHookForObject()
    {
        $obj = new Object();

        HookStorage::clearAll();

        $callback = function () {};

        HookStorage::register($obj, 'aHook', $callback);

        $hookId = HookStorage::getHookId($obj);
        self::assertNotEmpty($hookId);

        self::assertSame([
            'aHook' => [
                $hookId => [
                    spl_object_hash($callback) => $callback
                ]
            ]
        ],HookStorage::getAll());
    }

    public function testShouldRegisterSeveralHooksForObject()
    {
        $obj = new Object();

        HookStorage::clearAll();

        $callback = function () {};
        $anotherCallback = function () {};

        HookStorage::register($obj, 'aHook', $callback);
        HookStorage::register($obj, 'aHook', $anotherCallback);

        $hookId = HookStorage::getHookId($obj);
        self::assertNotEmpty($hookId);

        self::assertSame([
            'aHook' => [
                $hookId => [
                    spl_object_hash($callback) => $callback,
                    spl_object_hash($anotherCallback) => $anotherCallback,
                ]
            ]
        ],HookStorage::getAll());
    }

    public function testShouldRegisterSeveralHooksForDifferentFunctionsToObject()
    {
        $obj = new Object();

        HookStorage::clearAll();

        $callback = function () {};
        $anotherCallback = function () {};

        HookStorage::register($obj, 'aFooHook', $callback);
        HookStorage::register($obj, 'aBarHook', $anotherCallback);

        $hookId = HookStorage::getHookId($obj);
        self::assertNotEmpty($hookId);

        self::assertSame([
            'aFooHook' => [
                $hookId => [
                    spl_object_hash($callback) => $callback,
                ],
            ],
            'aBarHook' => [
                $hookId => [
                    spl_object_hash($anotherCallback) => $anotherCallback,
                ],
            ]
        ],HookStorage::getAll());
    }

    public function testShouldRegisterSeveralHooksToSeveralObjects()
    {
        $fooObj = new Object();
        $barObj = new Object();

        HookStorage::clearAll();

        $callback = function () {};
        $anotherCallback = function () {};

        HookStorage::register($fooObj, 'aHook', $callback);
        HookStorage::register($barObj, 'aHook', $anotherCallback);

        $fooHookId = HookStorage::getHookId($fooObj);
        self::assertNotEmpty($fooHookId);

        $barHookId = HookStorage::getHookId($barObj);
        self::assertNotEmpty($barHookId);

        self::assertNotSame($fooHookId, $barHookId);

        self::assertSame([
            'aHook' => [
                $fooHookId => [
                    spl_object_hash($callback) => $callback,
                ],
                $barHookId => [
                    spl_object_hash($anotherCallback) => $anotherCallback,
                ],
            ]
        ],HookStorage::getAll());
    }

    public function testShouldRegisterHookForClass()
    {
        $callback = function () {};

        HookStorage::register(Object::class, 'aHook', $callback);

        self::assertSame([
            'aHook' => [
                Object::class => [
                    spl_object_hash($callback) => $callback
                ]
            ]
        ],HookStorage::getAll());
    }

    public function testShouldRegisterSeveralHooksForClass()
    {
        $callback = function () {};
        $anotherCallback = function () {};

        HookStorage::register(Object::class, 'aHook', $callback);
        HookStorage::register(Object::class, 'aHook', $anotherCallback);

        self::assertSame([
            'aHook' => [
                Object::class => [
                    spl_object_hash($callback) => $callback,
                    spl_object_hash($anotherCallback) => $anotherCallback,
                ]
            ]
        ],HookStorage::getAll());
    }

    public function testShouldRegisterSeveralHooksForDifferentFunctionsToClass()
    {
        $callback = function () {};
        $anotherCallback = function () {};

        HookStorage::register(Object::class, 'aFooHook', $callback);
        HookStorage::register(Object::class, 'aBarHook', $anotherCallback);

        self::assertSame([
            'aFooHook' => [
                Object::class => [
                    spl_object_hash($callback) => $callback,
                ],
            ],
            'aBarHook' => [
                Object::class => [
                    spl_object_hash($anotherCallback) => $anotherCallback,
                ],
            ]
        ],HookStorage::getAll());
    }

    public function testShouldRegisterSeveralHooksToSeveralClasses()
    {
        $callback = function () {};
        $anotherCallback = function () {};

        HookStorage::register(Object::class, 'aHook', $callback);
        HookStorage::register(\stdClass::class, 'aHook', $anotherCallback);

        self::assertSame([
            'aHook' => [
                Object::class => [
                    spl_object_hash($callback) => $callback,
                ],
                \stdClass::class => [
                    spl_object_hash($anotherCallback) => $anotherCallback,
                ],
            ]
        ],HookStorage::getAll());
    }

    public function testShouldReturnEmptyArrayIfNoCallbacksRegisteredForSuchObjectAndHook()
    {
        $obj = new Object();

        HookStorage::clearAll();

        $hooks = HookStorage::get($obj, 'aHook');
        self::assertInstanceOf(\Generator::class, $hooks);
        self::assertSame([], iterator_to_array($hooks));
    }

    public function testShouldReturnRegisteredHooks()
    {
        $obj = new Object();

        HookStorage::clearAll();

        $callback = function () {};
        $anotherCallback = function () {};

        HookStorage::register($obj, 'aHook', $callback);
        HookStorage::register($obj, 'aHook', $anotherCallback);

        $hooks = HookStorage::get($obj, 'aHook');
        self::assertInstanceOf(\Generator::class, $hooks);
        self::assertSame([$callback, $anotherCallback], iterator_to_array($hooks));
    }

    public function testShouldReturnRegisteredHooksIncludingOnesForClass()
    {
        $obj = new Object();

        HookStorage::clearAll();

        $callback = function () {};
        $anotherCallback = function () {};

        HookStorage::register($obj, 'aHook', $callback);
        HookStorage::register(Object::class, 'aHook', $anotherCallback);

        $hooks = HookStorage::get($obj, 'aHook');
        self::assertInstanceOf(\Generator::class, $hooks);
        self::assertSame([$anotherCallback, $callback], iterator_to_array($hooks));
    }

    public function testShouldReturnRegisteredHooksIncludingGlobalOnes()
    {
        $obj = new Object();

        HookStorage::clearAll();

        $callback = function () {};
        $anotherCallback = function () {};

        HookStorage::register($obj, 'aHook', $callback);
        HookStorage::registerGlobal('aHook', $anotherCallback);

        $hooks = HookStorage::get($obj, 'aHook');
        self::assertInstanceOf(\Generator::class, $hooks);
        self::assertSame([$anotherCallback, $callback], iterator_to_array($hooks));
    }

    public function testShouldCallPostSetValuesCallbackOnPostSetValues()
    {
        $obj = new Object();

        HookStorage::clearAll();

        $isCalled = false;
        $values = ['foo' => 'bar'];

        HookStorage::register($obj, 'post_set_values', function() use ($obj, $values, &$isCalled) {
            $isCalled = true;

            self::assertSame($obj, func_get_arg(0));
            self::assertSame($values, func_get_arg(1));
            self::assertFalse(func_get_arg(2));
        });

        set_values($obj, $values);
        self::assertTrue($isCalled);
    }

    public function testShouldCallPostSetValueCallbackOnSetValuesAndPassByReferenceArgument()
    {
        $obj = new Object();

        HookStorage::clearAll();

        $isCalled = false;
        $values = ['foo' => 'bar'];

        HookStorage::register($obj, 'post_set_values', function() use ($obj, $values, &$isCalled) {
            $isCalled = true;

            self::assertTrue(func_get_arg(2));
        });

        set_values($obj, $values, true);
        self::assertTrue($isCalled);
    }

    public function testShouldCallPreAddValueCallbackOnAddValue()
    {
        $obj = new Object();

        HookStorage::clearAll();

        $isCalled = false;
        $value = 'bar';
        $key = 'foo';

        HookStorage::register($obj, 'pre_add_value', function() use ($obj, $key, $value, &$isCalled) {
            $isCalled = true;

            self::assertSame($obj, func_get_arg(0));
            self::assertSame($key, func_get_arg(1));
            self::assertSame($value, func_get_arg(2));
        });

        add_value($obj, $key, $value);
        self::assertTrue($isCalled);

        self::assertSame('bar', get_value($obj,'foo.0'));
    }

    public function testShouldAllowModifyValueInPreAddValueCallback()
    {
        $obj = new Object();

        HookStorage::clearAll();

        $isCalled = false;
        $value = 'bar';
        $key = 'foo';

        HookStorage::register($obj, 'pre_add_value', function() use (&$isCalled) {
            $isCalled = true;

            return 'baz';
        });

        add_value($obj, $key, $value);
        self::assertTrue($isCalled);

        self::assertSame('baz', get_value($obj,'foo.0'));
    }

    public function testShouldCallPostAddValueCallbackOnAddValue()
    {
        $obj = new Object();

        HookStorage::clearAll();

        $isCalled = false;
        $value = 'bar';
        $key = 'foo';

        HookStorage::register($obj, 'post_add_value', function() use ($obj, $key, $value, &$isCalled) {
            $isCalled = true;

            self::assertSame($obj, func_get_arg(0));
            self::assertSame($key.'.0', func_get_arg(1));
            self::assertSame($value, func_get_arg(2));
            self::assertTrue(func_get_arg(3));
        });

        add_value($obj, $key, $value);
        self::assertTrue($isCalled);
    }

    public function testShouldCallPostAddValueCallbackOnAddValueWithCustomValueKey()
    {
        $obj = new Object();

        HookStorage::clearAll();

        $isCalled = false;
        $value = 'bar';
        $valueKey = 'valKey';
        $key = 'foo';

        HookStorage::register($obj, 'post_add_value', function() use ($key, $valueKey, &$isCalled) {
            $isCalled = true;

            self::assertSame($key.'.'.$valueKey, func_get_arg(1));
        });

        add_value($obj, $key, $value, $valueKey);
        self::assertTrue($isCalled);
    }

    public function testShouldCallPreSetValueCallbackOnSetValue()
    {
        $obj = new Object();

        HookStorage::clearAll();

        $isCalled = false;
        $value = 'bar';
        $key = 'foo';

        HookStorage::register($obj, 'pre_set_value', function() use ($obj, $key, $value, &$isCalled) {
            $isCalled = true;

            self::assertSame($obj, func_get_arg(0));
            self::assertSame($key, func_get_arg(1));
            self::assertSame($value, func_get_arg(2));
        });

        set_value($obj, $key, $value);
        self::assertTrue($isCalled);

        self::assertSame('bar', get_value($obj,'foo'));
    }

    public function testShouldAllowModifyValueInPreSetValueCallback()
    {
        $obj = new Object();

        HookStorage::clearAll();

        $isCalled = false;
        $value = 'bar';
        $key = 'foo';

        HookStorage::register($obj, 'pre_set_value', function() use (&$isCalled) {
            $isCalled = true;

            return 'baz';
        });

        set_value($obj, $key, $value);
        self::assertTrue($isCalled);

        self::assertSame('baz', get_value($obj,'foo'));
    }

    public function testShouldCallPostSetValueCallbackOnSetValue()
    {
        $obj = new Object();

        HookStorage::clearAll();

        $isCalled = false;
        $value = 'bar';
        $key = 'foo';

        HookStorage::register($obj, 'post_set_value', function() use ($obj, $key, $value, &$isCalled) {
            $isCalled = true;

            self::assertSame($obj, func_get_arg(0));
            self::assertSame($key, func_get_arg(1));
            self::assertSame($value, func_get_arg(2));
            self::assertTrue(func_get_arg(3));
        });

        set_value($obj, $key, $value);
        self::assertTrue($isCalled);
    }

    public function testShouldCallPostGetValueCallbackOnGetValue()
    {
        $obj = new Object();

        HookStorage::clearAll();

        $isCalled = false;
        $value = 'bar';
        $key = 'foo';

        set_value($obj, $key, $value);

        HookStorage::register($obj, 'post_get_value', function() use ($obj, $key, $value, &$isCalled) {
            $isCalled = true;

            self::assertSame($obj, func_get_arg(0));
            self::assertSame($key, func_get_arg(1));
            self::assertSame($value, func_get_arg(2));
            self::assertSame('aDefaultValue', func_get_arg(3));
            self::assertSame('aCastTo', func_get_arg(4));
        });

        self::assertSame('bar', get_value($obj, $key, 'aDefaultValue', 'aCastTo'));
        self::assertTrue($isCalled);
    }

    public function testShouldAllowModifyValueInPostGetValueCallback()
    {
        $obj = new Object();

        HookStorage::clearAll();

        $isCalled = false;

        set_value($obj, 'foo', 'bar');

        HookStorage::register($obj, 'post_get_value', function() use (&$isCalled) {
            $isCalled = true;

            return 'baz';
        });

        self::assertSame('baz', get_value($obj, 'foo'));
        self::assertTrue($isCalled);
    }

    public function testShouldCallPostBuildObjectCallbackOnBuildObject()
    {
        $values = [];

        $isCalled = false;
        $actualObj = null;

        HookStorage::register(Object::class, 'post_build_object', function() use (&$actualObj, &$isCalled) {
            $isCalled = true;

            $actualObj = func_get_arg(0);
        });

        $obj = build_object(Object::class, $values);

        self::assertTrue($isCalled);
        self::assertSame($obj, $actualObj);
    }

    public function testShouldCallPostBuildObjectCallbackOnBuildObjectWithContext()
    {
        $parentObj = new Object();

        $values = [];

        $isCalled = false;
        $actualObj = null;

        HookStorage::register(Object::class, 'post_build_sub_object', function() use ($parentObj, &$actualObj, &$isCalled) {
            $isCalled = true;

            $actualObj = func_get_arg(0);
            self::assertSame($parentObj, func_get_arg(1));
            self::assertSame('aParentKey', func_get_arg(2));
        });

        $obj = build_object_ref(Object::class, $values, $parentObj, 'aParentKey');

        self::assertTrue($isCalled);
        self::assertSame($obj, $actualObj);
    }

    public function testShouldCallPostSetObjectCallbackOnSetObject()
    {
        $obj = new Object();
        $subObj = new SubObject();

        $isCalled = false;
        $actualObj = null;

        HookStorage::register($obj, 'post_set_object', function() use ($subObj, $obj, &$isCalled) {
            $isCalled = true;

            self::assertSame($subObj, func_get_arg(0));
            self::assertSame($obj, func_get_arg(1));
            self::assertSame('aKey', func_get_arg(2));
        });

        set_object($obj, 'aKey', $subObj);

        self::assertTrue($isCalled);
    }

    public function testShouldCallPostAddObjectCallbackOnAddObject()
    {
        $obj = new Object();
        $subObj = new SubObject();

        $isCalled = false;
        $actualObj = null;

        HookStorage::register($obj, 'post_add_object', function() use ($subObj, $obj, &$isCalled) {
            $isCalled = true;

            self::assertSame($subObj, func_get_arg(0));
            self::assertSame($obj, func_get_arg(1));
            self::assertSame('aKey.0', func_get_arg(2));
        });

        add_object($obj, 'aKey', $subObj);

        self::assertTrue($isCalled);
    }

    public function testShouldCallPostSetObjectCallbackOnSetObjects()
    {
        $obj = new Object();
        $subObj = new SubObject();

        $isCalled = false;
        $actualObj = null;

        HookStorage::register($obj, 'post_set_object', function() use ($subObj, $obj, &$isCalled) {
            $isCalled = true;

            self::assertSame($subObj, func_get_arg(0));
            self::assertSame($obj, func_get_arg(1));
            self::assertSame('aKey.0', func_get_arg(2));
        });

        set_objects($obj, 'aKey', [$subObj]);

        self::assertTrue($isCalled);
    }

    public function testShouldCallGetObjectClassOnGetObjectIfClassOrClosureArgumentNotProvided()
    {
        $values = [
            'aKey' => [
                'aSubKey' => 'aFooVal',
            ],
        ];

        $obj = new Object();
        set_values($obj, $values);

        $isCalled = false;
        $actualObj = null;

        HookStorage::register('build_object', 'get_object_class', function() use ($obj, &$isCalled) {
            $isCalled = true;

            self::assertSame(['aSubKey' => 'aFooVal'], func_get_arg(0));
            self::assertSame($obj, func_get_arg(1));
            self::assertSame('aKey', func_get_arg(2));

            return SubObject::class;
        });

        get_object($obj, 'aKey');

        self::assertTrue($isCalled);
    }
}
