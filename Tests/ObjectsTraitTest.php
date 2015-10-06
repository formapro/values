<?php
namespace Makasim\ValuesORM\Tests;

use Makasim\ValuesORM\ObjectsTrait;
use Makasim\ValuesORM\ValuesTrait;

class ObjectsTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldResetObjectIfValuesSetAgain()
    {
        $subObj = new SubObjectTest();
        $subObj->setValue('aSubName', 'aSubKey', 'aFooVal');

        $obj = new ObjectTest();
        $obj->setObject('aName', 'aKey', $subObj);

        $this->assertAttributeNotEmpty('values', $obj);
        $this->assertAttributeNotEmpty('objects', $obj);

        $obj->setValues([]);

        $this->assertAttributeEmpty('values', $obj);
        $this->assertAttributeEmpty('objects', $obj);
    }

    public function testShouldAllowGetPreviouslySetObject()
    {
        $subObj = new SubObjectTest();
        $subObj->setValue('aSubName', 'aSubKey', 'aFooVal');

        $obj = new ObjectTest();
        $obj->setObject('aName', 'aKey', $subObj);

        $this->assertSame($subObj, $obj->getObject('aName', 'aKey', SubObjectTest::class));

        $this->assertSame(['aName' => ['aKey' => ['aSubName' => ['aSubKey' => 'aFooVal']]]], $obj->getValues());
        $this->assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], $subObj->getValues());
    }

    public function testShouldCreateObjectOnGet()
    {
        $obj = new ObjectTest();
        $obj->setValues(['aName' => ['aKey' => ['aSubName' => ['aSubKey' => 'aFooVal']]]]);

        $subObj = $obj->getObject('aName', 'aKey', SubObjectTest::class);
        $this->assertInstanceOf(SubObjectTest::class, $subObj);

        $this->assertSame(['aName' => ['aKey' => ['aSubName' => ['aSubKey' => 'aFooVal']]]], $obj->getValues());
        $this->assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], $subObj->getValues());
    }

    public function testShouldReturnNullIfValueNotSet()
    {
        $obj = new ObjectTest();

        $this->assertNull($obj->getObject('aName', 'aKey', SubObjectTest::class));
    }

    public function testShouldChangesInSubObjReflectedInObjValues()
    {
        $subObj = new SubObjectTest();
        $subObj->setValue('aSubName', 'aSubKey', 'aFooVal');

        $obj = new ObjectTest();
        $obj->setObject('aName', 'aKey', $subObj);

        $this->assertSame(['aName' => ['aKey' => ['aSubName' => ['aSubKey' => 'aFooVal']]]], $obj->getValues());
        $this->assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], $subObj->getValues());

        $subObj->setValue('aSubName', 'aSubKey', 'aBarVal');

        $this->assertSame(['aName' => ['aKey' => ['aSubName' => ['aSubKey' => 'aBarVal']]]], $obj->getValues());
        $this->assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], $subObj->getValues());
    }

    public function testShouldChangesInSubSubObjReflectedInObjValues()
    {
        $subSubObj = new SubObjectTest();
        $subSubObj->setValue('aSubSubName', 'aSubSubKey', 'aFooVal');

        $subObj = new ObjectTest();
        $subObj->setObject('aSubName', 'aSubKey', $subSubObj);

        $obj = new ObjectTest();
        $obj->setObject('aName', 'aKey', $subObj);

        $this->assertSame(['aName' => ['aKey' => [
            'aSubName' => [
                'aSubKey' => ['aSubSubName' => ['aSubSubKey' => 'aFooVal']],
        ], ]]], $obj->getValues());
        $this->assertSame(['aSubSubName' => ['aSubSubKey' => 'aFooVal']], $subSubObj->getValues());

        $subSubObj->setValue('aSubSubName', 'aSubSubKey', 'aBarVal');

        $this->assertSame(['aName' => ['aKey' => [
            'aSubName' => [
                'aSubKey' => ['aSubSubName' => ['aSubSubKey' => 'aBarVal']],
            ], ]]], $obj->getValues());
        $this->assertSame(['aSubSubName' => ['aSubSubKey' => 'aBarVal']], $subSubObj->getValues());
    }

    public function testShouldNotChangesInSubObjReflectedInObjValuesIfUnset()
    {
        $subObj = new SubObjectTest();
        $subObj->setValue('aSubName', 'aSubKey', 'aFooVal');

        $obj = new ObjectTest();
        $obj->setObject('aName', 'aKey', $subObj);

        $this->assertSame(['aName' => ['aKey' => ['aSubName' => ['aSubKey' => 'aFooVal']]]], $obj->getValues());
        $this->assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], $subObj->getValues());

        $obj->setObject('aName', 'aKey', null);

        $this->assertSame(['aName' => []], $obj->getValues());
        $this->assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], $subObj->getValues());

        $subObj->setValue('aSubName', 'aSubKey', 'aBarVal');
        $this->assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], $subObj->getValues());
    }

    public function testShouldAddSubObjValuesToObjChangedValues()
    {
        $subObj = new SubObjectTest();
        $subObj->setValue('aSubName', 'aSubKey', 'aFooVal');

        $obj = new ObjectTest();
        $obj->setObject('aName', 'aKey', $subObj);

        $this->assertSame(['aName' => ['aKey' => ['aSubName' => ['aSubKey' => 'aFooVal']]]], $obj->getChangedValues());
    }

    public function testShouldUnsetSubObjIfSameValueChangedAfterSubObjSet()
    {
        $subObj = new SubObjectTest();
        $subObj->setValue('aSubName', 'aSubKey', 'aFooVal');

        $obj = new ObjectTest();
        $obj->setObject('aName', 'aKey', $subObj);

        $this->assertAttributeSame(['aName' => ['aKey' => $subObj]], 'objects', $obj);

        $obj->setValue('aName', 'aKey', 'aFooVal');

        $this->assertAttributeEquals(['aName' => []], 'objects', $obj);
    }

    public function testShouldAllowDefineClosureAsClass()
    {
        $subObjValues = ['aSubName' => ['aSubKey' => 'aFooVal']];

        $expectedSubClass = $this->getMockClass(SubObjectTest::class);

        $obj = new ObjectTest();
        $obj->setValues(['aName' => ['aKey' => $subObjValues]]);

        $subObj = $obj->getObject('aName', 'aKey', function ($actualSubObjValues) use ($subObjValues, $expectedSubClass) {
            $this->assertSame($subObjValues, $actualSubObjValues);

            return $expectedSubClass;
        });

        $this->assertInstanceOf($expectedSubClass, $subObj);
    }

    public function testShouldAllowGetPreviouslySetObjects()
    {
        $subObjFoo = new SubObjectTest();
        $subObjFoo->setValue('aSubName', 'aSubKey', 'aFooVal');

        $subObjBar = new SubObjectTest();
        $subObjBar->setValue('aSubName', 'aSubKey', 'aBarVal');

        $obj = new ObjectTest();
        $obj->setObjects('aName', 'aKey', [$subObjFoo, $subObjBar]);

        $this->assertSame([$subObjFoo, $subObjBar], $obj->getObjects('aName', 'aKey', SubObjectTest::class));

        $this->assertSame(['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], $obj->getValues());
        $this->assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], $subObjFoo->getValues());
        $this->assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], $subObjBar->getValues());
    }

    public function testShouldCreateObjectsOnGet()
    {
        $obj = new ObjectTest();
        $obj->setValues(['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]]);

        $subObjs = $obj->getObjects('aName', 'aKey', SubObjectTest::class);
        $this->assertInternalType('array', $subObjs);
        $this->assertCount(2, $subObjs);
        $this->assertContainsOnlyInstancesOf(SubObjectTest::class, $subObjs);

        $this->assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], $subObjs[0]->getValues());
        $this->assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], $subObjs[1]->getValues());
    }

    public function testShouldAllowAddObjectToCollection()
    {
        $subObjFoo = new SubObjectTest();
        $subObjFoo->setValue('aSubName', 'aSubKey', 'aFooVal');

        $subObjBar = new SubObjectTest();
        $subObjBar->setValue('aSubName', 'aSubKey', 'aBarVal');

        $obj = new ObjectTest();
        $obj->addObject('aName', 'aKey', $subObjFoo);
        $obj->addObject('aName', 'aKey', $subObjBar);

        $this->assertSame([$subObjFoo, $subObjBar], $obj->getObjects('aName', 'aKey', SubObjectTest::class));

        $this->assertSame(['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], $obj->getValues());

        $this->assertAttributeSame(['aName' => ['aKey' => [$subObjFoo, $subObjBar]]], 'objects', $obj);

        $this->assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], $subObjFoo->getValues());
        $this->assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], $subObjBar->getValues());
    }

    public function testShouldAllowGetObjectsEitherSetAsValuesAndAddObject()
    {
        $obj = new ObjectTest();
        $obj->setValues(['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
        ]]]);

        $subObjBar = new SubObjectTest();
        $subObjBar->setValue('aSubName', 'aSubKey', 'aBarVal');

        $obj->addObject('aName', 'aKey', $subObjBar);

        $subObjs = $obj->getObjects('aName', 'aKey', SubObjectTest::class);

        $this->assertCount(2, $subObjs);

        $this->assertSame(['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], $obj->getValues());

        $this->assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], $subObjs[0]->getValues());
        $this->assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], $subObjs[1]->getValues());
    }

    public function testShouldUpdateChangedValuesWhenObjectsSet()
    {
        $subObjFoo = new SubObjectTest();
        $subObjFoo->setValue('aSubName', 'aSubKey', 'aFooVal');

        $subObjBar = new SubObjectTest();
        $subObjBar->setValue('aSubName', 'aSubKey', 'aBarVal');

        $obj = new ObjectTest();

        $this->assertAttributeEmpty('changedValues', $obj);

        $obj->setObjects('aName', 'aKey', [$subObjFoo, $subObjBar]);

        $this->assertAttributeEquals(['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], 'changedValues', $obj);
    }

    public function testShouldUpdatedChangedValuesWhenObjectAdded()
    {
        $subObjFoo = new SubObjectTest();
        $subObjFoo->setValue('aSubName', 'aSubKey', 'aFooVal');

        $subObjBar = new SubObjectTest();
        $subObjBar->setValue('aSubName', 'aSubKey', 'aBarVal');

        $obj = new ObjectTest();

        $this->assertAttributeEmpty('changedValues', $obj);

        $obj->addObject('aName', 'aKey', $subObjFoo);
        $obj->addObject('aName', 'aKey', $subObjBar);

        $this->assertSame([$subObjFoo, $subObjBar], $obj->getObjects('aName', 'aKey', SubObjectTest::class));

        $this->assertAttributeEquals(['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], 'changedValues', $obj);
    }

    public function testShouldAllowUnsetObjects()
    {
        $subObjFoo = new SubObjectTest();
        $subObjFoo->setValue('aSubName', 'aSubKey', 'aFooVal');

        $subObjBar = new SubObjectTest();
        $subObjBar->setValue('aSubName', 'aSubKey', 'aBarVal');

        $obj = new ObjectTest();
        $obj->setObjects('aName', 'aKey', [$subObjFoo, $subObjBar]);

        $this->assertSame(['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], $obj->getValues());

        $this->assertAttributeSame(['aName' => ['aKey' => [$subObjFoo, $subObjBar]]], 'objects', $obj);

        $this->assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], $subObjFoo->getValues());
        $this->assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], $subObjBar->getValues());

        $obj->setObjects('aName', 'aKey', null);

        $this->assertSame(['aName' => []], $obj->getValues());
        $this->assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], $subObjFoo->getValues());
        $this->assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], $subObjBar->getValues());
    }

    public function testShouldAllowResetObjects()
    {
        $subObjFoo = new SubObjectTest();
        $subObjFoo->setValue('aSubName', 'aSubKey', 'aFooVal');

        $subObjBar = new SubObjectTest();
        $subObjBar->setValue('aSubName', 'aSubKey', 'aBarVal');

        $obj = new ObjectTest();
        $obj->setObjects('aName', 'aKey', [$subObjFoo, $subObjBar]);

        $this->assertSame(['aName' => ['aKey' => [
            ['aSubName' => ['aSubKey' => 'aFooVal']],
            ['aSubName' => ['aSubKey' => 'aBarVal']],
        ]]], $obj->getValues());

        $this->assertAttributeSame(['aName' => ['aKey' => [$subObjFoo, $subObjBar]]], 'objects', $obj);

        $this->assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], $subObjFoo->getValues());
        $this->assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], $subObjBar->getValues());

        $obj->setObjects('aName', 'aKey', []);

        $this->assertAttributeSame(['aName' => ['aKey' => []]], 'objects', $obj);

        $this->assertSame(['aName' => ['aKey' => []]], $obj->getValues());
        $this->assertSame(['aSubName' => ['aSubKey' => 'aFooVal']], $subObjFoo->getValues());
        $this->assertSame(['aSubName' => ['aSubKey' => 'aBarVal']], $subObjBar->getValues());
    }

    public function testShouldReflectChangesDoneInSubObject()
    {
        $obj = new ObjectTest();
        $obj->setValues([
            'aName' => [
                'aKey' => [
                    'aSubName' => ['aSubKey' => 'aFooVal'],
                ],
            ],
        ]);

        //guard
        $this->assertEmpty($obj->getChangedValues());

        $subObj = $obj->getObject('aName', 'aKey', SubObjectTest::class);

        $subObj->setValue('aSubName', 'aSubKey', 'aBarVal');

        $this->assertNotEmpty($subObj->getChangedValues());
        $this->assertNotEmpty($obj->getChangedValues());

        $this->assertEquals([
            'aName' => [
                'aKey' => [
                    'aSubName' => ['aSubKey' => 'aBarVal'],
                ],
            ],
        ], $obj->getChangedValues());
    }

    public function testShouldReflectChangesDoneInSubObjectFromCollection()
    {
        $obj = new ObjectTest();
        $obj->setValues([
            'aName' => [
                'aKey' => [
                    ['aSubName' => ['aSubKey' => 'aFooVal']],
                    ['aSubName' => ['aSubKey' => 'aBarVal']],
                ],
            ],
        ]);

        //guard
        $this->assertEmpty($obj->getChangedValues());

        $subObjs = $obj->getObjects('aName', 'aKey', SubObjectTest::class);

        $subObjs[0]->setValue('aSubName', 'aSubKey', 'aBarVal');

        $this->assertNotEmpty($subObjs[0]->getChangedValues());
        $this->assertNotEmpty($obj->getChangedValues());

        $this->assertEquals([
            'aName' => [
                'aKey' => [
                    ['aSubName' => ['aSubKey' => 'aBarVal']],
                ],
            ],
        ], $obj->getChangedValues());
    }

    public function testShouldReflectChangesDoneWhenSubObjectUnset()
    {
        $obj = new ObjectTest();
        $obj->setValues([
            'aName' => [
                'aKey' => [
                    'aSubName' => ['aSubKey' => 'aFooVal'],
                ],
            ],
        ]);

        //guard
        $this->assertEmpty($obj->getChangedValues());

        $obj->setObject('aName', 'aKey', null);

        $this->assertNotEmpty($obj->getChangedValues());

        $this->assertEquals(['aName' => ['aKey' => null]], $obj->getChangedValues());
    }

    public function testShouldNotReflectChangesIfObjectWasCloned()
    {
        $this->markTestIncomplete('This test covers the bug which is not yet fixed');

        $obj = new ObjectTest();
        $obj->setValues([
            'aName' => [
                'aKey' => [
                    'aSubName' => ['aSubKey' => 'aFooVal'],
                ],
            ],
        ]);

        //guard
        $this->assertEmpty($obj->getChangedValues());

        /** @var SubObjectTest $subObj */
        $subObj = $obj->getObject('aName', 'aKey', SubObjectTest::class);

        //guard
        $this->assertInstanceOf(SubObjectTest::class, $subObj);

        $clonedSubObj = clone $subObj;
        $clonedSubObj->setSelfValue('aSubKeyFoo', 'aBarVal');

        $this->assertEquals([
            'aName' => [
                'aKey' => [
                    'aSubName' => ['aSubKey' => 'aFooVal'],
                ],
            ],
        ], $obj->getValues());
    }
}

class ObjectTest
{
    use ValuesTrait {
        setSelfValue as public;
        getSelfValue as public;
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
        setSelfValue as public;
        getSelfValue as public;
    }
}
