<?php

/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Tests\Rule;

use Kextensions\Rule\Locator;

/**
 * Locator Test
 *
 * @package     Kextensions
 * @since       2.0
 */
class LocatorTest extends \PHPUnit_Framework_TestCase
{
    protected $namespaceRule = 'Kextensions\\Tests\\Rule\\Fixtures\\Rule';

    public static function setUpBeforeClass()
    {
        $reflection = new \ReflectionClass('Kextensions\\Rule\\Locator');
        $property = $reflection->getProperty('registry');
        $property->setAccessible(true);
        $property->setValue(array());
    }

    public function testGetDefaultNamespace()
    {
        $namespace = Locator::getNamespace('_default_');
        $this->assertEquals($namespace, 'Kextensions\\Rule\\Rule');
    }

    public function testSetGetNamespaceMethod()
    {
        Locator::setNamespace('test', $this->namespaceRule);

        $namespace = Locator::getNamespace('test');
        $this->assertEquals($namespace, $this->namespaceRule);
    }

    public function testGetNotExistsNamespaceException()
    {
        try
        {
            Locator::getNamespace('notexists');
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
        $actual = Locator::get('equals');
        $expected = new \Kextensions\Rule\Rule\Equals();

        $this->assertEquals($actual, $expected);
    }

    public function testSetNamespaceAndGetClass()
    {
        Locator::setNamespace('fixtures', $this->namespaceRule);

        $actual = Locator::get('fixtures.rule');
        $expected = new \Kextensions\Tests\Rule\Fixtures\Rule\Rule();

        $this->assertEquals($actual, $expected);
    }

    public function testGetSameClassMultiple()
    {
        $equals1 = Locator::get('equals');
        $equals1->prepare(array(
            'foo' => 'bar'
        ), 'foo');

        $equals2 = Locator::get('equals');

        $this->assertEquals($equals1, $equals2);
    }

    public function testGetNotExistsClassException()
    {
        Locator::setNamespace('fixtures', $this->namespaceRule);

        try
        {
            Locator::get('fixtures.notExistsClass');
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
        Locator::setNamespace('fixtures', $this->namespaceRule);

        try
        {
            Locator::get('fixtures.classWithWrnogInstance');
        }
        catch (\Exception $e)
        {
            $this->assertStringMatchesFormat('Class "%s" not instance of "%s"', $e->getMessage());
            $this->assertInstanceOf('InvalidArgumentException', $e);
            return;
        }

        $this->fail(sprintf('An expected exception "%s" has not been raised.', 'InvalidArgumentException'));
    }
}