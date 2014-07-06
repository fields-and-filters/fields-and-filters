<?php
/**
 * @package     Fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Fieldsandfilters\Tests\Field;

use Fieldsandfilters\Field\FieldList;
use Fieldsandfilters\Field\AbstractBase;

/**
 * Field List Test
 *
 * @package     Fieldsandfilters
 * @since       2.0
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testSetInstance()
    {
        $objectList = new FieldList();

        $reflection = new \ReflectionClass($objectList);
        $property = $reflection->getProperty('setInstance');
        $property->setAccessible(true);

        $this->assertEquals($property->getValue($objectList), AbstractBase::_CLASS_);
    }
}