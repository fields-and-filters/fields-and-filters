<?php
/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Object;

use IteratorAggregate, Countable;

defined('_JEXEC') or die;

/**
 * Object Instance
 *
 * @package     Kextensions
 * @since       2.0
 */
interface ObjectInterface extends IteratorAggregate, Countable
{
    /**
     * The magic isset method is used to check the state of an object property.
     *
     * @param string $property The name of the data property.
     *
     * @return bool True if set, otherwise false is returned.
     */
    public function __isset($property);

    /**
     * The magic set method is used to set a data property.
     *
     * @param string $property The name of the data property.
     * @param mixed $value The value to give the data property.
     *
     * @return void
     */
    public function __set($property, $value);

    /**
     * The magic get method is used to get a data property.
     *
     * @param string $property The name of the data property.
     *
     * @return mixed The value of the data property, or null if the data propterty does not exists.
     */
    public function __get($property);

    /**
     * The magic unset method is used to unset a data property.
     *
     * @param string $property The name of the data property.
     *
     * @return void
     */
    public function __unset($property);

    /**
     * Set a data propterty.
     *
     * @param string $property The name of the data property.
     * @param mixed $value The value to give the data property.
     *
     * @return Object Current instance.
     */
    public function set($property, $value);

    /**
     * Get a data property
     *
     * @param string $property The name of the data property.
     *
     * @return mixed The value of the data property, or null if the data property does not exists.
     */
    public function get($property);

    /**
     * Gets all keys/indices of the data.
     *
     * @return array The keys/indices of the data, in the order of the corresponding elements in the data.
     */
    function getKeys();

    /**
     * Gets all values of the collection.
     *
     * @return array The values of all elements in the data, in the order they appear in the data.
     */
    public function getValues();

    /**
     * Gets a native PHP array representation of the data.
     *
     * @return array
     */
    public function toArray();

    /**
     * Clears the collection, removing all elements.
     *
     * @return Object Current instance.
     */
    public function clear();

    /**
     * Checks whether the data is empty (contains no elements).
     *
     * @return boolean TRUE if the data is empty, FALSE otherwise.
     */
    public function isEmpty();

    /**
     * Binds an array or object to this object.
     *
     * @param mixed $properties An associative array of properties or an object.
     *
     * @return Object Current instance.
     */
    public function bind($properties);
}