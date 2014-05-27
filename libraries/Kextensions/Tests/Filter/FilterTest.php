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
        $data = new \ArrayIterator($this->getData());
        $filter = new Filter($data);
        $filter->addRule('equals', 'foo', array('foo'));

        // [TODO] Dokończyć

        echo '<pre>';
        print_r($filter->getInnerIterator()->getArrayCopy());
        echo '</pre>';

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