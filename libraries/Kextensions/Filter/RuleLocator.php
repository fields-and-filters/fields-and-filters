<?php

/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Filter;

use Kextensions\Filter\RuleInterface;

defined('_JEXEC') or die;

/**
 * Filter
 *
 * @package     Kextensions
 * @since       2.0
 */
abstract class RuleLocator
{
    protected static $namespaces = array(
        'rule' => 'Kextensions\\Filter\\Rule'
    );

    protected static $registry = array();

    public static function get($name)
    {
        $name = (strpos($name, '.') === false) ? 'rule.'.$name : $name;
        $key = strtolower($name);

        if (!isset(self::$registry[$key]))
        {
            self::$registry[$key] = self::getClass($name);
        }

        return self::$registry[$key];
    }

    public static function setNamespace($name, $namespace)
    {
        self::$namespaces[$name] = $namespace;
    }

    public static function getNamespace($name)
    {
        if (!isset(self::$namespaces[$name]))
        {
            throw new \InvalidArgumentException(sprintf('Namspace "%s" not exists', $name));
        }

        return self::$namespaces[$name];
    }

    protected static function getClass($name)
    {
        list($namespace, $class) = explode('.', $name);

        $namespace = self::getNamespace($namespace);
        $class = $namespace.'\\'.ucfirst($class);

        if (!class_exists($class))
        {
            throw new \Exception(sprintf('Class "%s" not exists', $class));
        }

        $class = new $class();

        if (!$class instanceof RuleInterface)
        {
            throw new \InvalidArgumentException(sprintf('Class "%s" not instance of "%s"', get_class($class), 'Kextensions\\Filter\\RuleInterface'));
        }
        else if(!is_callable(array($class, 'validate')))
        {
            throw new \InvalidArgumentException(sprintf('The method "%s::%s" is not callable', get_class($class), 'validate'));
        }

        return $class;
    }
}