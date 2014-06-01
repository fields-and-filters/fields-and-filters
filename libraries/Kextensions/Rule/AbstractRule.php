<?php

/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Rule;

defined('_JEXEC') or die;

/**
 * AbstractRule
 *
 * @package     Kextensions
 * @since       2.0
 */
abstract class AbstractRule implements RuleInterface
{
    protected $data;

    protected $field;

    public function prepare($data, $field)
    {
        $this->data = $data;
        $this->field = $field;

        return $this;
    }

    public function getValue()
    {
        $field = $this->field;
        return isset($this->data->$field) ? $this->data->$field : null;
    }

    public function is()
    {
        return call_user_func_array(array($this, 'validate'), func_get_args());
    }

    public function isNot()
    {
        return !call_user_func_array(array($this, 'validate'), func_get_args());
    }
}