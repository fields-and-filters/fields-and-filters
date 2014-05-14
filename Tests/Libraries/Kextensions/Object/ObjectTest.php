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

    public function testSetMethodChaining()
    {
        $actual = new Object();

        $expected = new Object();
        $expected->set('foo', 'bar');
        $expected->set('bar', 'foo');
        $expected->set('array', array(1,2,3));

        $actual
            ->set('foo', 'bar')
            ->set('bar', 'foo')
            ->set('array', array(1,2,3))
        ;

        $this->assertEquals($actual, $expected);
    }

    /**
     * [TODO] Write test:
     * testBindMethod
     * testBindMethodWithSetMethod
     * testBindMethodChaining
     * testBindMethodWithSetMethodChaining
     * testConstructor
     * testConstructorWithBind
     * testConstructorWithBindAndSetMethod
     * testConstructorWithBindAndSetMethodChaining
     */
}