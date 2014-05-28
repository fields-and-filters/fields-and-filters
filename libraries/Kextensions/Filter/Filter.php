<?php

/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Filter;

defined('_JEXEC') or die;

/**
 * Filter
 *
 * @package     Kextensions
 * @since       2.0
 */
class Filter extends \FilterIterator
{
    const IS = 'is';

    const IS_NOT = 'isNot';

    protected $rules = array();

    public function accept()
    {
        $data = $this->current();

        foreach ($this->rules AS $info)
        {
            $rule = RuleLocator::get($info['name']);
            $rule->prepare($data, $info['field']);

            if (!call_user_func_array(array($rule, $info['method']), $info['params']))
            {
                return false;
            }
        }

        return true;
    }

    public function filter()
    {
        return iterator_to_array($this);
    }

    public function addRule($name, $field, $method = Filter::IS)
    {
        $this->rules[] = array(
            'name' => $name,
            'field' => $field,
            'params' => array_slice(func_get_args(), 3),
            'method' => $method
        );

        return $this;
    }

    public function clearRule()
    {
        $this->rules = array();

        return $this;
    }
}