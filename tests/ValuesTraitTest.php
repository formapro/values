<?php
namespace Makasim\Values\Tests;

use function Makasim\Values\clone_object;
use function Makasim\Values\get_object_changed_values;
use function Makasim\Values\get_object_values;
use function Makasim\Values\set_object_values;
use Makasim\Values\ValuesTrait;
use PHPUnit\Framework\TestCase;

class ValuesTraitTest extends TestCase
{
    public function testShouldAllowSetValuesAndGetPreviouslySet()
    {
        $values = ['foo' => 'fooVal', 'bar' => ['bar1' => 'bar1Val', 'bar2' => 'bar2Val']];

        $obj = new ValueTest();

        set_object_values($obj, $values);

        self::assertSame($values, get_object_values($obj));
        self::assertSame([], get_object_changed_values($obj));
    }

    public function testShouldAllowSetValueAndGetPreviouslySet()
    {
        $obj = new ValueTest();
        $obj->setValue('aNamespace.aKey', 'aVal');

        self::assertSame('aVal', $obj->getValue('aNamespace.aKey'));
        self::assertSame(['aNamespace' => ['aKey' => 'aVal']], get_object_values($obj));
        self::assertSame(['aNamespace' => ['aKey' => 'aVal']], get_object_changed_values($obj));
    }

    public function testShouldAllowGetDefaultValueIfNotSet()
    {
        $obj = new ValueTest();

        self::assertSame('aDefaultVal', $obj->getValue('aNamespace.aKey', 'aDefaultVal'));

        $obj->setValue('aNamespace.aKey', 'aVal');

        self::assertSame('aVal', $obj->getValue('aNamespace.aKey', 'aDefaultVal'));
    }

    public function testShouldAllowSetSelfValueAndGetPreviouslySet()
    {
        $obj = new ValueTest();
        $obj->setValue('self.aKey', 'aVal');

        self::assertSame('aVal', $obj->getValue('self.aKey'));
        self::assertSame(['self' => ['aKey' => 'aVal']], get_object_values($obj));
        self::assertSame(['self' => ['aKey' => 'aVal']], get_object_changed_values($obj));
    }

    public function testShouldResetChangedValuesWhenValuesSet()
    {
        $obj = new ValueTest();
        $obj->setValue('aNamespace.aKey', 'aVal');

        self::assertSame(['aNamespace' => ['aKey' => 'aVal']], get_object_values($obj));

        $values = ['bar' => 'barVal'];
        set_object_values($obj, $values);
        self::assertSame([], get_object_changed_values($obj));
    }

    public function testShouldAllowUnsetPreviouslySetValue()
    {
        $obj = new ValueTest();
        $obj->setValue('aName.aKey', 'aVal');

        self::assertSame('aVal', $obj->getValue('aName.aKey'));
        self::assertSame(['aName' => ['aKey' => 'aVal']], get_object_values($obj));
        self::assertSame(['aName' => ['aKey' => 'aVal']], get_object_changed_values($obj));
        
        $obj->setValue('aName.aKey', null);
        
        self::assertSame(null, $obj->getValue('aName.aKey'));
        self::assertSame(['aName' => []], get_object_values($obj));
        self::assertSame(['aName' => ['aKey' => null]], get_object_changed_values($obj));
    }

    public function testShouldAllowAddValueToEmptyArray()
    {
        $obj = new ValueTest();
        $obj->addValue('aNamespace.aKey', 'aVal');

        self::assertSame(['aVal'], $obj->getValue('aNamespace.aKey'));
        self::assertSame(['aNamespace' => ['aKey' => ['aVal']]], get_object_values($obj));
        self::assertSame(['aNamespace' => ['aKey' => ['aVal']]], get_object_changed_values($obj));
    }

    public function testShouldAllowAddValueToAlreadyArray()
    {
        $values = ['aNamespace' => ['aKey' => ['aVal']]];

        $obj = new ValueTest();
        set_object_values($obj, $values);
        $obj->addValue('aNamespace.aKey', 'aVal');

        self::assertSame(['aVal', 'aVal'], $obj->getValue('aNamespace.aKey'));
        self::assertSame(['aNamespace' => ['aKey' => ['aVal', 'aVal']]], get_object_values($obj));
        self::assertSame(['aNamespace' => ['aKey' => ['aVal', 'aVal']]], get_object_changed_values($obj));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Cannot set value to aNamespace.aKey it is already set and not array
     */
    public function testThrowsIfAddValueToExistOneWhichNotArray()
    {
        $values = ['aNamespace' => ['aKey' => 'aVal']];

        $obj = new ValueTest();
        set_object_values($obj, $values);

        $obj->addValue('aNamespace.aKey', 'aVal');
    }

    public function testShouldNotReflectChangesOnClonedObject()
    {
        $obj = new ValueTest();
        $obj->setValue('aNamespace.aKey', 'foo');

        $clonedObj = clone_object($obj);
        $clonedObj->setValue('aNamespace.aKey', 'bar');

        self::assertSame('foo', $obj->getValue('aNamespace.aKey'));
        self::assertSame('bar', $clonedObj->getValue('aNamespace.aKey'));
    }
}

class ValueTest
{
    use ValuesTrait {
        getValue as public;
        setValue as public;
        addValue as public;
    }
}