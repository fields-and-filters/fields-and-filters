<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Tests\Libraries\Kextensions\Object;

use Kextensions\Object\Object;
use Kextensions\Object\Table;

/**
 * List
 *
 * @package     Kextensions
 * @since       2.0
 */
class ListTest extends \PHPUnit_Framework_TestCase
{
    public function testGetMethodReturnedInstanceOfObject()
    {
        $object = new Object(array(
            'foo' => 'bar',
            'bar' => 'foo'
        ));

        $table = new Table();
        $table->set('object', $object);

        $this->assertInstanceOf('Kextensions\\Object\\Object', $table->get('object'));
        $this->assertEquals($object, $table->get('object'));
    }
}