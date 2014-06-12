<?php
/**
* @package     Fieldsandfilters
* @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
* @license     GNU General Public License version 3 or later; see License.txt
* @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
*/

namespace Fieldandfilters\Field;

use Kextensions\Object\ObjectList;

defined('_JEXEC') or die;

/**
 * Field List
 *
 * @package     Fieldsandfilters
 * @since       2.0
 *
 * [TODO] Extends Object for create list class (ObjectList)
 */
class FieldList extends ObjectList
{
    public function set($property, AbstractField $value)
    {
        return $this->set($property, $value);
    }
}