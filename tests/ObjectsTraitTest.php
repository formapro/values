<?php
namespace Makasim\Values\Tests;

use function Makasim\Values\clone_object;
use function Makasim\Values\get_object_values;
use Makasim\Values\ObjectsTrait;
use function Makasim\Values\get_object_changed_values;
use function Makasim\Values\set_object_values;
use Makasim\Values\ValuesTrait;
use PHPUnit\Framework\TestCase;

class ObjectsTraitTest extends TestCase
{
    public function testShouldResetObjectIfValuesSetAgain()
    {
        $subObj = new SubObjectTest();
        $subObj->setValue('aSubName.aSubKey', 'aFooVal');

        $obj = new ObjectTest();
        $obj->setObject('aName.aKey', $subObj);

        self::assertAttributeNotEmpty('values', $obj);
        self::assertAttributeNotEmpty('objects', $obj);

        $values = [];
        set_object_values($obj, $values);

        self::assertAttributeEmpty('values', $obj);
        self::assertAttributeEmpty('objects', $obj);
    }

    public function testShouldAllowGetPreviouslySetObject()
    {
        $subObj = new SubObjectTest();
        $subObj->setValue('aSubName.aSubKey', 'aFooVal');

        $obj = new ObjectTest();
        $obj->setObject('aName.aKey', $subObj);

        self::assertSame($subObj, $obj->getObject('aName.aKey', SubObjectTest::class));

        self::assertSame(['aName' => ['aKey' => ['aSubName' => ['aSubKey' => 'aFooVal']]]], get_object_values($obj));
        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_object_values($subObj));
    }

    public function testShouldCreateObjectOnGet()
    {
        $obj = new ObjectTest();

        $values = ['aName' => ['aKey' => ['aSubName' => ['aSubKey' => 'aFooVal']]]];
        set_object_values($obj, $values);

        $subObj = $obj->getObject('aName.aKey', SubObjectTest::class);
        self::assertInstanceOf(SubObjectTest::class, $subObj);

        self::assertSame(['aName' => ['aKey' => ['aSubName' => ['aSubKey' => 'aFooVal']]]], get_object_values($obj));
        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_object_values($subObj));
    }

    public function testShouldReturnNullIfValueNotSet()
    {
        $obj = new ObjectTest();

        self::assertNull($obj->getObject('aName.aKey', SubObjectTest::class));
    }

    public function testShouldChangesInSubObjReflectedInObjValues()
    {
        $subObj = new SubObjectTest();
        $subObj->setValue('aSubName.aSubKey', 'aFooVal');

        $obj = new ObjectTest();
        $obj->setObject('aName.aKey', $subObj);

        self::assertSame(['aName' => ['aKey' => ['aSubName' => ['aSubKey' => 'aFooVal']]]], get_object_values($obj));
        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_object_values($subObj));

        $subObj->setValue('aSubName.aSubKey', 'aBarVal');

        self::assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], get_object_values($subObj));
        self::assertSame(['aName' => ['aKey' => ['aSubName' => ['aSubKey' => 'aBarVal']]]], get_object_values($obj));
    }

    public function testShouldChangesInSubSubObjReflectedInObjValues()
    {
        $subSubObj = new SubObjectTest();
        $subSubObj->setValue('aSubSubName.aSubSubKey', 'aFooVal');

        $subObj = new ObjectTest();
        $subObj->setObject('aSubName.aSubKey', $subSubObj);

        $obj = new ObjectTest();
        $obj->setObject('aName.aKey', $subObj);

        self::assertSame(['aName' => ['aKey' => [
            'aSubName' => [
                'aSubKey' => ['aSubSubName' => ['aSubSubKey' => 'aFooVal']],
            ], ]]], get_object_values($obj));
        self::assertSame(['aSubSubName' => ['aSubSubKey' => 'aFooVal']], get_object_values($subSubObj));

        $subSubObj->setValue('aSubSubName.aSubSubKey', 'aBarVal');

        self::assertSame(['aName' => ['aKey' => [
            'aSubName' => [
                'aSubKey' => ['aSubSubName' => ['aSubSubKey' => 'aBarVal']],
            ], ]]], get_object_values($obj));
        self::assertSame(['aSubSubName' => ['aSubSubKey' => 'aBarVal']], get_object_values($subSubObj));
    }

    public function testShouldNotChangesInSubObjReflectedInObjValuesIfUnset()
    {
        $subObj = new SubObjectTest();
        $subObj->setValue('aSubName.aSubKey', 'aFooVal');

        $obj = new ObjectTest();
        $obj->setObject('aName.aKey', $subObj);

        self::assertSame(['aName' => ['aKey' => ['aSubName' => ['aSubKey' => 'aFooVal']]]], get_object_values($obj));
        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_object_values($subObj));

        $obj->setObject('aName.aKey', null);

        self::assertSame(['aName' => []], get_object_values($obj));
        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_object_values($subObj));

        $subObj->setValue('aSubName.aSubKey', 'aBarVal');
        self::assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], get_object_values($subObj));
    }

    /**
     *
     */
    public function testShouldAddSubObjValuesToObjChangedValues()
    {
        $subObj = new SubObjectTest();
        $subObj->setValue('aSubName.aSubKey', 'aFooVal');

        $obj = new ObjectTest();
        $obj->setObject('aName.aKey', $subObj);

        self::assertSame(['aName' => ['aKey' => ['aSubName' => ['aSubKey' => 'aFooVal']]]], get_object_changed_values($obj));
    }

    public function testShouldUnsetSubObjIfSameValueChangedAfterSubObjSet()
    {
        $subObj = new SubObjectTest();
        $subObj->setValue('aSubName.aSubKey', 'aFooVal');

        $obj = new ObjectTest();
        $obj->setObject('aName.aKey', $subObj);

        self::assertAttributeSame(['aName' => ['aKey' => $subObj]], 'objects', $obj);

        $obj->setValue('aName.aKey', 'aFooVal');

        self::assertAttributeEquals(['aName' => []], 'objects', $obj);
    }

    public function testShouldAllowDefineClosureAsClass()
    {
        $subObjValues = ['aSubName' => ['aSubKey' => 'aFooVal']];

        $expectedSubClass = $this->getMockClass(SubObjectTest::class);

        $obj = new ObjectTest();

        $values = ['aName' => ['aKey' => $subObjValues]];
        set_object_values($obj, $values);

        $subObj = $obj->getObject('aName.aKey', function ($actualSubObjValues) use ($subObjValues, $expectedSubClass) {
            self::assertSame($subObjValues, $actualSubObjValues);

            return $expectedSubClass;
        });

        self::assertInstanceOf($expectedSubClass, $subObj);
    }

    public function testShouldAllowGetPreviouslySetObjects()
    {
        $subObjFoo = new SubObjectTest();
        $subObjFoo->setValue('aSubName.aSubKey', 'aFooVal');

        $subObjBar = new SubObjectTest();
        $subObjBar->setValue('aSubName.aSubKey', 'aBarVal');

        $obj = new ObjectTest();
        $obj->setObjects('aName.aKey', [$subObjFoo, $subObjBar]);

        $objs = $obj->getObjects('aName.aKey', SubObjectTest::class);
        self::assertInstanceOf(\Traversable::class, $objs);

        self::assertSame([$subObjFoo, $subObjBar], iterator_to_array($objs));

        self::assertSame(['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], get_object_values($obj));
        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_object_values($subObjFoo));
        self::assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], get_object_values($subObjBar));
    }

    public function testShouldCreateObjectsOnGet()
    {
        $values = ['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]];

        $obj = new ObjectTest();
        set_object_values($obj, $values);

        $subObjs = $obj->getObjects('aName.aKey', SubObjectTest::class);
        $subObjs = iterator_to_array($subObjs);

        self::assertCount(2, $subObjs);
        self::assertContainsOnlyInstancesOf(SubObjectTest::class, $subObjs);

        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_object_values($subObjs[0]));
        self::assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], get_object_values($subObjs[1]));
    }

    public function testShouldAllowAddObjectToCollection()
    {
        $subObjFoo = new SubObjectTest();
        $subObjFoo->setValue('aSubName.aSubKey', 'aFooVal');

        $subObjBar = new SubObjectTest();
        $subObjBar->setValue('aSubName.aSubKey', 'aBarVal');

        $obj = new ObjectTest();
        $obj->addObject('aName.aKey', $subObjFoo);
        $obj->addObject('aName.aKey', $subObjBar);

        $objs = $obj->getObjects('aName.aKey', SubObjectTest::class);
        $objs = iterator_to_array($objs);

        self::assertSame([$subObjFoo, $subObjBar], $objs);

        self::assertSame(['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], get_object_values($obj));

        self::assertAttributeSame(['aName' => ['aKey' => [$subObjFoo, $subObjBar]]], 'objects', $obj);

        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_object_values($subObjFoo));
        self::assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], get_object_values($subObjBar));
    }

    public function testShouldAllowGetObjectsEitherSetAsValuesAndAddObject()
    {
        $values = ['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
        ]]];

        $obj = new ObjectTest();
        set_object_values($obj, $values);

        $subObjBar = new SubObjectTest();
        $subObjBar->setValue('aSubName.aSubKey', 'aBarVal');

        $obj->addObject('aName.aKey', $subObjBar);

        $subObjs = $obj->getObjects('aName.aKey', SubObjectTest::class);
        self::assertInstanceOf(\Traversable::class, $subObjs);

        $subObjs = iterator_to_array($subObjs);

        self::assertCount(2, $subObjs);

        self::assertSame(['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], get_object_values($obj));

        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_object_values($subObjs[0]));
        self::assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], get_object_values($subObjs[1]));
    }

    public function testShouldUpdateChangedValuesWhenObjectsSet()
    {
        $subObjFoo = new SubObjectTest();
        $subObjFoo->setValue('aSubName.aSubKey', 'aFooVal');

        $subObjBar = new SubObjectTest();
        $subObjBar->setValue('aSubName.aSubKey', 'aBarVal');

        $obj = new ObjectTest();

        self::assertAttributeEmpty('changedValues', $obj);

        $obj->setObjects('aName.aKey', [$subObjFoo, $subObjBar]);

        self::assertAttributeEquals(['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], 'changedValues', $obj);
    }

    public function testShouldUpdatedChangedValuesWhenObjectAdded()
    {
        $subObjFoo = new SubObjectTest();
        $subObjFoo->setValue('aSubName.aSubKey', 'aFooVal');

        $subObjBar = new SubObjectTest();
        $subObjBar->setValue('aSubName.aSubKey', 'aBarVal');

        $obj = new ObjectTest();

        self::assertAttributeEmpty('changedValues', $obj);

        $obj->addObject('aName.aKey', $subObjFoo);
        $obj->addObject('aName.aKey', $subObjBar);

        $objs = $obj->getObjects('aName.aKey', SubObjectTest::class);
        $objs = iterator_to_array($objs);

        self::assertSame([$subObjFoo, $subObjBar], $objs);

        self::assertAttributeEquals(['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], 'changedValues', $obj);
    }

    public function testShouldAllowUnsetObjects()
    {
        $subObjFoo = new SubObjectTest();
        $subObjFoo->setValue('aSubName.aSubKey', 'aFooVal');

        $subObjBar = new SubObjectTest();
        $subObjBar->setValue('aSubName.aSubKey', 'aBarVal');

        $obj = new ObjectTest();
        $obj->setObjects('aName.aKey', [$subObjFoo, $subObjBar]);

        self::assertSame(['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], get_object_values($obj));

        self::assertAttributeSame(['aName' => ['aKey' => [$subObjFoo, $subObjBar]]], 'objects', $obj);

        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_object_values($subObjFoo));
        self::assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], get_object_values($subObjBar));

        $obj->setObjects('aName.aKey', null);

        self::assertSame(['aName' => []], get_object_values($obj));
        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_object_values($subObjFoo));
        self::assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], get_object_values($subObjBar));
    }

    public function testShouldAllowResetObjects()
    {
        $subObjFoo = new SubObjectTest();
        $subObjFoo->setValue('aSubName.aSubKey', 'aFooVal');

        $subObjBar = new SubObjectTest();
        $subObjBar->setValue('aSubName.aSubKey', 'aBarVal');

        $obj = new ObjectTest();
        $obj->setObjects('aName.aKey', [$subObjFoo, $subObjBar]);

        self::assertSame(['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], get_object_values($obj));

        self::assertAttributeSame(['aName' => ['aKey' => [$subObjFoo, $subObjBar]]], 'objects', $obj);

        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_object_values($subObjFoo));
        self::assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], get_object_values($subObjBar));

        $obj->setObjects('aName.aKey', []);

        self::assertAttributeSame(['aName' => ['aKey' => []]], 'objects', $obj);

        self::assertSame(['aName' => ['aKey' => []]], get_object_values($obj));
        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_object_values($subObjFoo));
        self::assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], get_object_values($subObjBar));
    }

    /**
     * @group d
     */
    public function testShouldReflectChangesDoneInSubObject()
    {
        $values = [
            'aName' => [
                'aKey' => [
                    'aSubName' => ['aSubKey' => 'aFooVal'],
                ],
            ],
        ];

        $obj = new ObjectTest();
        set_object_values($obj, $values);

        //guard
        self::assertEmpty(get_object_changed_values($obj));

        $subObj = $obj->getObject('aName.aKey', SubObjectTest::class);

        $subObj->setValue('aSubName.aSubKey', 'aBarVal');

        self::assertEquals(['aSubName' => ['aSubKey' => 'aBarVal']], get_object_changed_values($subObj));
        self::assertEquals([
            'aName' => [
                'aKey' => [
                    'aSubName' => ['aSubKey' => 'aBarVal'],
                ],
            ],
        ], get_object_changed_values($obj));

        self::assertEquals([
            'aName' => [
                'aKey' => [
                    'aSubName' => ['aSubKey' => 'aBarVal'],
                ],
            ],
        ], get_object_changed_values($obj));
    }

    public function testShouldReflectChangesDoneInSubObjectFromCollection()
    {
        $values = [
            'aName' => [
                'aKey' => [
                    ['aSubName' => ['aSubKey' => 'aFooVal']],
                    ['aSubName' => ['aSubKey' => 'aBarVal']],
                ],
            ],
        ];

        $obj = new ObjectTest();
        set_object_values($obj, $values);

        //guard
        self::assertEmpty(get_object_changed_values($obj));

        $subObjs = $obj->getObjects('aName.aKey', SubObjectTest::class);

        self::assertInstanceOf(\Traversable::class, $subObjs);
        $subObjs = iterator_to_array($subObjs);
        $subObjs[0]->setValue('aSubName.aSubKey', 'aBazVal');

        self::assertEquals(
            ['aSubName' => ['aSubKey' => 'aBazVal']],
            get_object_changed_values($subObjs[0])
        );

        self::assertEquals([
            'aName' => [
                'aKey' => [
                    ['aSubName' => ['aSubKey' => 'aBazVal']],
                ],
            ],
        ], get_object_changed_values($obj));
    }

    public function testShouldReflectChangesDoneWhenSubObjectUnset()
    {
        $values = $arr = [
            'aName' => [
                'aKey' => [
                    'aSubName' => ['aSubKey' => 'aFooVal'],
                ],
            ],
        ];

        $obj = new ObjectTest();
        set_object_values($obj, $values);

        //guard
        self::assertEmpty(get_object_changed_values($obj));

        $obj->setObject('aName.aKey', null);

        self::assertNotEmpty(get_object_changed_values($obj));

        self::assertEquals(['aName' => ['aKey' => null]], get_object_changed_values($obj));
    }

    public function testShouldNotReflectChangesIfObjectWasCloned()
    {
        $values = [
            'aName' => [
                'aKey' => [
                    'aSubName' => ['aSubKey' => 'aFooVal'],
                ],
            ],
        ];

        $obj = new ObjectTest();
        set_object_values($obj, $values);

        //guard
        self::assertEmpty(get_object_changed_values($obj));

        /** @var SubObjectTest $subObj */
        $subObj = $obj->getObject('aName.aKey', SubObjectTest::class);

        //guard
        self::assertInstanceOf(SubObjectTest::class, $subObj);

        $clonedSubObj = clone_object($subObj);
        $clonedSubObj->setValue('self.aSubKeyFoo', 'aBarVal');

        self::assertEquals([
            'aName' => [
                'aKey' => [
                    'aSubName' => ['aSubKey' => 'aFooVal'],
                ],
            ],
        ], get_object_values($obj));
    }

    public function testShouldAllowSetSelfObjectAndGetPreviouslySet()
    {
        $subObjFoo = new SubObjectTest();
        $subObjFoo->setValue('aSubName.aSubKey', 'aFooVal');

        $obj = new ObjectTest();
        $obj->setObject('self.aKey', $subObjFoo);

        self::assertSame($subObjFoo, $obj->getObject('self.aKey', ObjectTest::class));
        self::assertSame(['self' => ['aKey' =>
            ['aSubName' => ['aSubKey' => 'aFooVal']],
        ]], get_object_values($obj));
        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_object_values($subObjFoo));
    }

    public function testShouldAllowSetSelfObjectsAndGetPreviouslySet()
    {
        $subObjFoo = new SubObjectTest();
        $subObjFoo->setValue('aSubName.aSubKey', 'aFooVal');

        $subObjBar = new SubObjectTest();
        $subObjBar->setValue('aSubName.aSubKey', 'aBarVal');

        $obj = new ObjectTest();
        $obj->setObjects('self.aKey', [$subObjFoo, $subObjBar]);

        $objs = $obj->getObjects('self.aKey', SubObjectTest::class);
        $objs = iterator_to_array($objs);

        self::assertSame([$subObjFoo, $subObjBar], $objs);

        self::assertSame(['self' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], get_object_values($obj));
        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_object_values($subObjFoo));
        self::assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], get_object_values($subObjBar));
    }

    public function testShouldAllowAddSelfObjectsAndGetPreviouslySet()
    {
        $subObjFoo = new SubObjectTest();
        $subObjFoo->setValue('aSubName.aSubKey', 'aFooVal');

        $subObjBar = new SubObjectTest();
        $subObjBar->setValue('aSubName.aSubKey', 'aBarVal');

        $obj = new ObjectTest();
        $obj->addObject('self.aKey', $subObjFoo);
        $obj->addObject('self.aKey', $subObjBar);

        $objs = $obj->getObjects('self.aKey', SubObjectTest::class);
        $objs = iterator_to_array($objs);

        self::assertSame([$subObjFoo, $subObjBar], $objs);

        self::assertSame(['self' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], get_object_values($obj));
        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_object_values($subObjFoo));
        self::assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], get_object_values($subObjBar));
    }
}

class ObjectTest
{
    use ValuesTrait {
        getValue as public;
        setValue as public;
        addValue as public;
    }

    use ObjectsTrait {
        setObject as public;
        getObject as public;
        setObjects as public;
        getObjects as public;
        addObject as public;
    }
}

class SubObjectTest
{
    use ValuesTrait {
        getValue as public;
        setValue as public;
        addValue as public;
    }
}