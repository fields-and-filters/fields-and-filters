<?php
/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Object;

namespace Kextensions\Tests\Object;

use Kextensions\Object\Object;
use Kextensions\Object\ObjectList;

/**
 * Object List Test
 *
 * @package     Kextensions
 * @since       2.0
 */
class ObjectListTest extends \PHPUnit_Framework_TestCase
{
    public function testSetMethod()
    {
        $expected = new Object(array(
            'foo' => 'foo',
            'bar' => 'bar'
        ));

        $objectList = new ObjectList();
        $objectList->set('expected', $expected);

        $this->assertEquals($expected, $objectList->get('expected'));
        $this->assertNull($objectList->get('not_exists'));
    }

    public function testSetStringException()
    {
        $objectList = new ObjectList();

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
        $objectList = new ObjectList();

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
}