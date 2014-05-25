<?php

/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Tests\Filter;

use Kextensions\Filter\RuleLocator;

/**
 * RuleLocatorTest
 *
 * @package     Kextensions
 * @since       2.0
 */
class RuleLocatorTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDefaultNamespace()
    {
        $namespace = RuleLocator::getNamespace('rule');
        $this->assertEquals($namespace, 'Kextensions\\Filter\\Rule');
    }

    public function testSetGetNamespaceMethod()
    {
        RuleLocator::setNamespace('test', 'Kextensions\\Tests\\Filter\\Rule');

        $namespace = RuleLocator::getNamespace('test');
        $this->assertEquals($namespace, 'Kextensions\\Tests\\Filter\\Rule');
    }

    public function testGetNotExistsNamespaceException()
    {
        try
        {
            RuleLocator::getNamespace('notexists');
        }
        catch (\Exception $e)
        {
            $this->assertInstanceOf('InvalidArgumentException', $e);
        }
    }

    public function testGetMethod()
    {
        $actual = RuleLocator::get('equals');
        $expected = new \Kextensions\Filter\Rule\Equals();

        $this->assertEquals($actual, $expected);
    }

    public function testSetNamespaceAndGetClass()
    {
        RuleLocator::setNamespace('fixtures', 'Kextensions\\Tests\\Filter\\Fixtures\\Rule');

        $actual = RuleLocator::get('fixtures.rule');
        $expected = new \Kextensions\Tests\Filter\Fixtures\Rule\Rule();

        $this->assertEquals($actual, $expected);
    }

    public function testGetSameClassMultiple()
    {
        $equals1 = RuleLocator::get('equals');
        $equals1->prepare(array(
            'foo' => 'bar'
        ), 'foo');

        $equals2 = RuleLocator::get('equals');

        $this->assertEquals($equals1, $equals2);
    }

    public function testGetNotExistsClassException()
    {
        RuleLocator::setNamespace('fixtures', 'Kextensions\\Tests\\Filter\\Fixtures\\Rule');

        try
        {
            RuleLocator::get('fixtures.notExistsClass');
        }
        catch (\Exception $e)
        {
            $this->assertStringMatchesFormat('Class "%s" not exists', $e->getMessage());
            $this->assertInstanceOf('Exception', $e);
        }
    }

    public function testGetClassWithWrongInstanceException()
    {
        RuleLocator::setNamespace('fixtures', 'Kextensions\\Tests\\Filter\\Fixtures\\Rule');

        try
        {
            RuleLocator::get('fixtures.classWithWrnogInstance');
        }
        catch (\Exception $e)
        {
            $this->assertStringMatchesFormat('Class "%s" not instance of "%s"', $e->getMessage());
            $this->assertInstanceOf('InvalidArgumentException', $e);
        }
    }

    public function testGetClassWithoutValidateMethodException()
    {
        RuleLocator::setNamespace('fixtures', 'Kextensions\\Tests\\Filter\\Fixtures\\Rule');

        try
        {
            RuleLocator::get('fixtures.classWithoutValidateMethod');
        }
        catch (\Exception $e)
        {
            $this->assertStringMatchesFormat('The method "%s::%s" is not callable', $e->getMessage());
            $this->assertInstanceOf('InvalidArgumentException', $e);
        }
    }
}