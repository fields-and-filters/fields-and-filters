<?php

/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Rule;

defined('_JEXEC') or die;

/**
 * Locator
 *
 * @package     Kextensions
 * @since       2.0
 */
abstract class Locator
{
    /**
     * List of namespaces handled by the rules.
     *
     * @var array
     */
    protected static $namespaces = array(
        '_default_' => 'Kextensions\\Rule\\Rule'
    );

    /**
     * A registry to retain rule objects.
     *
     * @var array
     */
    protected static $registry = array();

    /**
     * Gets a rule from the registry by name.
     *
     * @param string $name The rule to retrieve. Look like namspace.class (Default: _defult_.class).
     *
     * @return RuleInterface A rule object.
     */
    public static function get($name)
    {
        $name = (strpos($name, '.') === false) ? '_default_.'.$name : $name;
        $key = strtolower($name);

        if (!isset(self::$registry[$key]))
        {
            self::$registry[$key] = self::getClass($name);
        }

        return self::$registry[$key];
    }

    /**
     * Set namspace path.
     *
     * @param $name The name of namspace. Use this later to get namspace path.
     * @param $namespace The namspace path.
     *
     * @return void
     */
    public static function setNamespace($name, $namespace)
    {
        self::$namespaces[$name] = $namespace;
    }

    /**
     * Get namspace path.
     *
     * @param $name The name of namspace.
     *
     * @return string Namspace path.
     *
     * @throws \InvalidArgumentException Namspace not exists.
     */
    public static function getNamespace($name)
    {
        if (!isset(self::$namespaces[$name]))
        {
            throw new \InvalidArgumentException(sprintf('Namspace "%s" not exists', $name));
        }

        return self::$namespaces[$name];
    }

    /**
     * Get class instance.
     *
     * @param string $name The rule to retrieve.
     *
     * @return RuleInterface A rule object.
     *
     * @throws \Exception Class not exists.
     * @throws \InvalidArgumentException Class not instance of RuleInterface.
     */
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

        return $class;
    }
}