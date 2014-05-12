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
 * FieldsandfiltersField
 *
 * @package     com_fieldsandfilters
 * @since       2.0
 */
class FieldsandfiltersField 
{
    public function render($item)
    {
        if (is_int($item))
        {
            $item = FieldsandfiltersItem();
        }
        elseif (!$item instanceof FieldsandfiltersItem)
        {
            throw new InvalidArgumentException(sprintf('%s(%s)', __METHOD__, gettype($properties)));
        }

        return $this->renderField($item);
    }

    protected function renderField(FieldsandfiltersItem $item)
    {
        // [TODO] for field
    }
}