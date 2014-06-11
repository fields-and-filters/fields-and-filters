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
 * Abstract Rule
 *
 * @package     Kextensions
 * @since       2.0
 */
abstract class AbstractRule implements RuleInterface
{
    /**
     * The full set of data to be filtered.
     *
     * @var object
     */
    protected $data;

    /**
     * The field to be filtered within the data.
     *
     * @var string
     */
    protected $field;

    /**
     * {@inheritdoc}
     */
    public function prepare($data, $field)
    {
        $this->data = $data;
        $this->field = $field;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        $field = $this->field;
        return isset($this->data->$field) ? $this->data->$field : null;
    }

    /**
     * {@inheritdoc}
     */
    public function is()
    {
        return call_user_func_array(array($this, 'validate'), func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function isNot()
    {
        return !call_user_func_array(array($this, 'validate'), func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function condition()
    {
        return call_user_func_array(array($this, 'formula'), func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function conditionNot()
    {
        return call_user_func_array(array($this, 'formulaNot'), func_get_args());
    }
}