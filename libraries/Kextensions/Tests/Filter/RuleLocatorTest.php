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
    protected $namespaceRule = '\\Kextensions\\Tests\\Filter\\Fixtures\\Rule';

    public function testGetDefaultNamespace()
    {
        $namespace = RuleLocator::getNamespace('rule');
        $this->assertEquals($namespace, 'Kextensions\\Filter\\Rule');
    }

    public function testSetGetNamespaceMethod()
    {
        RuleLocator::setNamespace('test', $this->namespaceRule);

        $namespace = RuleLocator::getNamespace('test');
        $this->assertEquals($namespace, $this->namespaceRule);
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
            return;
        }

        $this->fail(sprintf('An expected exception "%s" has not been raised.', 'InvalidArgumentException'));
    }

    public function testGetMethod()
    {
        $actual = RuleLocator::get('equals');
        $expected = new \Kextensions\Filter\Rule\Equals();

        $this->assertEquals($actual, $expected);
    }

    public function testSetNamespaceAndGetClass()
    {
        RuleLocator::setNamespace('fixtures', $this->namespaceRule);

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
        RuleLocator::setNamespace('fixtures', $this->namespaceRule);

        try
        {
            RuleLocator::get('fixtures.notExistsClass');
        }
        catch (\Exception $e)
        {
            $this->assertStringMatchesFormat('Class "%s" not exists', $e->getMessage());
            $this->assertInstanceOf('Exception', $e);
            return;
        }

        $this->fail(sprintf('An expected exception "%s" has not been raised.', 'Exception'));
    }

    public function testGetClassWithWrongInstanceException()
    {
        RuleLocator::setNamespace('fixtures', $this->namespaceRule);

        try
        {
            RuleLocator::get('fixtures.classWithWrnogInstance');
        }
        catch (\Exception $e)
        {
            $this->assertStringMatchesFormat('Class "%s" not instance of "%s"', $e->getMessage());
            $this->assertInstanceOf('InvalidArgumentException', $e);
            return;
        }

        $this->fail(sprintf('An expected exception "%s" has not been raised.', 'InvalidArgumentException'));
    }

    public function testGetClassWithoutValidateMethodException()
    {
        RuleLocator::setNamespace('fixtures', $this->namespaceRule);

        try
        {
            RuleLocator::get('fixtures.classWithoutValidateMethod');
        }
        catch (\Exception $e)
        {
            $this->assertStringMatchesFormat('The method "%s::%s" is not callable', $e->getMessage());
            $this->assertInstanceOf('InvalidArgumentException', $e);
            return;
        }

        $this->fail(sprintf('An expected exception "%s" has not been raised.', 'InvalidArgumentException'));
    }
}