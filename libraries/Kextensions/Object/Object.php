<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Object;

/**
 * Object
 *
 * @package     Kextensions
 * @since       2.0
 */
class Object
{
    private $data = array();

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

    public function __unset($property)
    {
        unset($this->data[$property]);
    }

    public function set($property, $value)
    {
        $this->data[$property] = $value;

        return $this;
    }

    public function get($property)
    {
        return isset($this->data[$property]) ? $this->data[$property] : null;
    }
} 