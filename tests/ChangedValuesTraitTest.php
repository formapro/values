<?php
namespace Makasim\Values\Tests;

use function Makasim\Values\clone_object;
use function Makasim\Values\set_values;
use Makasim\Values\Tests\Model\Object;
use PHPUnit\Framework\TestCase;

class ChangedValuesTraitTest extends TestCase
{
    public function testShouldNotTrackValuesSetViaAllowSetValuesAndGetPreviouslySet()
    {
        $values = ['foo' => 'fooVal', 'bar' => ['bar1' => 'bar1Val', 'bar2' => 'bar2Val']];

        $obj = new Object();

        //guard
        self::assertChangedValuesSame([], $obj);

        set_values($obj, $values);

        self::assertChangedValuesSame([], $obj);
    }

    public function testShouldTrackSetNewValue()
    {
        $obj = new Object();
        $obj->setValue('aKey', 'aVal');

        self::assertChangedValuesSame(['aKey' => 'aVal'], $obj);

        $obj = new Object();
        $obj->setValue('aNamespace.aKey', 'aVal');

        self::assertChangedValuesSame(['aNamespace' => ['aKey' => 'aVal']], $obj);

        $obj = new Object();
        $obj->setValue('aNamespace.4', 'aVal');

        self::assertChangedValuesSame(['aNamespace' => [4 => 'aVal']], $obj);
    }

    public function testShouldResetChangedValuesOnSetValues()
    {
        $obj = new Object();
        $obj->setValue('aKey', 'aVal');

        self::assertChangedValuesSame(['aKey' => 'aVal'], $obj);
        
        $values = ['bar' => 'barVal'];
        set_values($obj, $values);
        self::assertChangedValuesSame([], $obj);
    }

    public function testShouldTrackValueUnset()
    {
        $obj = new Object();
        $obj->setValue('aName.aKey', 'aVal');

        self::assertChangedValuesSame(['aName' => ['aKey' => 'aVal']], $obj);

        $obj->setValue('aName.aKey', null);

        self::assertChangedValuesSame(['aName' => ['aKey' => null]], $obj);
    }

    public function testShouldTrackValueAddedToEmptyArray()
    {
        $obj = new Object();
        $obj->addValue('aNamespace.aKey', 'aVal');

        self::assertChangedValuesSame(['aNamespace' => ['aKey' => ['aVal']]], $obj);
    }

    public function testShouldTrackValueAddedToExistingArray()
    {
        $values = ['aNamespace' => ['aKey' => ['aVal']]];

        $obj = new Object();
        set_values($obj, $values);
        $obj->addValue('aNamespace.aKey', 'aNewVal');

        self::assertChangedValuesSame(['aNamespace' => ['aKey' => [1 => 'aNewVal']]], $obj);
    }

    public function testShouldNotReflectChangesOnClonedObject()
    {
        $obj = new Object();
        $obj->setValue('aKey', 'foo');

        $clonedObj = clone_object($obj);
        $clonedObj->setValue('aKey', 'bar');

        self::assertChangedValuesSame(['aKey' => 'foo'], $obj);
        self::assertChangedValuesSame(['aKey' => 'bar'], $clonedObj);
    }
    
    private static function assertChangedValuesSame($expected, $object)
    {
        self::assertAttributeSame($expected, 'changedValues', $object);
    }
}