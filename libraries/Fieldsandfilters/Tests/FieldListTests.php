<?php
/**
 * @package     Fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Fieldsandfilters\Tests\Field;

use Fieldsandfilters\Field\FieldList;
use Fieldsandfilters\Field\AbstractField;

/**
 * Field List Tests
 *
 * @package     Fieldsandfilters
 * @since       2.0
 */
class FieldListTests extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $list = new FieldList();
        print_r(AbstractField::_CLASS_);
    }
}