<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Tests\Libraries\Kextensions\Object;

use Kextensions\Object\Object;

/**
 * Object
 *
 * @package     Kextensions
 * @since       2.0
 */
class ObjectTest extends \PHPUnit_Framework_TestCase
{
    public function testIssetMethod()
    {
        $object = new Object();

        $this->assertFalse(isset($object->foo));

        $object->set('foo', 'bar');

        $this->assertTrue(isset($object->foo));
    }

    public function testUnsetMethod()
    {
        $object = new Object();
        $object->set('foo', 'bar');

        $this->assertTrue(isset($object->foo));

        unset($object->foo);

        $this->assertFalse(isset($object->foo));
    }

    public function testGetSetByProperty()
    {
        $object = new Object();

        $this->assertNotEquals('bar', $object->foo);
        $this->assertNotEquals('bar', $object->get('foo'));

        $object->foo = 'bar';

        $this->assertEquals('bar', $object->foo);
        $this->assertEquals('bar', $object->get('foo'));
    }

    public function testSetGetMethod()
    {
        $object = new Object();

        $this->assertNotEquals('bar', $object->get('foo'));
        $this->assertNotEquals('bar', $object->foo);

        $object->set('foo', 'bar');

        $this->assertEquals('bar', $object->get('foo'));
        $this->assertEquals('bar', $object->foo);
    }

    public function testSetPropertyMultiple()
    {
        $object = new Object();

        $this->assertFalse(isset($object->foo) || isset($object->bar) || isset($object->array));

        $object->foo = 'bar';
        $object->bar = 'foo';
        $object->array = array(1,2,3);

        $this->assertEquals('bar', $object->foo);
        $this->assertEquals('foo', $object->bar);
        $this->assertEquals(array(1,2,3), $object->array);
    }

    public function testSetMethodMultiple()
    {
        $object = new Object();

        $this->assertFalse(isset($object->foo) || isset($object->bar) || isset($object->array));

        $object->set('foo', 'bar');
        $object->set('bar', 'foo');
        $object->set('array', array(1,2,3));

        $this->assertEquals('bar', $object->get('foo'));
        $this->assertEquals('foo', $object->get('bar'));
        $this->assertEquals(array(1,2,3), $object->get('array'));
    }

    public function testSetMethodChaining()
    {
        $actual = new Object();
        $actual
            ->set('foo', 'bar')
            ->set('bar', 'foo')
            ->set('array', array(1,2,3))
        ;

        $expected = new Object();
        $expected->set('foo', 'bar');
        $expected->set('bar', 'foo');
        $expected->set('array', array(1,2,3));

        $this->assertEquals($actual, $expected);
    }

    public function testPropertyNotExistsAndReturNull()
    {
        $object = new Object();

        $this->assertFalse(isset($object->foo));
        $this->assertNull($object->foo);
    }

    public function testPropertyExistsButIsNull()
    {
        $object = new Object();
        $object->foo = null;

        $this->assertTrue(isset($object->foo));
        $this->assertNull($object->foo);
    }

    public function testConstructorBindMethod()
    {
        $actual = new Object(array(
            'foo' => 'bar',
            'bar' => 'foo',
            'array' => array(1,2,3)
        ));

        $expected = new Object();
        $expected->set('foo', 'bar');
        $expected->set('bar', 'foo');
        $expected->set('array', array(1,2,3));

        $this->assertEquals($actual, $expected);
    }

    public function testBindMethodWithSetMethod()
    {
        $actual = new Object(array(
            'foo' => 'bar',
            'bar' => 'foo',
            'array' => array(1,2,3)
        ));

        $actual
            ->set('object', new \stdClass())
            ->set('integer', 123);

        $expected = new Object(array(
            'foo' => 'bar',
            'bar' => 'foo',
            'array' => array(1,2,3),
            'object' => new \stdClass(),
            'integer' => 123
        ));

        $this->assertEquals($actual, $expected);
    }

    public function testBindMethodMultiple()
    {
        $actual = new Object(array(
            'foo' => 'bar',
            'bar' => 'foo',
            'array' => array(1,2,3)
        ));

        $actual->bind(array(
            'foo' => 'bar2',
            'object' => new \stdClass(),
            'integer' => 123
        ));

        $expected = new Object(array(
            'foo' => 'bar2',
            'bar' => 'foo',
            'array' => array(1,2,3),
            'object' => new \stdClass(),
            'integer' => 123
        ));

        $this->assertEquals($actual, $expected);
    }

    public function testBindMethodChaining()
    {
        $actual = new Object();

        $actual
            ->bind(array(
                'foo' => 'bar',
                'bar' => 'foo',
                'array' => array(1,2,3)
            ))
            ->bind(array(
                'foo' => 'bar2',
                'object' => new \stdClass(),
                'integer' => 123
            ))
        ;

        $expected = new Object(array(
            'foo' => 'bar2',
            'bar' => 'foo',
            'array' => array(1,2,3),
            'object' => new \stdClass(),
            'integer' => 123
        ));

        $this->assertEquals($actual, $expected);
    }
}