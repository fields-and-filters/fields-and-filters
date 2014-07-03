<?php
/**
* @package     Fieldsandfilters
* @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
* @license     GNU General Public License version 3 or later; see License.txt
* @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
*/

namespace Fieldsandfilters\Tests\Field;

/**
 * Abstract Field Test
 *
 * @package     Fieldsandfilters
 * @since       2.0
 */
class AbstractFieldTest extends \PHPUnit_Framework_TestCase
{
    protected static $fieldAbstractClass = 'Fieldsandfilters\\Field\\AbstractField';
    protected static $contentAbstractClass = 'Fieldsandfilters\\Content\\AbstractContent';

    public function testIsConstants()
    {
        $base = $this->getMockForAbstractClass(self::$fieldAbstractClass);

        $this->assertTrue($base::isField);
        $this->assertFalse($base::isFilter);
        $this->assertFalse($base::isStatic);
    }

    public function testSetGetContent()
    {
        $content = $this->getMockForAbstractClass(self::$contentAbstractClass);
        $actual = $this->getMockForAbstractClass(self::$fieldAbstractClass);

        $this->assertNull($actual->getContent());

        $actual->setContent($content);

        $this->assertInstanceOf(self::$contentAbstractClass, $actual->getContent());
    }

    public function testSetContentException()
    {
        $actual = $this->getMockForAbstractClass(self::$fieldAbstractClass);

        try
        {
            $actual->setContent(new \stdClass());
        }
        catch (\Exception $e)
        {
            $this->assertStringMatchesFormat('Argument 1 passed to Fieldsandfilters\Field\AbstractField::setContent() must be an instance of Fieldsandfilters\Content\AbstractContent, instance of stdClass given, called in %s on line %s and defined', $e->getMessage());
            $this->assertInstanceOf('PHPUnit_Framework_Error', $e);
            return;
        }

        $this->fail(sprintf('An expected exception "%s" has not been raised.', 'PHPUnit_Framework_Error'));
    }

    public function testGetData()
    {
        $expected = 'foo';

        $content = $this->getMockForAbstractClass(self::$contentAbstractClass, array(), '', true, true, true, array('getData'));
        $actual = $this->getMockForAbstractClass(self::$fieldAbstractClass);

        $content->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($expected));

        $actual->setContent($content);

        $this->assertEquals($expected, $actual->getData());
    }

    public function testGetDataException()
    {
        $actual = $this->getMockForAbstractClass(self::$fieldAbstractClass);

        try
        {
            $actual->getData();
        }
        catch (\Exception $e)
        {
            $this->assertStringMatchesFormat(sprintf('Content property is not instance of "%s".', self::$contentAbstractClass), $e->getMessage());
            $this->assertInstanceOf('InvalidArgumentException', $e);
            return;
        }

        $this->fail(sprintf('An expected exception "%s" has not been raised.', 'InvalidArgumentException'));
    }

    public function testRetrunMethod()
    {
        $actual = $this->getMockForAbstractClass(self::$fieldAbstractClass);
        $content = $this->getMockForAbstractClass(self::$contentAbstractClass);

        $actual->setContent($content);

        $this->assertSame('', $actual->render());
        $this->assertNotSame('Not Same', $actual->render());
    }

    public function testRetrunMethodException()
    {
        $actual = $this->getMockForAbstractClass(self::$fieldAbstractClass);

        try
        {
            $actual->render();
        }
        catch (\Exception $e)
        {
            $this->assertStringMatchesFormat(sprintf('Content property is not instance of "%s".', self::$contentAbstractClass), $e->getMessage());
            $this->assertInstanceOf('InvalidArgumentException', $e);
            return;
        }

        $this->fail(sprintf('An expected exception "%s" has not been raised.', 'InvalidArgumentException'));
    }
}