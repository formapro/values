<?php
namespace Makasim\Values\Tests;

use function Makasim\Values\add_value;
use function Makasim\Values\get_registered_hooks;
use function Makasim\Values\get_value;
use function Makasim\Values\register_hook;
use function Makasim\Values\set_value;
use function Makasim\Values\set_values;
use Makasim\Values\Tests\Model\Object;
use PHPUnit\Framework\TestCase;

class HookTest extends TestCase
{
    public function testShouldRegisterHookToObject()
    {
        $obj = new Object();

        // TODO remove later when migration is done
        (function() { $this->hooks = []; })->call($obj);

        $hook = function () {
        };

        register_hook($obj, 'aHook', $hook);

        self::assertAttributeSame([
            'aHook' => [$hook],
        ], 'hooks', $obj);
    }

    public function testShouldRegisterSeveralHooksToObject()
    {
        $obj = new Object();

        // TODO remove later when migration is done
        (function() { $this->hooks = []; })->call($obj);

        $callback = function () {
        };
        $anotherCallback = function () {
        };

        register_hook($obj, 'aHook', $callback);
        register_hook($obj, 'aHook', $anotherCallback);

        self::assertAttributeSame([
            'aHook' => [$callback, $anotherCallback],
        ], 'hooks', $obj);
    }

    public function testShouldRegisterSeveralHooksForDifferentFunctionsToObject()
    {
        $obj = new Object();

        // TODO remove later when migration is done
        (function() { $this->hooks = []; })->call($obj);

        $callback = function () {
        };
        $anotherCallback = function () {
        };

        register_hook($obj, 'aFooHook', $callback);
        register_hook($obj, 'aBarHook', $anotherCallback);

        self::assertAttributeSame([
            'aFooHook' => [$callback],
            'aBarHook' => [$anotherCallback],
        ], 'hooks', $obj);
    }

    public function testShouldRegisterSeveralHooksToSeveralObjects()
    {
        $fooObj = new Object();
        $barObj = new Object();

        // TODO remove later when migration is done
        (function() { $this->hooks = []; })->call($fooObj);
        (function() { $this->hooks = []; })->call($barObj);

        $callback = function () {
        };
        $anotherCallback = function () {
        };

        register_hook($fooObj, 'aHook', $callback);
        register_hook($barObj, 'aHook', $anotherCallback);

        self::assertAttributeSame(['aHook' => [$callback]], 'hooks', $fooObj);
        self::assertAttributeSame(['aHook' => [$anotherCallback]], 'hooks', $barObj);
    }

    public function testShouldReturnEmptyArrayIfNoCallbacksRegisteredForSuchHook()
    {
        $obj = new Object();

        // TODO remove later when migration is done
        (function() { $this->hooks = []; })->call($obj);

        //guard
        $this->assertAttributeSame([], 'hooks', $obj);

        $this->assertSame([], get_registered_hooks($obj, 'aHook'));
    }

    public function testShouldReturnRegisteredHooks()
    {
        $obj = new Object();

        // TODO remove later when migration is done
        (function() { $this->hooks = []; })->call($obj);

        //guard
        $this->assertAttributeSame([], 'hooks', $obj);

        $callback = function () {
        };
        $anotherCallback = function () {
        };

        register_hook($obj, 'aHook', $callback);
        register_hook($obj, 'aHook', $anotherCallback);

        $this->assertSame([$callback, $anotherCallback], get_registered_hooks($obj, 'aHook'));
    }

    public function testShouldCallPostSetValuesCallbackOnPostSetValues()
    {
        $obj = new Object();

        $isCalled = false;
        $values = ['foo' => 'bar'];

        register_hook($obj, 'post_set_values', function() use ($obj, $values, &$isCalled) {
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

        $isCalled = false;
        $values = ['foo' => 'bar'];

        register_hook($obj, 'post_set_values', function() use ($obj, $values, &$isCalled) {
            $isCalled = true;

            self::assertTrue(func_get_arg(2));
        });

        set_values($obj, $values, true);
        self::assertTrue($isCalled);
    }

    public function testShouldCallPreAddValueCallbackOnAddValue()
    {
        $obj = new Object();

        $isCalled = false;
        $value = 'bar';
        $key = 'foo';

        register_hook($obj, 'pre_add_value', function() use ($obj, $key, $value, &$isCalled) {
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

        $isCalled = false;
        $value = 'bar';
        $key = 'foo';

        register_hook($obj, 'pre_add_value', function() use (&$isCalled) {
            $isCalled = true;

            return 'baz';
        });

        add_value($obj, $key, $value);
        self::assertTrue($isCalled);

        self::assertSame('baz', get_value($obj,'foo.0'));
    }

    /**
     * @group d
     */
    public function testShouldCallPostAddValueCallbackOnAddValue()
    {
        $obj = new Object();

        $isCalled = false;
        $value = 'bar';
        $key = 'foo';

        register_hook($obj, 'post_add_value', function() use ($obj, $key, $value, &$isCalled) {
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

        $isCalled = false;
        $value = 'bar';
        $valueKey = 'valKey';
        $key = 'foo';

        register_hook($obj, 'post_add_value', function() use ($key, $valueKey, &$isCalled) {
            $isCalled = true;

            self::assertSame($key.'.'.$valueKey, func_get_arg(1));
        });

        add_value($obj, $key, $value, $valueKey);
        self::assertTrue($isCalled);
    }

    public function testShouldCallPreSetValueCallbackOnSetValue()
    {
        $obj = new Object();

        $isCalled = false;
        $value = 'bar';
        $key = 'foo';

        register_hook($obj, 'pre_set_value', function() use ($obj, $key, $value, &$isCalled) {
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

        $isCalled = false;
        $value = 'bar';
        $key = 'foo';

        register_hook($obj, 'pre_set_value', function() use (&$isCalled) {
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

        $isCalled = false;
        $value = 'bar';
        $key = 'foo';

        register_hook($obj, 'post_set_value', function() use ($obj, $key, $value, &$isCalled) {
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
        $isCalled = false;
        $value = 'bar';
        $key = 'foo';

        $obj = new Object();
        set_value($obj, $key, $value);

        register_hook($obj, 'post_get_value', function() use ($obj, $key, $value, &$isCalled) {
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
        $isCalled = false;

        $obj = new Object();
        set_value($obj, 'foo', 'bar');

        register_hook($obj, 'post_get_value', function() use (&$isCalled) {
            $isCalled = true;

            return 'baz';
        });

        self::assertSame('baz', get_value($obj, 'foo'));
        self::assertTrue($isCalled);
    }
}
