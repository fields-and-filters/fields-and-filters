<?php
/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Tests\Filter\Rule;

use Kextensions\Filter\Rule\Equals;

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

    public function testAcceptMethodString()
    {
        $data = $this->getData();
        $this->rule->prepare($data, 'foo');

        $this->assertTrue($this->rule->is($data->foo));

        $this->assertFalse($this->rule->is($data->bar));
    }

    public function testAcceptMethodInteger()
    {
        $data = $this->getData();
        $this->rule->prepare($data, '1');

        $this->assertTrue($this->rule->is($data->{1}));

        $this->assertFalse($this->rule->is($data->{2}));
    }

    public function testAcceptMethodArray()
    {
        $data = $this->getData();
        $this->rule->prepare($data, 'array1');

        $this->assertTrue($this->rule->is($data->array1));

        $this->assertFalse($this->rule->is($data->array2));
    }

    public function testAcceptMethodObject()
    {
        $data = $this->getData();
        $this->rule->prepare($data, 'object1');

        $this->assertTrue($this->rule->is($data->object1));

        $this->assertFalse($this->rule->is($data->object2));
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