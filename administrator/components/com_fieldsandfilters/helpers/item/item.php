<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access
defined('_JEXEC') or die;

/**
 * FieldsandfiltersItem
 *
 * @package     com_fieldsandfilters
 * @since       2.0
 */
class FieldsandfiltersItem
{
    public function render($field)
    {
        /**
         * tutaj mogą być zarówno:
         * - lista id
         * - integer id
         * - FieldandfiltersFields
         * - FieldsandfiltersField
         **/


        if (is_int($field))
        {
            $field = FieldsandfiltersFields();
        }
        elseif (!$field instanceof FieldsandfiltersField)
        {
            throw new InvalidArgumentException(sprintf('%s(%s)', __METHOD__, gettype($properties)));
        }

        return $field->render($this);
    }
}