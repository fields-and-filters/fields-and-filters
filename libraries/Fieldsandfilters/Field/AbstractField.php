<?php
/**
* @package     Fieldsandfilters
* @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
* @license     GNU General Public License version 3 or later; see License.txt
* @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
*/

namespace Fieldsandfilters\Field;

use Kextensions\Object\Object;

defined('_JEXEC') or die;

/**
 * Abstract Field
 *
 * @package     Fieldsandfilters
 * @since       2.0
 */
abstract class AbstractField extends Object
{
    /**
     * {@inheritdoc}
     */
    const _CLASS_ = __CLASS__;

    public function render()
    {
        return '';
    }

    function __toString()
    {
        return $this->render();
    }
}