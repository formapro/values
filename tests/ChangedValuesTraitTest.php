<?php
namespace Formapro\Values\Tests;

use function Formapro\Values\clone_object;
use function Formapro\Values\set_values;
use Formapro\Values\Tests\Model\EmptyObject;
use PHPUnit\Framework\TestCase;

class ChangedValuesTraitTest extends TestCase
{
    public function testShouldNotTrackValuesSetViaAllowSetValuesAndGetPreviouslySet()
    {
        $values = ['foo' => 'fooVal', 'bar' => ['bar1' => 'bar1Val', 'bar2' => 'bar2Val']];

        $obj = new EmptyObject();

        //guard
        self::assertChangedValuesSame([], $obj);

        set_values($obj, $values);

        self::assertChangedValuesSame([], $obj);
    }

    public function testShouldTrackSetNewValue()
    {
        $obj = new EmptyObject();
        $obj->setValue('aKey', 'aVal');

        self::assertChangedValuesSame(['aKey' => 'aVal'], $obj);

        $obj = new EmptyObject();
        $obj->setValue('aNamespace.aKey', 'aVal');

        self::assertChangedValuesSame(['aNamespace' => ['aKey' => 'aVal']], $obj);

        $obj = new EmptyObject();
        $obj->setValue('aNamespace.4', 'aVal');

        self::assertChangedValuesSame(['aNamespace' => [4 => 'aVal']], $obj);
    }

    public function testShouldResetChangedValuesOnSetValues()
    {
        $obj = new EmptyObject();
        $obj->setValue('aKey', 'aVal');

        self::assertChangedValuesSame(['aKey' => 'aVal'], $obj);

        $values = ['bar' => 'barVal'];
        set_values($obj, $values);
        self::assertChangedValuesSame([], $obj);
    }

    public function testShouldTrackValueUnset()
    {
        $obj = new EmptyObject();
        $obj->setValue('aName.aKey', 'aVal');

        self::assertChangedValuesSame(['aName' => ['aKey' => 'aVal']], $obj);

        $obj->setValue('aName.aKey', null);

        self::assertChangedValuesSame(['aName' => ['aKey' => null]], $obj);
    }

    public function testShouldTrackValueAddedToEmptyArray()
    {
        $obj = new EmptyObject();
        $obj->addValue('aNamespace.aKey', 'aVal');

        self::assertChangedValuesSame(['aNamespace' => ['aKey' => ['aVal']]], $obj);
    }

    public function testShouldTrackValueAddedToExistingArray()
    {
        $values = ['aNamespace' => ['aKey' => ['aVal']]];

        $obj = new EmptyObject();
        set_values($obj, $values);
        $obj->addValue('aNamespace.aKey', 'aNewVal');

        self::assertChangedValuesSame(['aNamespace' => ['aKey' => [1 => 'aNewVal']]], $obj);
    }

    public function testShouldNotReflectChangesOnClonedObject()
    {
        $obj = new EmptyObject();
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