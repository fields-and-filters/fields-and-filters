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
     *
     * @param string $class Class name.
     */
    public function map(Closure $callback, $class = null)
    {
        $array = array_map($callback, $this->data);

        return ($class ? new $class($array) : new static($array));
    }

    /**
     * {@inheritDoc}
     *
     * @param bool $newInstance Create new instance of the class or set filtered value into data property.
     */
    public function filter(Closure $callback, $newInstance = true)
    {
        $array = array_filter($this->data, $callback);

        if ($newInstance)
        {
            return new static($array);
        }

        $this->data = $array;

        return $this;
    }
}