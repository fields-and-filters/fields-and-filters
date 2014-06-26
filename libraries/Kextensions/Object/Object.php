<?php
/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Object;

use Traversable, ArrayIterator, InvalidArgumentException;

defined('_JEXEC') or die;

/**
 * Object
 *
 * @package     Kextensions
 * @since       2.0
 */
class Object implements ObjectInterface
{
    /**
     * The data.
     *
     * @var array
     */
    protected $data = array();

    /**
     * The class constructor.
     *
     * @param mixed $properties Either an associative array or another object by which to set the initial data of the new object.
     *
     * @throws InvalidArgumentException
     */
    public function __construct($properties = null)
    {
        if ($properties !== null)
        {
            $this->bind($properties);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function __isset($property)
    {
        return array_key_exists($property, $this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function __set($property, $value)
    {
        $this->set($property, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function __get($property)
    {
        return $this->get($property);
    }

    /**
     * {@inheritDoc}
     */
    public function __unset($property)
    {
        unset($this->data[$property]);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function set($property, $value)
    {
        $this->data[$property] = $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function get($property)
    {
        return isset($this->data[$property]) ? $this->data[$property] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getKeys()
    {
        return array_keys($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function getValues()
    {
        return array_values($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        $this->data = array();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty()
    {
        return !$this->data;
    }

    /**
     * {@inheritDoc}
     */
    public function bind($properties)
    {
        $properties = self::beforeBind($properties);

        foreach($properties AS $property => $value)
        {
            $this->set($property, $value);
        }

        return $this;
    }

    /**
     * Prepare properties before addition into the data.
     *
     * @param mixed $properties An associative array of properties or an object.
     *
     * @return array Prepared properties
     *
     * @throws \InvalidArgumentException
     */
    protected static function beforeBind($properties)
    {
        if (!is_array($properties) && !is_object($properties))
        {
            throw new InvalidArgumentException(sprintf('Type "%s" not supported. Use type of array or object.', gettype($properties)));
        }

        if ($properties instanceof Traversable)
        {
            $properties = iterator_to_array($properties);
        }
        elseif (is_object($properties))
        {
            $properties = (array) $properties;
        }

        return $properties;
    }
}