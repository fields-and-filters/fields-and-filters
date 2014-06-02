<?php
/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Tests\Rule\Rule;

use Kextensions\Rule\Rule\Equals;

/**
 * EqualsTest
 *
 * @package     Kextensions
 * @since       2.0
 */
class EqualsTest extends \PHPUnit_Framework_TestCase
{
    protected $rule;

    protected function setUp()
    {
        $this->rule = new Equals();
    }

    public function testValidateMethodString()
    {
        $data = $this->getData();
        $this->rule->prepare($data, 'foo');

        $this->assertTrue($this->rule->is($data->foo));

        $this->assertFalse($this->rule->is($data->bar));
    }

    public function testValidateMethodInteger()
    {
        $data = $this->getData();
        $this->rule->prepare($data, '1');

        $this->assertTrue($this->rule->is($data->{1}));

        $this->assertFalse($this->rule->is($data->{2}));
    }

    public function testValidateMethodArray()
    {
        $data = $this->getData();
        $this->rule->prepare($data, 'array1');

        $this->assertTrue($this->rule->is($data->array1));

        $this->assertFalse($this->rule->is($data->array2));
    }

    public function testValidateMethodArrayDifferentOrder()
    {
        $data = $this->getData();
        $this->rule->prepare($data, 'array1');

        $this->assertTrue($this->rule->is(array_reverse($data->array1)));

        $this->assertFalse($this->rule->is(array_reverse($data->array2)));
    }

    public function testValidateMethodObject()
    {
        $data = $this->getData();
        $this->rule->prepare($data, 'object1');

        $this->assertTrue($this->rule->is($data->object1));

        $this->assertFalse($this->rule->is($data->object2));
    }

    public function testConditionIsMethodString()
    {
        $data = $this->getData();

        $actual = $this->rule->queryIs($data->foo);
        $expected = sprintf(' = %s ', $data->foo);

        $this->assertEquals($actual, $expected);
    }

    public function testConditionIsMethodArray()
    {
        $data = $this->getData();

        $actual = $this->rule->queryIs($data->array1);
        $expected = sprintf(' IN(%s) ', implode(',', $data->array1));

        $this->assertEquals($actual, $expected);
    }

    public function testConditionNotMethodString()
    {
        $data = $this->getData();

        $actual = $this->rule->queryNot($data->foo);
        $expected = sprintf(' != %s ', $data->foo);

        $this->assertEquals($actual, $expected);
    }

    public function testConditionNotMethodArray()
    {
        $data = $this->getData();

        $actual = $this->rule->queryNot($data->array1);
        $expected = sprintf(' NOT IN(%s) ', implode(',', $data->array1));

        $this->assertEquals($actual, $expected);
    }

    protected function getData()
    {
        $data = new \stdClass();

        $data->foo = 'foo';
        $data->bar = 'bar';

        $data->{1} = 1;
        $data->{2} = 2;

        $data->array1 = array(
            'foo' => 'foo',
            'bar' => 'bar'
        );
        $data->array2 = array(
            1 => 1,
            2 => 2
        );

        $data->object1 = (object) $data->array1;
        $data->object2 = (object) $data->array2;

        return $data;
    }
}