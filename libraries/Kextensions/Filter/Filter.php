<?php

/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Filter;

use Kextensions\Rule\ManagerInterface;
use Kextensions\Rule\Locator;

defined('_JEXEC') or die;

/**
 * Filter
 *
 * @package     Kextensions
 * @since       2.0
 */
class Filter extends \FilterIterator implements FilterInterface, ManagerInterface
{
    /**
     * The rules to be applied to a data object.
     *
     * @var array
     */
    protected $rules = array();

    /**
     * Check whether the current element of the iterator is acceptable.
     *
     * @return bool true if the current element is acceptable, otherwise false.
     */
    public function accept()
    {
        $data = $this->current();

        foreach ($this->rules AS $info)
        {
            $rule = Locator::get($info['name']);
            $rule->prepare($data, $info['field']);

            if (!call_user_func_array(array($rule, $info['method']), $info['params']))
            {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function filter()
    {
        return iterator_to_array($this);
    }

    /**
     * {@inheritdoc}
     *
     * @return Filter Current instance.
     */
    public function addRule($name, $field, $method = Filter::IS)
    {
        $this->rules[] = array(
            'name' => $name,
            'field' => $field,
            'method' => $method,
            'params' => array_slice(func_get_args(), 3)
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return Filter Current instance.
     */
    public function clearRule()
    {
        $this->rules = array();

        return $this;
    }
}