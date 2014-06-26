<?php
/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Object;

use Closure, InvalidArgumentException;

defined('_JEXEC') or die;

/**
 * Collection
 *
 * @package     Kextensions
 * @since       2.0
 */
class Collection extends Object implements CollectionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param ObjectInterface $value The object with instance of Object.
     *
     * @return Collection Current instance.
     *
     * @throws InvalidArgumentException
     */
    public function set($property, $value)
    {
        if (!$value instanceof ObjectInterface)
        {
            throw new InvalidArgumentException(sprintf('Value "%s" is not instance of "%s".', (is_object($value) ? get_class($value) : gettype($value)), __NAMESPACE__ . '\\ObjectInterface'));
        }

        return parent::set($property, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function first()
    {
        return reset($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function last()
    {
        return end($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function map(Closure $callback)
    {
        return new Object(array_map($callback, $this->data));
    }

    /**
     * {@inheritDoc}
     */
    public function filter(Closure $callback)
    {
        return new static(array_filter($this->data, $callback));
    }
}