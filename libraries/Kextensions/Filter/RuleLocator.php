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

    public static function get($name)
    {
        $name = (strpos($name, '.') === false) ? 'rule.'.$name : $name;
        $name = strtolower($name);

        if (!isset(self::$registry[$name]))
        {
            list($namespace, $class) = explode('.', $name);

            $namespace = self::getNamespace($namespace);
            $class = $namespace.'\\'.ucfirst($name);

            if (!class_exists($class))
            {
                throw new \Exception(sprintf('Class "%s" not exists', $class));
            }
            else if (!$class instanceof AbstractRule)
            {
                throw new \InvalidArgumentException(sprintf('%s(%s)', __METHOD__, $class));
            }

            self::$registry[$name] = new $class();
        }

        return self::$registry[$name];
    }
}