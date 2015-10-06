<?php
namespace Makasim\ValuesORM\Tests;

use Makasim\ValuesORM\ValuesTrait;

class ValuesTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldAllowSetValuesAndGetPreviouslySet()
    {
        $values = ['foo' => 'fooVal', 'bar' => ['bar1' => 'bar1Val', 'bar2' => 'bar2Val']];

        $obj = new ValueTest();

        $obj->setValues($values);

        $this->assertSame($values, $obj->getValues());
        $this->assertSame([], $obj->getChangedValues());
    }

    public function testShouldAllowSetValueAndGetPreviouslySet()
    {
        $obj = new ValueTest();
        $obj->setValue('aNamespace', 'aKey', 'aVal');

        $this->assertSame('aVal', $obj->getValue('aNamespace', 'aKey'));
        $this->assertSame(['aNamespace' => ['aKey' => 'aVal']], $obj->getValues());
        $this->assertSame(['aNamespace' => ['aKey' => 'aVal']], $obj->getChangedValues());
    }

    public function testShouldAllowGetDefaultValueIfNotSet()
    {
        $obj = new ValueTest();

        $this->assertSame('aDefaultVal', $obj->getValue('aNamespace', 'aKey', 'aDefaultVal'));

        $obj->setValue('aNamespace', 'aKey', 'aVal');

        $this->assertSame('aVal', $obj->getValue('aNamespace', 'aKey', 'aDefaultVal'));
    }

    public function testShouldAllowSetSelfValueAndGetPreviouslySet()
    {
        $obj = new ValueTest();
        $obj->setSelfValue('aKey', 'aVal');

        $this->assertSame('aVal', $obj->getSelfValue('aKey'));
        $this->assertSame(['self' => ['aKey' => 'aVal']], $obj->getValues());
        $this->assertSame(['self' => ['aKey' => 'aVal']], $obj->getChangedValues());
    }

    public function testShouldResetChangedValuesWhenValuesSet()
    {
        $obj = new ValueTest();
        $obj->setValue('aNamespace', 'aKey', 'aVal');

        $this->assertSame(['aNamespace' => ['aKey' => 'aVal']], $obj->getValues());

        $obj->setValues(['bar' => 'barVal']);
        $this->assertSame([], $obj->getChangedValues());
    }

    public function testShouldAllowSetDateTimeValueAndGetPreviouslySet()
    {
        $now = new \DateTime('now');
        $timestamp = $now->format('U');

        $obj = new ValueTest();
        $obj->setValue('aNamespace', 'aKey', $now);

        $actualDate = $obj->getValue('aNamespace', 'aKey', null, 'date');
        $this->assertInstanceOf(\DateTime::class, $actualDate);
        $this->assertEquals($timestamp, $actualDate->format('U'));
    }

    public function testShouldAllowSetDateTimeValueASISOAndGetPreviouslySet()
    {
        $now = new \DateTime('now');
        $timestamp = $now->format('U');
        $iso = $now->format(DATE_ISO8601);

        $obj = new ValueTest();
        $obj->setValue('aNamespace', 'aKey', $now->format(DATE_ISO8601));

        $this->assertSame($iso, $obj->getValue('aNamespace', 'aKey'));

        $actualDate = $obj->getValue('aNamespace', 'aKey', null, 'date');
        $this->assertInstanceOf(\DateTime::class, $actualDate);
        $this->assertEquals($timestamp, $actualDate->format('U'));
    }

    public function testShouldAllowSetDateTimeValueASTimestampAndGetPreviouslySet()
    {
        $now = new \DateTime('now');
        $timestamp = $now->format('U');

        $obj = new ValueTest();
        $obj->setValue('aNamespace', 'aKey', $now->format('U'));

        $this->assertSame($timestamp, $obj->getValue('aNamespace', 'aKey'));

        $actualDate = $obj->getValue('aNamespace', 'aKey', null, 'date');
        $this->assertInstanceOf(\DateTime::class, $actualDate);
        $this->assertEquals($timestamp, $actualDate->format('U'));
    }

    public function testShouldAllowCastToTypeOnGet()
    {
        $obj = new ValueTest();
        $obj->setValue('aNamespace', 'aKey', '123');

        $this->assertSame(123, $obj->getValue('aNamespace', 'aKey', null, 'int'));
    }

    public function testShouldAllowUnsetPreviouslySetValue()
    {
        $obj = new ValueTest();
        $obj->setValue('aName', 'aKey', 'aVal');

        $this->assertSame('aVal', $obj->getValue('aName', 'aKey'));
        $this->assertSame(['aName' => ['aKey' => 'aVal']], $obj->getValues());
        $this->assertSame(['aName' => ['aKey' => 'aVal']], $obj->getChangedValues());

        $obj->setValue('aName', 'aKey', null);

        $this->assertSame(null, $obj->getValue('aName', 'aKey'));
        $this->assertSame(['aName' => []], $obj->getValues());
        $this->assertSame(['aName' => ['aKey' => null]], $obj->getChangedValues());
    }

    public function testShouldAllowAddValueToEmptyArray()
    {
        $obj = new ValueTest();
        $obj->addValue('aNamespace', 'aKey', 'aVal');

        $this->assertSame(['aVal'], $obj->getValue('aNamespace', 'aKey'));
        $this->assertSame(['aNamespace' => ['aKey' => ['aVal']]], $obj->getValues());
        $this->assertSame(['aNamespace' => ['aKey' => ['aVal']]], $obj->getChangedValues());
    }

    public function testShouldAllowAddDateValueToArrayAndConvertToISO()
    {
        $now = new \DateTime('now');
        $timestamp = (int) $now->format('U');
        $iso = $now->format(DATE_ISO8601);

        $obj = new ValueTest();
        $obj->addValue('aNamespace', 'aKey', $now);

        $this->assertSame([['unix' => $timestamp, 'iso' => $iso]], $obj->getValue('aNamespace', 'aKey'));
        $this->assertSame(['aNamespace' => ['aKey' => [['unix' => $timestamp, 'iso' => $iso]]]], $obj->getValues());
        $this->assertSame(['aNamespace' => ['aKey' => [['unix' => $timestamp, 'iso' => $iso]]]], $obj->getChangedValues());
    }

    public function testShouldAllowAddValueToAlreadyArray()
    {
        $obj = new ValueTest();
        $obj->setValues(['aNamespace' => ['aKey' => ['aVal']]]);
        $obj->addValue('aNamespace', 'aKey', 'aVal');

        $this->assertSame(['aVal', 'aVal'], $obj->getValue('aNamespace', 'aKey'));
        $this->assertSame(['aNamespace' => ['aKey' => ['aVal', 'aVal']]], $obj->getValues());
        $this->assertSame(['aNamespace' => ['aKey' => ['aVal', 'aVal']]], $obj->getChangedValues());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Cannot set value to aNamespace.aKey it is already set and not array
     */
    public function testThrowsIfAddValueToExistOneWhichNotArray()
    {
        $obj = new ValueTest();
        $obj->setValues(['aNamespace' => ['aKey' => 'aVal']]);
        $obj->addValue('aNamespace', 'aKey', 'aVal');
    }
}

class ValueTest
{
    use ValuesTrait {
        setSelfValue as public;
        getSelfValue as public;
    }
}
