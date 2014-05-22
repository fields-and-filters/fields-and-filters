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

    public function __construct(\Iterator $iterator , $filter)
    {
        parent::__construct($iterator);
        $this->userFilter = $filter;
    }

    public function accept()
    {
        $data = $this->getInnerIterator()->current();

        foreach ($this->rules AS $info)
        {
            $rule = RuleLocator::get($info['name']);
            $rule->prepare($data, $info['field']);

            if (!call_user_func_array(array($info['name'], $info['method']), $info['params']))
            {
                return false;
            }
        }

        return true;
    }

    public function addRule($name, $field, array $params = array(), $method = Filter::IS)
    {
        $this->rules[] = array(
            'name' => $name,
            'field' => $field,
            'params' => $params,
            'method' => $method
        );
    }

    public function cleanRule()
    {
        $this->rules = array();
    }
}