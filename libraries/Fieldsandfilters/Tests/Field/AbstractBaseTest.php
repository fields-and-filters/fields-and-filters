<?php
/**
 * @package     Fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Fieldsandfilters\Tests\Field;

/**
 * Abstract Base Test
 *
 * @package     Fieldsandfilters
 * @since       2.0
 */
class AbstractBaseTest extends \PHPUnit_Framework_TestCase
{
    protected static $fieldAbstractClass = 'Fieldsandfilters\\Field\\AbstractBase';
    protected static $contentTypeAbstractClass = 'Fieldsandfilters\\Content\\AbstractContentType';

    public function testIsConstants()
    {
        $base = $this->getMockForAbstractClass(self::$fieldAbstractClass);

        $this->assertFalse($base::isField);
        $this->assertFalse($base::isFilter);
        $this->assertFalse($base::isStatic);
    }

    public function testSetGetContentType()
    {
        $contentType = $this->getMockForAbstractClass(self::$contentTypeAbstractClass);
        $actual = $this->getMockForAbstractClass(self::$fieldAbstractClass);

        $this->assertNull($actual->getContentType());

        $actual->setContentType($contentType);

        $this->assertInstanceOf(self::$contentTypeAbstractClass, $actual->getContentType());
    }

    public function testSetContentTypeException()
    {
        $actual = $this->getMockForAbstractClass(self::$fieldAbstractClass);

        try
        {
            $actual->setContentType(new \stdClass());
        }
        catch (\Exception $e)
        {
            $this->assertStringMatchesFormat('Argument 1 passed to Fieldsandfilters\Field\AbstractBase::setContentType() must be an instance of Fieldsandfilters\Content\AbstractContentType, instance of stdClass given, called in %s on line %s and defined', $e->getMessage());
            $this->assertInstanceOf('PHPUnit_Framework_Error', $e);
            return;
        }

        $this->fail(sprintf('An expected exception "%s" has not been raised.', 'PHPUnit_Framework_Error'));
    }

    public function testToStringMethod()
    {
        $actual = $this->getMockForAbstractClass(self::$fieldAbstractClass);

        $expected = '<span>rendered</span>';

        $actual->expects($this->any())
            ->method('render')
            ->will($this->returnValue($expected));

        $this->assertSame($expected, (string) $actual);
        $this->assertNotSame('Not Same', (string) $actual);
    }
}