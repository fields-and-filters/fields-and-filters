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

    public function filter($class = '\\stdClass')
    {
        if (!class_exists($class))
        {
            throw new \Exception(sprintf('Class "%s" not exists', $class));
        }

        $object = new $class();

        foreach ($this AS $key => $value)
        {
            $object->$key = $value;
        }

        return $object;
    }

    public function addRule($name, $field, array $params = array(), $method = Filter::IS)
    {
        $this->rules[] = array(
            'name' => $name,
            'field' => $field,
            'params' => $params,
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