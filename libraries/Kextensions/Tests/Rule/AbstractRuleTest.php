<?php

/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Tests\Rule;

/**
 * Abstract Rule Test
 *
 * @package     Kextensions
 * @since       2.0
 */
class AbstractRuleTest extends \PHPUnit_Framework_TestCase
{
    protected $abstractClass = '\\Kextensions\\Rule\\AbstractRule';

    public function testPreapreMethod()
    {
        $data = new \stdClass();
        $data->foo = 'bar';
        $field = 'foo';

        $rule = $this->getMockForAbstractClass($this->abstractClass);
        $rule->prepare($data, $field);

        $reflection = new \ReflectionClass($rule);
        $property = $reflection->getProperty('data');
        $property->setAccessible(true);

        $this->assertEquals($property->getValue($rule), $data);

        $property = $reflection->getProperty('field');
        $property->setAccessible(true);
        $this->assertEquals($property->getValue($rule), $field);
    }

    public function testGetValueMethod()
    {
        $data = new \stdClass();
        $data->foo = 'bar';
        $field = 'foo';

        $rule = $this->getMockForAbstractClass($this->abstractClass);
        $rule->prepare($data, $field);

        $this->assertEquals($rule->getValue('foo'), $data->foo);
    }

    public function testIsMethod()
    {
        $rule = $this->getMock($this->abstractClass, array('validate'));

        $rule->expects($this->any())
            ->method('validate')
            ->will($this->returnValue(TRUE));

        $this->assertTrue($rule->is());
    }

    public function testFalseIsMethod()
    {
        $rule = $this->getMock($this->abstractClass, array('validate'));

        $rule->expects($this->any())
            ->method('validate')
            ->will($this->returnValue(FALSE));

        $this->assertFalse($rule->is());
    }

    public function testIsNotMethod()
    {
        $rule = $this->getMock($this->abstractClass, array('validate'));

        $rule->expects($this->any())
            ->method('validate')
            ->will($this->returnValue(TRUE));

        $this->assertFalse($rule->isNot());
    }

    public function testTrueIsNotMethod()
    {
        $rule = $this->getMock($this->abstractClass, array('validate'));

        $rule->expects($this->any())
            ->method('validate')
            ->will($this->returnValue(FALSE));

        $this->assertTrue($rule->isNot());
    }

    public function testQueryIsMethod()
    {
        $rule = $this->getMock($this->abstractClass, array('conditionIs'));

        $rule->expects($this->any())
            ->method('conditionIs')
            ->will($this->returnValue(''));

        $this->assertEquals($rule->conditionIs(), '');
        $this->assertTrue(is_string($rule->conditionIs()));
    }

    public function testQueryNotMethod()
    {
        $rule = $this->getMock($this->abstractClass, array('conditionNot'));

        $rule->expects($this->any())
            ->method('conditionNot')
            ->will($this->returnValue(''));

        $this->assertEquals($rule->conditionNot(), '');
        $this->assertTrue(is_string($rule->conditionNot()));
    }
}