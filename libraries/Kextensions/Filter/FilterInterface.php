<?php

/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Filter;

defined('_JEXEC') or die;

/**
 * FilterInterface
 *
 * @package     Kextensions
 * @since       2.0
 */
interface FilterInterface
{
    /**
     * Apply a rule to check if the value is valid.
     */
    const IS = 'is';

    /**
     * Apply a rule to check if the value **is not** valid.
     */
    const IS_NOT = 'isNot';

    /**
     * Return the elements of an iterator into an array.
     *
     * @return array An array containing the elements of the iterator.
     */
    public function filter();
}