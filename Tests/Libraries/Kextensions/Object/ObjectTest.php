<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Tests\Libraries\Kextensions\Object;

use Kextensions\Object\Object;

/**
 * Object
 *
 * @package     Kextensions
 * @since       2.0
 */
class ObjectTest extends PHPUnit_Framework_TestCase
{
    protected $object;

    protected function setUp()
    {
        $this->object = new Object();
    }
} 