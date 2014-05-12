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
 * FieldsandfiltersFields
 *
 * @package     com_fieldsandfilters
 * @since       2.0
 */
class FieldsandfiltersFields implements IteratorAggregate, Countable
{
    protected $data = array();

    public function __isset($property)
    {
        return isset($this->data[$property]);
    }

    public function __set($property, $value)
    {
        $this->set($property, $value);

        return $this->get($property);
    }

    public function __get($property)
    {
        return $this->get($property);
    }

    /*
        [TODO] in Fields the $value must be a Field
    */
    public function set($property, $value)
    {
        $this->data[$property] = $value;

        return $this;
    }

    public function get($property)
    {
        return isset($this->data[$property]) ? $this->data[$property] : null;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    public function count()
    {
        return count($this->data);
    }
}