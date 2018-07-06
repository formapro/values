<?php
namespace Makasim\Values\Tests;

use function Makasim\Values\get_object_changed_values;
use function Makasim\Values\get_values;
use Makasim\Values\Tests\Model\CastableObject;
use PHPUnit\Framework\TestCase;

class CastTraitTest extends TestCase
{
    public function testShouldAllowCastToTypeOnGet()
    {
        $obj = new CastableObject();
        $obj->setValue('aNamespace.aKey', '123');

        self::assertSame(123, $obj->getValue('aNamespace.aKey', null, 'int'));
    }

    public function testShouldAllowSetDateTimeValueAndGetPreviouslySet()
    {
        $now = new \DateTime('now');
        $timestamp = $now->format('U');

        $obj = new CastableObject();
        $obj->setValue('aNamespace.aKey', $now);

        $actualDate = $obj->getValue('aNamespace.aKey', null, \DateTime::class);

        self::assertInstanceOf(\DateTime::class, $actualDate);
        self::assertEquals($timestamp, $actualDate->format('U'));
    }

    public function testShouldNotCastValueToDateTimeIfValueIsNotSetOrNull()
    {
        $obj = new CastableObject();

        // is not set
        self::assertNull($obj->getValue('aNamespace.aKey', null, \DateTime::class));

        // set null
        $obj->setValue('aNamespace.aKey', null);
        self::assertNull($obj->getValue('aNamespace.aKey', null, \DateTime::class));
    }

    public function testShouldNotCastValueToDateIntervalIfValueIsNotSetOrNull()
    {
        $obj = new CastableObject();

        // is not set
        self::assertNull($obj->getValue('aNamespace.aKey', null, \DateInterval::class));

        // set null
        $obj->setValue('aNamespace.aKey', null);
        self::assertNull($obj->getValue('aNamespace.aKey', null, \DateInterval::class));
    }

    public function testShouldCastScalarValueEvenIfValueIsNotSetOrNull()
    {
        $obj = new CastableObject();

        // is not set
        self::assertInternalType('integer', $obj->getValue('aNamespace.aKey', null, 'integer'));
        self::assertSame(0, $obj->getValue('aNamespace.aKey', null, 'integer'));

        // set null
        $obj->setValue('aNamespace.aKey', null);
        self::assertInternalType('integer', $obj->getValue('aNamespace.aKey', null, 'integer'));
        self::assertSame(0, $obj->getValue('aNamespace.aKey', null, 'integer'));
    }

    public function testShouldAllowSetDateTimeValueASISOAndGetPreviouslySet()
    {
        $now = new \DateTime('now');
        $timestamp = $now->format('U');
        $iso = $now->format(DATE_ISO8601);

        $obj = new CastableObject();
        $obj->setValue('aNamespace.aKey', $now->format(DATE_ISO8601));

        self::assertSame($iso, $obj->getValue('aNamespace.aKey'));

        $actualDate = $obj->getValue('aNamespace.aKey', null, \DateTime::class);
        self::assertInstanceOf(\DateTime::class, $actualDate);
        self::assertEquals($timestamp, $actualDate->format('U'));
    }

    public function testShouldAllowSetDateTimeValueASTimestampAndGetPreviouslySet()
    {
        $now = new \DateTime('now');
        $timestamp = $now->format('U');

        $obj = new CastableObject();
        $obj->setValue('aNamespace.aKey', $now->format('U'));

        self::assertSame($timestamp, $obj->getValue('aNamespace.aKey'));

        $actualDate = $obj->getValue('aNamespace.aKey', null, \DateTime::class);
        self::assertInstanceOf(\DateTime::class, $actualDate);
        self::assertEquals($timestamp, $actualDate->format('U'));
    }

    public function testShouldAllowAddDateValueToArrayAndConvertToDateAndTimeZone()
    {
        $date = new \DateTime('2012-12-12 12:00:00', new \DateTimeZone('Europe/Kiev'));
        $timestamp = (int) $date->format('U');

        $obj = new CastableObject();
        $obj->addValue('aNamespace.aKey', $date);

        $expectedValue = [
            'unix' => $timestamp,
            'time' => '2012-12-12T12:00:00',
            'tz' => 'Europe/Kiev'
        ];

        self::assertSame([$expectedValue], $obj->getValue('aNamespace.aKey'));
        self::assertSame(['aNamespace' => ['aKey' => [$expectedValue]]], get_values($obj));
        self::assertSame(['aNamespace' => ['aKey' => [$expectedValue]]], get_object_changed_values($obj));
    }

    public function testShouldAllowAddDateIntervalValueToArray()
    {
        $interval = new \DateInterval('P7D');

        $obj = new CastableObject();
        $obj->addValue('aNamespace.aKey', $interval);

        self::assertSame([[
            'interval' => 'P0Y0M7DT00H00M00S',
            'days' => false,
            'y' => 0,
            'm' => 0,
            'd' => 7,
            'h' => 0,
            'i' => 0,
            's' => 0,
        ]], $obj->getValue('aNamespace.aKey'));
    }

    public function testShouldAllowSetDateIntervalValueToArray()
    {
        $interval = new \DateInterval('P7D');

        $obj = new CastableObject();
        $obj->setValue('aNamespace.aKey', $interval);

        self::assertSame([
            'interval' => 'P0Y0M7DT00H00M00S',
            'days' => false,
            'y' => 0,
            'm' => 0,
            'd' => 7,
            'h' => 0,
            'i' => 0,
            's' => 0,
        ], $obj->getValue('aNamespace.aKey'));
    }

    public function testShouldAllowSetDateTimeValueASStringAndGetPreviouslySet()
    {
        $obj = new CastableObject();
        $obj->setValue('aNamespace.aKey', 'P7D');


        $interval = $obj->getValue('aNamespace.aKey', null, \DateInterval::class);
        self::assertInstanceOf(\DateInterval::class, $interval);
        self::assertEquals('P0Y0M7DT00H00M00S', $interval->format('P%yY%mM%dDT%HH%IM%SS'));
    }

    public function testShouldAllowSetDateTimeValueASArrayAndGetPreviouslySet()
    {
        $obj = new CastableObject();
        $obj->setValue('aNamespace.aKey', ['interval' => 'P7D']);


        $interval = $obj->getValue('aNamespace.aKey', null, \DateInterval::class);
        self::assertInstanceOf(\DateInterval::class, $interval);
        self::assertEquals('P0Y0M7DT00H00M00S', $interval->format('P%yY%mM%dDT%HH%IM%SS'));
    }
}