<?php
/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Tests\Object;

use Kextensions\Object\Object;
use Kextensions\Object\Collection;

/**
 * Collection Test
 *
 * @package     Kextensions
 * @since       2.0
 */
class CollectionTests extends \PHPUnit_Framework_TestCase
{
    public function testSetMethod()
    {
        $expected = new Object(array(
            'foo' => 'foo',
            'bar' => 'bar'
        ));

        $objectList = new Collection();
        $objectList->set('expected', $expected);

        $this->assertEquals($expected, $objectList->get('expected'));
        $this->assertNull($objectList->get('not_exists'));
    }

    public function testSetStringException()
    {
        $objectList = new Collection();

        try
        {
            $objectList->set('foo', 'foo');
        }
        catch (\Exception $e)
        {
            $this->assertStringMatchesFormat('Value "string" is not instance of "%s".', $e->getMessage());
            $this->assertInstanceOf('InvalidArgumentException', $e);
            return;
        }

        $this->fail(sprintf('An expected exception "%s" has not been raised.', 'InvalidArgumentException'));
    }

    public function testSetClassWithWrongInstanceException()
    {
        $objectList = new Collection();

        try
        {
            $objectList->set('foo', new \stdClass());
        }
        catch (\Exception $e)
        {
            $this->assertStringMatchesFormat('Value "stdClass" is not instance of "%s".', $e->getMessage());
            $this->assertInstanceOf('InvalidArgumentException', $e);
            return;
        }

        $this->fail(sprintf('An expected exception "%s" has not been raised.', 'InvalidArgumentException'));
    }

    public function testFirstMethod()
    {
        $collection = $this->getCollection();
        $actual = $collection->first();

        $this->assertEquals($collection->get(1), $actual);
        $this->assertNotEquals($collection->get(4), $actual);
    }

    public function testLastMethod()
    {
        $collection = $this->getCollection();
        $actual = $collection->last();

        $this->assertEquals($collection->get(4), $actual);
        $this->assertNotEquals($collection->get(1), $actual);
    }

    public function testMapReturnedObjectInstance()
    {
        $collection = $this->getCollection();
        $expected = new Object();

        $actual = $collection->map(function($object) {
            return $object->name;
        });

        $this->assertInstanceOf(get_class($expected), $actual);
    }

    public function testMapMethod()
    {
        $collection = $this->getCollection();
        $expected = new Object();

        foreach ($collection AS $key => $object)
        {
            $expected->set($key, $object->name);
        }

        $actual = $collection->map(function(Object $object) {
            return $object->name;
        });

        $this->assertEquals($expected, $actual);
    }

    public function testFilterMethod()
    {
        $collection = $this->getCollection();

        $expected = clone $collection;
        unset($expected->{2}, $expected->{4});

        $actual = $collection->filter(function(Object $object) {
            return $object->name == 'foo' || $object->name == 'foobar';
        });

        $this->assertInstanceOf(get_class($expected), $actual);
        $this->assertEquals($expected, $actual);

        $expected = clone $collection;
        unset($expected->{1}, $expected->{3}, $expected->{4});

        $actual = $collection->filter(function(Object $object) {
            return $object->name == 'bar' && $object->type == 'type2';
        });

        $this->assertEquals($expected, $actual);
    }

    public function testFilterReturnedEmpty()
    {
        $collection = $this->getCollection();

        $expected = clone $collection;
        $expected->clear();

        $actual = $collection->filter(function(Object $object) {
            return $object->name == 'foo' && $object->type == 'type4';
        });

        $this->assertInstanceOf(get_class($expected), $actual);
        $this->assertEquals($expected, $actual);
        $this->assertTrue($actual->isEmpty());
    }

    protected function getCollection()
    {
        return new Collection(array(
            1 => new Object(array(
                    'name' => 'foo',
                    'type' => 'type1'
                )),
            2 => new Object(array(
                    'name' => 'bar',
                    'type' => 'type2'
                )),
            3 => new Object(array(
                    'name' => 'foobar',
                    'type' => 'type1'
                )),
            4 => new Object(array(
                    'name' => 'barfoo',
                    'type' => 'type2'
                ))
        ));
    }
}