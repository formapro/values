<?php
namespace Makasim\Values\Tests;

use Makasim\Values\CastTrait;
use function Makasim\Values\get_object_changed_values;
use function Makasim\Values\get_object_values;
use Makasim\Values\ValuesTrait;
use PHPUnit\Framework\TestCase;

class CastTraitTest extends TestCase
{
    public function testShouldAllowCastToTypeOnGet()
    {
        $obj = new CastTest();
        $obj->setValue('aNamespace.aKey', '123');

        self::assertSame(123, $obj->getValue('aNamespace.aKey', null, 'int'));
    }

    public function testShouldAllowSetDateTimeValueAndGetPreviouslySet()
    {
        $now = new \DateTime('now');
        $timestamp = $now->format('U');

        $obj = new CastTest();
        $obj->setValue('aNamespace.aKey', $now);

        $actualDate = $obj->getValue('aNamespace.aKey', null, \DateTime::class);

        self::assertInstanceOf(\DateTime::class, $actualDate);
        self::assertEquals($timestamp, $actualDate->format('U'));
    }

    public function testShouldAllowSetDateTimeValueASISOAndGetPreviouslySet()
    {
        $now = new \DateTime('now');
        $timestamp = $now->format('U');
        $iso = $now->format(DATE_ISO8601);

        $obj = new CastTest();
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

        $obj = new CastTest();
        $obj->setValue('aNamespace.aKey', $now->format('U'));

        self::assertSame($timestamp, $obj->getValue('aNamespace.aKey'));

        $actualDate = $obj->getValue('aNamespace.aKey', null, \DateTime::class);
        self::assertInstanceOf(\DateTime::class, $actualDate);
        self::assertEquals($timestamp, $actualDate->format('U'));
    }

    public function testShouldAllowAddDateValueToArrayAndConvertToISO()
    {
        $now = new \DateTime('now');
        $timestamp = (int) $now->format('U');
        $iso = $now->format(DATE_ISO8601);

        $obj = new CastTest();
        $obj->addValue('aNamespace.aKey', $now);

        self::assertSame([['unix' => $timestamp, 'iso' => $iso]], $obj->getValue('aNamespace.aKey'));
        self::assertSame(['aNamespace' => ['aKey' => [['unix' => $timestamp, 'iso' => $iso]]]], get_object_values($obj));
        self::assertSame(['aNamespace' => ['aKey' => [['unix' => $timestamp, 'iso' => $iso]]]], get_object_changed_values($obj));
    }

    public function testShouldAllowAddDateIntervalValueToArray()
    {
        $interval = new \DateInterval('P7D');

        $obj = new CastTest();
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

        $obj = new CastTest();
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
        $obj = new CastTest();
        $obj->setValue('aNamespace.aKey', 'P7D');


        $interval = $obj->getValue('aNamespace.aKey', null, \DateInterval::class);
        self::assertInstanceOf(\DateInterval::class, $interval);
        self::assertEquals('P0Y0M7DT00H00M00S', $interval->format('P%yY%mM%dDT%HH%IM%SS'));
    }

    public function testShouldAllowSetDateTimeValueASArrayAndGetPreviouslySet()
    {
        $obj = new CastTest();
        $obj->setValue('aNamespace.aKey', ['interval' => 'P7D']);


        $interval = $obj->getValue('aNamespace.aKey', null, \DateInterval::class);
        self::assertInstanceOf(\DateInterval::class, $interval);
        self::assertEquals('P0Y0M7DT00H00M00S', $interval->format('P%yY%mM%dDT%HH%IM%SS'));
    }
}

class CastTest
{
    use ValuesTrait {
        getValue as public;
        setValue as public;
        addValue as public;
    }
    use CastTrait;
}