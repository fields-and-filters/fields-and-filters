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

    /**
     * [TODO]
     * testGetMethod
     * testSetNamespaceAndGetClass
     * testGetSameClassMultiple
     * testGetNotExistsClassException
     * testGetClassWithWrongInstanceException
     **/
}