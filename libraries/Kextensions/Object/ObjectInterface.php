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
     * @return mixed The value of the data property, or null if the data propterty does not exists.
     */
    public function get($property);

    /**
     * Binds an array or object to this object.
     *
     * @param mixed $properties An associative array of properties or an object.
     *
     * @return Object Current instance.
     */
    public function bind($properties);
}