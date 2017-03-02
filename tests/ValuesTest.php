<?php
namespace Makasim\Values\Tests;

use function Makasim\Values\add_value;
use function Makasim\Values\clone_object;
use function Makasim\Values\get_value;
use function Makasim\Values\get_values;
use function Makasim\Values\set_value;
use function Makasim\Values\set_values;
use Makasim\Values\Tests\Model\Object;
use PHPUnit\Framework\TestCase;

class ValuesTest extends TestCase
{
    public function testShouldAllowSetValuesAndGetPreviouslySet()
    {
        $values = ['foo' => 'fooVal', 'bar' => ['bar1' => 'bar1Val', 'bar2' => 'bar2Val']];

        $obj = new Object();

        set_values($obj, $values);

        self::assertSame($values, get_values($obj));
    }

    public function testShouldAllowSetNewValueAndGetPreviouslySet()
    {
        $obj = new Object();
        set_value($obj, 'aKey', 'aVal');

        self::assertSame('aVal', get_value($obj, 'aKey'));
        self::assertSame(['aKey' => 'aVal'], get_values($obj));
    }

    public function testShouldAllowSetNewNameSpacedValueAndGetPreviouslySet()
    {
        $obj = new Object();
        set_value($obj, 'aNamespace.aKey', 'aVal');

        self::assertSame('aVal', get_value($obj, 'aNamespace.aKey'));
        self::assertSame(['aNamespace' => ['aKey' => 'aVal']], get_values($obj));
    }

    public function testShouldAllowGetDefaultValueIfSimpleValueNotSet()
    {
        $obj = new Object();

        self::assertSame('aDefaultVal', get_value($obj, 'aKey', 'aDefaultVal'));

        set_value($obj, 'aKey', 'aVal');

        self::assertSame('aVal', get_value($obj, 'aKey', 'aDefaultVal'));
    }

    public function testShouldAllowGetDefaultValueIfNameSpacedValueNotSet()
    {
        $obj = new Object();

        self::assertSame('aDefaultVal', get_value($obj, 'aNamespace.aKey', 'aDefaultVal'));

        set_value($obj, 'aNamespace.aKey', 'aVal');

        self::assertSame('aVal', get_value($obj, 'aNamespace.aKey', 'aDefaultVal'));
    }

    public function testShouldResetChangedValuesOnSetValues()
    {
        $obj = new Object();
        set_value($obj, 'aNamespace.aKey', 'aVal');

        self::assertSame(['aNamespace' => ['aKey' => 'aVal']], get_values($obj));

        $values = ['bar' => 'barVal'];
        set_values($obj, $values);

        self::assertSame(['bar' => 'barVal'], get_values($obj));
    }

    public function testShouldAllowUnsetPreviouslySetSimpleValue()
    {
        $obj = new Object();
        set_value($obj, 'aKey', 'aVal');
        set_value($obj, 'anotherKey', 'anotherVal');

        self::assertSame('aVal', get_value($obj, 'aKey'));
        self::assertSame(['aKey' => 'aVal', 'anotherKey' => 'anotherVal'], get_values($obj));

        set_value($obj, 'aKey', null);

        self::assertSame(null, get_value($obj, 'aKey'));
        self::assertSame(['anotherKey' => 'anotherVal'], get_values($obj));
    }

    public function testShouldAllowUnsetPreviouslySetNameSpacedValue()
    {
        $obj = new Object();
        set_value($obj, 'aName.aKey', 'aVal');
        set_value($obj, 'anotherName.aKey', 'anotherVal');

        self::assertSame('aVal', get_value($obj, 'aName.aKey'));
        self::assertSame(
            [
                'aName' => ['aKey' => 'aVal'],
                'anotherName' => ['aKey' => 'anotherVal'],
            ],
            get_values($obj)
        );

        set_value($obj, 'aName.aKey', null);

        self::assertSame(null, get_value($obj, 'aName.aKey'));
        self::assertSame(
            [
                'aName' => [],
                'anotherName' => ['aKey' => 'anotherVal'],
            ],
            get_values($obj)
        );
    }

