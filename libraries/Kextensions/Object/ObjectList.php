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
 * Object List
 *
 * @package     Kextensions
 * @since       2.0
 */
class ObjectList extends Object
{
    /**
     * {@inheritdoc}
     */
    const _CLASS_ = __CLASS__;

    /**
     * @var string Instance name of class which is required for set.
     */
    protected static $setInstance = Object::_CLASS_;

    /**
     * Set a data propterty.
     *
     * @param string $property The name of the data property.
     * @param Object $value The object with instance of Object.
     *
     * @return ObjectList Current instance.
     */
    public function set($property, $value)
    {
        if (!$value instanceof self::$setInstance)
        {
            throw new \InvalidArgumentException(sprintf('Value "%s" is not instance of "%s".', (is_object($value) ? get_class($value) : gettype($value)), self::$setInstance));
        }

        return parent::set($property, $value);
    }
}