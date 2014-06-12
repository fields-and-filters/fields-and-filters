<?php
/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Object;

defined('_JEXEC') or die;

/**
 * Object
 *
 * @package     Kextensions
 * @since       2.0
 */
class Object implements \IteratorAggregate, \Countable
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
     * @throws \InvalidArgumentException
     */
    public function __construct($properties = null)
    {
        if ($properties !== null)
        {
            $this->bind($properties);
        }
    }

    /**
     * The magic isset method is used to check the state of an object property.
     *
     * @param string $property The name of the data property.
     *
     * @return bool True if set, otherwise false is returned.
     */
    public function __isset($property)
    {
        return array_key_exists($property, $this->data);
    }

    /**
     * The magic set method is used to set a data property.
     *
     * @param $property The name of the data property.
     * @param $value The value to give the data property.
     *
     * @return void
     */
    public function __set($property, $value)
    {
        $this->set($property, $value);
    }

    /**
     * The magic get method is used to get a data property.
     *
     * @param $property The name of the data property.
     * @return mixed The value of the data property, or null if the data propterty does not exists.
     */
    public function __get($property)
    {
        return $this->get($property);
    }

    /**
     * The magic unset method is used to unset a data property.
     *
     * @param $property The name of the data property.
     *
     * @return void
     */
    public function __unset($property)
    {
        unset($this->data[$property]);
    }

    /**
     * Gets this object represented as an ArrayIterator.
     *
     * @return \ArrayIterator This object represented as an ArrayIterator.
     */
    public function getIterator()
    {
        return new \ArrayIterator($this);
    }

    /**
     * Count elements of an object.
     *
     * @return int The number of data properties.
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Set a data propterty.
     *
     * @param $property The name of the data property.
     * @param $value The value to give the data property.
     *
     * @return Object Current instance.
     */
    public function set($property, $value)
    {
        $this->data[$property] = $value;

        return $this;
    }

    /**
     * Get a data property
     *
     * @param $property The name of the data property.
     *
     * @return mixed The value of the data property, or null if the data propterty does not exists.
     */
    public function get($property)
    {
        return isset($this->data[$property]) ? $this->data[$property] : null;
    }

    /**
     * Binds an array or object to this object.
     *
     * @param $properties An associative array of properties or an object.
     *
     * @return Object Current instance.
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
     * @param $properties An associative array of properties or an object.
     *
     * @return array Prepared properties
     *
     * @throws \InvalidArgumentException
     */
    protected static function beforeBind($properties)
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

        return $properties;
    }
}