    public function testShouldAllowAddSimpleValueToEmptyArray()
    {
        $obj = new Object();
        add_value($obj, 'aKey', 'aVal');

        self::assertSame(['aVal'], get_value($obj, 'aKey'));
        self::assertSame(['aKey' => ['aVal']], get_values($obj));
    }

    public function testShouldAllowAddSeveralSimpleValuesToEmptyArray()
    {
        $obj = new Object();
        add_value($obj, 'aKey', 'foo');
        add_value($obj, 'aKey', 'bar');
        add_value($obj, 'aKey', 'baz', 'customKey');
        add_value($obj, 'aKey', 'ololo');

        self::assertSame(
            [
                'aKey' => [
                    0 => 'foo',
                    1 => 'bar',
                    'customKey' => 'baz',
                    2 => 'ololo',
                ]
            ],
            get_values($obj)
        );
    }

    public function testAddValueReturnsAddedValueKey()
    {
        $obj = new Object();

        self::assertSame(0, add_value($obj, 'aKey', 'aVal'));
        self::assertSame('customKey', add_value($obj, 'aKey', 'aVal', 'customKey'));
        self::assertSame(1, add_value($obj, 'aKey', 'aVal'));
    }

    public function testShouldAllowAddNameSpacedValueToEmptyArray()
    {
        $obj = new Object();
        add_value($obj, 'aNamespace.aKey', 'aVal');

        self::assertSame(['aVal'], get_value($obj, 'aNamespace.aKey'));
        self::assertSame(['aNamespace' => ['aKey' => ['aVal']]], get_values($obj));
    }

    public function testShouldAllowAddSimpleValueToExistingArray()
    {
        $values = ['aKey' => ['aVal']];

        $obj = new Object();
        set_values($obj, $values);

        add_value($obj, 'aKey', 'aNewVal');

        self::assertSame(['aVal', 'aNewVal'], get_value($obj, 'aKey'));
        self::assertSame(['aKey' => ['aVal', 'aNewVal']], get_values($obj));
    }

    public function testShouldAllowAddNameSpacedValueToExistingArray()
    {
        $values = ['aNamespace' => ['aKey' => ['aVal']]];

        $obj = new Object();
        set_values($obj, $values);

        add_value($obj, 'aNamespace.aKey', 'aNewVal');

        self::assertSame(['aVal', 'aNewVal'], get_value($obj, 'aNamespace.aKey'));
        self::assertSame(['aNamespace' => ['aKey' => ['aVal', 'aNewVal']]], get_values($obj));
    }

    public function testShouldAllowAddValueWithCustomValueKeyThatContainsDot()
    {
        $obj = new Object();

        add_value($obj, 'aKey', 'aVal', 'valueKey.withDot');

        self::assertSame(['valueKey.withDot' => 'aVal'], get_value($obj, 'aKey'));
        self::assertSame(['aKey' => ['valueKey.withDot' => 'aVal']], get_values($obj));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Cannot set value to aNamespace.aKey it is already set and not array
     */
    public function testThrowsIfAddValueButExistingValueIsNotArray()
    {
        $values = ['aNamespace' => ['aKey' => 'aVal']];

        $obj = new Object();
        set_values($obj, $values);

        add_value($obj, 'aNamespace.aKey', 'aVal');
    }

    public function testShouldNotReflectChangesOnClonedObject()
    {
        $obj = new Object();
        set_value($obj, 'aNamespace.aKey', 'foo');

        $clonedObj = clone_object($obj);
        set_value($clonedObj, 'aNamespace.aKey', 'bar');

        self::assertSame('foo', get_value($obj, 'aNamespace.aKey'));
        self::assertSame('bar', get_value($clonedObj, 'aNamespace.aKey'));
    }
}
