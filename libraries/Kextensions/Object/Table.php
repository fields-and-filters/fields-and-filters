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
class Table extends Object implements \IteratorAggregate, \Countable
{
    public function set($property, $value)
    {
        if (!$value instanceof Object)
        {
            throw new \InvalidArgumentException(sprintf('%s(%s)', __METHOD__, gettype($properties)));
        }

        parent::set($property, $value);
    }

    public function getIterator() {
        return new \ArrayIterator($this);
    }

    public function count()
    {
        return count($this->data);
    }
}