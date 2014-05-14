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

    public function __construct($properties = null)
    {
        if ($properties !== null)
        {
            $this->bind($properties);
        }
    }

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

    public function bind($properties)
    {
        if (!is_array($properties) && !is_object($properties))
        {
            throw new \InvalidArgumentException(sprintf('%s(%s)', __METHOD__, gettype($properties)));
        }

        if ($properties instanceof \Traversable)
        {
            $properties = iterator_to_array($properties);
        }
        elseif (is_object($properties))
        {
            $properties = (array) $properties;
        }

        $this->data = $this->data + $properties;

        return $this;
    }
} 