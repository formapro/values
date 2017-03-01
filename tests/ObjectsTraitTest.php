<?php
namespace Makasim\Values\Tests;

use function Makasim\Values\clone_object;
use function Makasim\Values\get_values;
use function Makasim\Values\get_object_changed_values;
use function Makasim\Values\set_values;
use Makasim\Values\Tests\Model\Object;
use Makasim\Values\Tests\Model\SubObject;
use PHPUnit\Framework\TestCase;

class ObjectsTraitTest extends TestCase
{
    public function testShouldResetObjectIfValuesSetAgain()
    {
        $subObj = new SubObject();
        $subObj->setValue('aSubName.aSubKey', 'aFooVal');

        $obj = new Object();
        $obj->setObject('aName.aKey', $subObj);

        self::assertAttributeNotEmpty('values', $obj);
        self::assertAttributeNotEmpty('objects', $obj);

        $values = [];
        set_values($obj, $values);

        self::assertAttributeEmpty('values', $obj);
        self::assertAttributeEmpty('objects', $obj);
    }

    public function testShouldAllowGetPreviouslySetObject()
    {
        $subObj = new SubObject();
        $subObj->setValue('aSubName.aSubKey', 'aFooVal');

        $obj = new Object();
        $obj->setObject('aName.aKey', $subObj);

        self::assertSame($subObj, $obj->getObject('aName.aKey', SubObject::class));

        self::assertSame(['aName' => ['aKey' => ['aSubName' => ['aSubKey' => 'aFooVal']]]], get_values($obj));
        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_values($subObj));
    }

    public function testShouldCreateObjectOnGet()
    {
        $obj = new Object();

        $values = ['aName' => ['aKey' => ['aSubName' => ['aSubKey' => 'aFooVal']]]];
        set_values($obj, $values);

        $subObj = $obj->getObject('aName.aKey', SubObject::class);
        self::assertInstanceOf(SubObject::class, $subObj);

        self::assertSame(['aName' => ['aKey' => ['aSubName' => ['aSubKey' => 'aFooVal']]]], get_values($obj));
        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_values($subObj));
    }

    public function testShouldReturnNullIfValueNotSet()
    {
        $obj = new Object();

        self::assertNull($obj->getObject('aName.aKey', SubObject::class));
    }

    public function testShouldChangesInSubObjReflectedInObjValues()
    {
        $subObj = new SubObject();
        $subObj->setValue('aSubName.aSubKey', 'aFooVal');

        $obj = new Object();
        $obj->setObject('aName.aKey', $subObj);

        self::assertSame(['aName' => ['aKey' => ['aSubName' => ['aSubKey' => 'aFooVal']]]], get_values($obj));
        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_values($subObj));

        $subObj->setValue('aSubName.aSubKey', 'aBarVal');

        self::assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], get_values($subObj));
        self::assertSame(['aName' => ['aKey' => ['aSubName' => ['aSubKey' => 'aBarVal']]]], get_values($obj));
    }

    public function testShouldChangesInSubSubObjReflectedInObjValues()
    {
        $subSubObj = new SubObject();
        $subSubObj->setValue('aSubSubName.aSubSubKey', 'aFooVal');

        $subObj = new Object();
        $subObj->setObject('aSubName.aSubKey', $subSubObj);

        $obj = new Object();
        $obj->setObject('aName.aKey', $subObj);

        self::assertSame(['aName' => ['aKey' => [
            'aSubName' => [
                'aSubKey' => ['aSubSubName' => ['aSubSubKey' => 'aFooVal']],
            ], ]]], get_values($obj));
        self::assertSame(['aSubSubName' => ['aSubSubKey' => 'aFooVal']], get_values($subSubObj));

        $subSubObj->setValue('aSubSubName.aSubSubKey', 'aBarVal');

        self::assertSame(['aName' => ['aKey' => [
            'aSubName' => [
                'aSubKey' => ['aSubSubName' => ['aSubSubKey' => 'aBarVal']],
            ], ]]], get_values($obj));
        self::assertSame(['aSubSubName' => ['aSubSubKey' => 'aBarVal']], get_values($subSubObj));
    }

    public function testShouldNotChangesInSubObjReflectedInObjValuesIfUnset()
    {
        $subObj = new SubObject();
        $subObj->setValue('aSubName.aSubKey', 'aFooVal');

        $obj = new Object();
        $obj->setObject('aName.aKey', $subObj);

        self::assertSame(['aName' => ['aKey' => ['aSubName' => ['aSubKey' => 'aFooVal']]]], get_values($obj));
        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_values($subObj));

        $obj->setObject('aName.aKey', null);

        self::assertSame(['aName' => []], get_values($obj));
        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_values($subObj));

        $subObj->setValue('aSubName.aSubKey', 'aBarVal');
        self::assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], get_values($subObj));
    }

    /**
     *
     */
    public function testShouldAddSubObjValuesToObjChangedValues()
    {
        $subObj = new SubObject();
        $subObj->setValue('aSubName.aSubKey', 'aFooVal');

        $obj = new Object();
        $obj->setObject('aName.aKey', $subObj);

        self::assertSame(['aName' => ['aKey' => ['aSubName' => ['aSubKey' => 'aFooVal']]]], get_object_changed_values($obj));
    }

    public function testShouldUnsetSubObjIfSameValueChangedAfterSubObjSet()
    {
        $subObj = new SubObject();
        $subObj->setValue('aSubName.aSubKey', 'aFooVal');

        $obj = new Object();
        $obj->setObject('aName.aKey', $subObj);

        self::assertAttributeSame(['aName' => ['aKey' => $subObj]], 'objects', $obj);

        $obj->setValue('aName.aKey', 'aFooVal');

        self::assertAttributeEquals(['aName' => []], 'objects', $obj);
    }

    public function testShouldAllowDefineClosureAsClass()
    {
        $subObjValues = ['aSubName' => ['aSubKey' => 'aFooVal']];

        $expectedSubClass = $this->getMockClass(SubObject::class);

        $obj = new Object();

        $values = ['aName' => ['aKey' => $subObjValues]];
        set_values($obj, $values);

        $subObj = $obj->getObject('aName.aKey', function ($actualSubObjValues) use ($subObjValues, $expectedSubClass) {
            self::assertSame($subObjValues, $actualSubObjValues);

            return $expectedSubClass;
        });

        self::assertInstanceOf($expectedSubClass, $subObj);
    }

    public function testShouldAllowGetPreviouslySetObjects()
    {
        $subObjFoo = new SubObject();
        $subObjFoo->setValue('aSubName.aSubKey', 'aFooVal');

        $subObjBar = new SubObject();
        $subObjBar->setValue('aSubName.aSubKey', 'aBarVal');

        $obj = new Object();
        $obj->setObjects('aName.aKey', [$subObjFoo, $subObjBar]);

        $objs = $obj->getObjects('aName.aKey', SubObject::class);
        self::assertInstanceOf(\Traversable::class, $objs);

        self::assertSame([$subObjFoo, $subObjBar], iterator_to_array($objs));

        self::assertSame(['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], get_values($obj));
        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_values($subObjFoo));
        self::assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], get_values($subObjBar));
    }

    public function testShouldCreateObjectsOnGet()
    {
        $values = ['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]];

        $obj = new Object();
        set_values($obj, $values);

        $subObjs = $obj->getObjects('aName.aKey', SubObject::class);
        $subObjs = iterator_to_array($subObjs);

        self::assertCount(2, $subObjs);
        self::assertContainsOnlyInstancesOf(SubObject::class, $subObjs);

        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_values($subObjs[0]));
        self::assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], get_values($subObjs[1]));
    }

    /**
     * @group d
     */
    public function testShouldAllowAddObjectToCollection()
    {
        $subObjFoo = new SubObject();
        $subObjFoo->setValue('aSubName.aSubKey', 'aFooVal');

        $subObjBar = new SubObject();
        $subObjBar->setValue('aSubName.aSubKey', 'aBarVal');

        $obj = new Object();
        $obj->addObject('aName.aKey', $subObjFoo);
        $obj->addObject('aName.aKey', $subObjBar);

        $objs = $obj->getObjects('aName.aKey', SubObject::class);
        $objs = iterator_to_array($objs);

        self::assertSame([$subObjFoo, $subObjBar], $objs);

        self::assertSame(['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], get_values($obj));

        self::assertAttributeSame(['aName' => ['aKey' => [$subObjFoo, $subObjBar]]], 'objects', $obj);

        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_values($subObjFoo));
        self::assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], get_values($subObjBar));
    }

    public function testShouldAllowGetObjectsEitherSetAsValuesAndAddObject()
    {
        $values = ['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
        ]]];

        $obj = new Object();
        set_values($obj, $values);

        $subObjBar = new SubObject();
        $subObjBar->setValue('aSubName.aSubKey', 'aBarVal');

        $obj->addObject('aName.aKey', $subObjBar);

        $subObjs = $obj->getObjects('aName.aKey', SubObject::class);
        self::assertInstanceOf(\Traversable::class, $subObjs);

        $subObjs = iterator_to_array($subObjs);

        self::assertCount(2, $subObjs);

        self::assertSame(['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], get_values($obj));

        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_values($subObjs[0]));
        self::assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], get_values($subObjs[1]));
    }

    public function testShouldUpdateChangedValuesWhenObjectsSet()
    {
        $subObjFoo = new SubObject();
        $subObjFoo->setValue('aSubName.aSubKey', 'aFooVal');

        $subObjBar = new SubObject();
        $subObjBar->setValue('aSubName.aSubKey', 'aBarVal');

        $obj = new Object();

        self::assertAttributeEmpty('changedValues', $obj);

        $obj->setObjects('aName.aKey', [$subObjFoo, $subObjBar]);

        self::assertAttributeEquals(['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], 'changedValues', $obj);
    }

    public function testShouldUpdatedChangedValuesWhenObjectAdded()
    {
        $subObjFoo = new SubObject();
        $subObjFoo->setValue('aSubName.aSubKey', 'aFooVal');

        $subObjBar = new SubObject();
        $subObjBar->setValue('aSubName.aSubKey', 'aBarVal');

        $obj = new Object();

        self::assertAttributeEmpty('changedValues', $obj);

        $obj->addObject('aName.aKey', $subObjFoo);
        $obj->addObject('aName.aKey', $subObjBar);

        $objs = $obj->getObjects('aName.aKey', SubObject::class);
        $objs = iterator_to_array($objs);

        self::assertSame([$subObjFoo, $subObjBar], $objs);

        self::assertAttributeEquals(['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], 'changedValues', $obj);
    }

    public function testShouldAllowUnsetObjects()
    {
        $subObjFoo = new SubObject();
        $subObjFoo->setValue('aSubName.aSubKey', 'aFooVal');

        $subObjBar = new SubObject();
        $subObjBar->setValue('aSubName.aSubKey', 'aBarVal');

        $obj = new Object();
        $obj->setObjects('aName.aKey', [$subObjFoo, $subObjBar]);

        self::assertSame(['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], get_values($obj));

        self::assertAttributeSame(['aName' => ['aKey' => [$subObjFoo, $subObjBar]]], 'objects', $obj);

        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_values($subObjFoo));
        self::assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], get_values($subObjBar));

        $obj->setObjects('aName.aKey', null);

        self::assertSame(['aName' => []], get_values($obj));
        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_values($subObjFoo));
        self::assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], get_values($subObjBar));
    }

    public function testShouldAllowResetObjects()
    {
        $subObjFoo = new SubObject();
        $subObjFoo->setValue('aSubName.aSubKey', 'aFooVal');

        $subObjBar = new SubObject();
        $subObjBar->setValue('aSubName.aSubKey', 'aBarVal');

        $obj = new Object();
        $obj->setObjects('aName.aKey', [$subObjFoo, $subObjBar]);

        self::assertSame(['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], get_values($obj));

        self::assertAttributeSame(['aName' => ['aKey' => [$subObjFoo, $subObjBar]]], 'objects', $obj);

        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_values($subObjFoo));
        self::assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], get_values($subObjBar));

        $obj->setObjects('aName.aKey', []);

        self::assertAttributeSame(['aName' => []], 'objects', $obj);

        self::assertSame(['aName' => ['aKey' => []]], get_values($obj));
        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_values($subObjFoo));
        self::assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], get_values($subObjBar));
    }

    public function testShouldReflectChangesDoneInSubObject()
    {
        $values = [
            'aName' => [
                'aKey' => [
                    'aSubName' => ['aSubKey' => 'aFooVal'],
                ],
            ],
        ];

        $obj = new Object();
        set_values($obj, $values);

        //guard
        self::assertEmpty(get_object_changed_values($obj));

        $subObj = $obj->getObject('aName.aKey', SubObject::class);

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

        $obj = new Object();
        set_values($obj, $values);

        //guard
        self::assertEmpty(get_object_changed_values($obj));

        $subObjs = $obj->getObjects('aName.aKey', SubObject::class);

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

        $obj = new Object();
        set_values($obj, $values);

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

        $obj = new Object();
        set_values($obj, $values);

        //guard
        self::assertEmpty(get_object_changed_values($obj));

        /** @var SubObject $subObj */
        $subObj = $obj->getObject('aName.aKey', SubObject::class);

        //guard
        self::assertInstanceOf(SubObject::class, $subObj);

        $clonedSubObj = clone_object($subObj);
        $clonedSubObj->setValue('self.aSubKeyFoo', 'aBarVal');

        self::assertEquals([
            'aName' => [
                'aKey' => [
                    'aSubName' => ['aSubKey' => 'aFooVal'],
                ],
            ],
        ], get_values($obj));
    }

    public function testShouldAllowSetSelfObjectAndGetPreviouslySet()
    {
        $subObjFoo = new SubObject();
        $subObjFoo->setValue('aSubName.aSubKey', 'aFooVal');

        $obj = new Object();
        $obj->setObject('self.aKey', $subObjFoo);

        self::assertSame($subObjFoo, $obj->getObject('self.aKey', Object::class));
        self::assertSame(['self' => ['aKey' =>
            ['aSubName' => ['aSubKey' => 'aFooVal']],
        ]], get_values($obj));
        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_values($subObjFoo));
    }

    public function testShouldAllowSetSelfObjectsAndGetPreviouslySet()
    {
        $subObjFoo = new SubObject();
        $subObjFoo->setValue('aSubName.aSubKey', 'aFooVal');

        $subObjBar = new SubObject();
        $subObjBar->setValue('aSubName.aSubKey', 'aBarVal');

        $obj = new Object();
        $obj->setObjects('self.aKey', [$subObjFoo, $subObjBar]);

        $objs = $obj->getObjects('self.aKey', SubObject::class);
        $objs = iterator_to_array($objs);

        self::assertSame([$subObjFoo, $subObjBar], $objs);

        self::assertSame(['self' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], get_values($obj));
        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_values($subObjFoo));
        self::assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], get_values($subObjBar));
    }

    public function testShouldAllowAddSelfObjectsAndGetPreviouslySet()
    {
        $subObjFoo = new SubObject();
        $subObjFoo->setValue('aSubName.aSubKey', 'aFooVal');

        $subObjBar = new SubObject();
        $subObjBar->setValue('aSubName.aSubKey', 'aBarVal');

        $obj = new Object();
        $obj->addObject('self.aKey', $subObjFoo);
        $obj->addObject('self.aKey', $subObjBar);

        $objs = $obj->getObjects('self.aKey', SubObject::class);
        $objs = iterator_to_array($objs);

        self::assertSame([$subObjFoo, $subObjBar], $objs);

        self::assertSame(['self' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], get_values($obj));
        self::assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], get_values($subObjFoo));
        self::assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], get_values($subObjBar));
    }
}