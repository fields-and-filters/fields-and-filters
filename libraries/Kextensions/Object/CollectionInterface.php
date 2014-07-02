<?php
/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Object;

use Closure;

defined('_JEXEC') or die;

/**
 * Collection Interface
 *
 * @package     Kextensions
 * @since       2.0
 */
interface CollectionInterface extends ObjectInterface
{
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
     * Sets the internal iterator to the first element in the collection and returns this element.
     *
     * @return mixed
     */
    public function first();

    /**
     * Sets the internal iterator to the last element in the collection and returns this element.
     *
     * @return mixed
     */
    public function last();

    /**
     * Applies the given function to each element in the collection and returns a new collection with the elements returned by the function.
     *
     * @param Closure $callback
     * @param string $class Class name. Instance have after mapped.
     *
     * @return self Current instance
     */
    public function map(Closure $callback, $class = null);

    /**
     * Returns all the elements of this collection that satisfy the predicate callback.
     * The order of the elements is preserved.
     *
     * @param Closure $callback The predicate used for filtering.
     * @param bool $newInstance Create new instance of the class or set filtered value into data property.
     *
     * @return Collection A collection with the results of the filter operation.
     */
    public function filter(Closure $callback, $newInstance = true);

//    public function find();
}