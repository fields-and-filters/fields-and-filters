<?php

/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Tests\Filter;

use Kextensions\Filter\Filter;

/**
 * FilterTest
 *
 * @package     Kextensions
 * @since       2.0
 */
class FilterTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorException()
    {
        try
        {
            new Filter($this->getData());
        }
        catch (\Exception $e)
        {
            $this->assertStringMatchesFormat('Argument %i passed to %s must implement interface Iterator, array given', $e->getMessage());
            $this->assertInstanceOf('PHPUnit_Framework_Error', $e);
            return;
        }

        $this->fail(sprintf('An expected exception "%s" has not been raised.', 'PHPUnit_Framework_Error'));
    }

    public function testAddRuleMethod()
    {
        $data = new \ArrayIterator($this->getData());
        $rule = $this->getRule('equals', 'foo');

        $filter = new Filter($data);
        $filter->addRule($rule['name'], $rule['field']);

        $reflection = new \ReflectionClass($filter);
        $property = $reflection->getProperty('rules');
        $property->setAccessible(true);

        $this->assertEquals($property->getValue($filter), array($rule));
    }

    public function testAddRuleMethodMultiple()
    {
        $rules = array();
        $data = new \ArrayIterator($this->getData());
        $filter = new Filter($data);

        $rule = $this->getRule('equals', 'foo');
        $filter->addRule($rule['name'], $rule['field']);
        array_push($rules, $rule);

        $rule = $this->getRule('other', 'bar');
        $filter->addRule($rule['name'], $rule['field']);
        array_push($rules, $rule);

        $rule = $this->getRule('otherArray', 'array');
        $filter->addRule($rule['name'], $rule['field']);
        array_push($rules, $rule);

        $reflection = new \ReflectionClass($filter);
        $property = $reflection->getProperty('rules');
        $property->setAccessible(true);

        $this->assertEquals($property->getValue($filter), $rules);
    }

    public function testAddRuleMethodChaining()
    {
        $rules = array();
        $data = new \ArrayIterator($this->getData());
        $filter = new Filter($data);

        $rule1 = $this->getRule('equals', 'foo');
        $rule2 = $this->getRule('other', 'bar');
        $rule3 = $this->getRule('otherArray', 'array');
        array_push($rules, $rule1, $rule2, $rule3);

        $filter
            ->addRule($rule1['name'], $rule1['field'])
            ->addRule($rule2['name'], $rule2['field'])
            ->addRule($rule3['name'], $rule3['field'])
        ;

        $reflection = new \ReflectionClass($filter);
        $property = $reflection->getProperty('rules');
        $property->setAccessible(true);

        $this->assertEquals($property->getValue($filter), $rules);
    }

    public function testClearRuleMethod()
    {
        $data = new \ArrayIterator($this->getData());
        $filter = new Filter($data);

        $rule1 = $this->getRule('equals', 'foo');
        $rule2 = $this->getRule('other', 'bar');
        $rule3 = $this->getRule('otherArray', 'array');

        $filter
            ->addRule($rule1['name'], $rule1['field'])
            ->addRule($rule2['name'], $rule2['field'])
            ->addRule($rule3['name'], $rule3['field'])
        ;

        $filter->clearRule();

        $reflection = new \ReflectionClass($filter);
        $property = $reflection->getProperty('rules');
        $property->setAccessible(true);

        $this->assertEquals($property->getValue($filter), array());
    }

    public function testClearRuleMethodChaining()
    {
        $data = new \ArrayIterator($this->getData());
        $filter = new Filter($data);

        $rule1 = $this->getRule('equals', 'foo');
        $rule2 = $this->getRule('other', 'bar');
        $rule3 = $this->getRule('otherArray', 'array');

        $filter
            ->addRule($rule1['name'], $rule1['field'])
            ->addRule($rule2['name'], $rule2['field'])
            ->addRule($rule3['name'], $rule3['field'])
            ->clearRule()
            ->addRule($rule1['name'], $rule1['field'])
        ;

        $reflection = new \ReflectionClass($filter);
        $property = $reflection->getProperty('rules');
        $property->setAccessible(true);

        $this->assertEquals($property->getValue($filter), array($rule1));
    }

    public function testFilterOnceRule()
    {

        $data = $this->getData();
        $filter = new Filter(new \ArrayIterator($data));
        $filter->addRule('equals', 'foo', Filter::IS, 'foo');

        unset($data[2], $data[3]);

        $this->assertEquals($filter->filter(), $data);

        $data = $this->getData();
        unset($data[1], $data[4]);

        $this->assertNotEquals($filter->filter(), $data);
    }

    public function testFilterMethodMultieRules()
    {
        $data = $this->getData();
        $filter = new Filter(new \ArrayIterator($data));

        $filter
            ->addRule('equals', 'foo', Filter::IS, 'foo')
            ->addRule('equals', 'array', Filter::IS, array(3,4))
        ;

        unset($data[1], $data[2], $data[3]);

        $this->assertEquals($filter->filter(), $data);

        $data = $this->getData();
        unset($data[4]);

        $this->assertNotEquals($filter->filter(), $data);
    }

    public function testFilterMethodMultiple()
    {
        $data = $this->getData();
        $filter = new Filter(new \ArrayIterator($data));

        $filter->addRule('equals', 'foo', Filter::IS, 'foobar');

        unset($data[1], $data[4]);

        $this->assertEquals($filter->filter(), $data);

        $filter->addRule('equals', 'bar', Filter::IS, 'barfoo');

        unset($data[3]);

        $this->assertEquals($filter->filter(), $data);

        $data = $this->getData();
        $this->assertNotEquals($filter->filter(), $data);
    }

    public function testFilterForeach()
    {
        $data = $this->getData();
        $filter = new Filter(new \ArrayIterator($data));

        $filter->addRule('equals', 'array', Filter::IS, array(1,2));

        $actual = array();
        foreach($filter AS $key => $value)
        {
            $actual[$key] = $value;
        }

        unset($data[2], $data[4]);

        $this->assertEquals($actual, $data);
    }

    public function testFilterReturnObjectClass()
    {
        $data = $this->getData();
        $filter = new Filter(new \ArrayIterator($data));

        $filter->addRule('equals', 'foo', Filter::IS, 'foobar');

        $object = new \Kextensions\Object\Object($filter);

        unset($data[1], $data[4]);

        $this->assertEquals($object, new \Kextensions\Object\Object($data));
    }

    protected function getData()
    {
        return array(
            1 => (object) array(
                'foo' => 'foo',
                'bar' => 'bar',
                'array' => array(1,2)
            ),
            2 => (object) array(
                'foo' => 'foobar',
                'bar' => 'barfoo',
                'array' => array(3,4)
            ),
            3 => (object) array(
                'foo' => 'foobar',
                'bar' => 'bar',
                'array' => array(1,2)
            ),
            4 => (object) array(
                'foo' => 'foo',
                'bar' => 'barfoo',
                'array' => array(3,4)
            ),

        );
    }

    protected function getRule($name, $field, array $params = array(), $method = Filter::IS)
    {
        return array(
            'name' => $name,
            'field' => $field,
            'params' => $params,
            'method' => $method
        );
    }
}