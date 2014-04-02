<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Fieldsandfilters Factory.
 *
 * @since       1.1.0
 */
class FieldsandfiltersFactory
{
	/**
	 * @var    FieldsandfiltersFields
	 * @since  1.2.0
	 */
	public static $fields = null;

	/**
	 * @var    FieldsandfiltersElements
	 * @since  1.2.0
	 */
	public static $elements = null;

	/**
	 * @var    FieldsandfiltersExtensions
	 * @since  1.2.0
	 */
	public static $extensions = null;

	/**
	 * @var    FieldsandfiltersTypes
	 * @since  1.2.0
	 */
	public static $types = null;

	/**
	 * @var    JEventDispatcher or JDispatcher
	 * @since  1.1.0
	 */
	public static $dispatcher = null;

	/**
	 * Get a fields object.
	 *
	 * @see     FieldsandfiltersFields
	 * @since   1.2.0
	 */
	public static function getFields()
	{
		if (!self::$fields)
		{
			self::$fields = FieldsandfiltersFields::getInstance();
		}

		return self::$fields;
	}

	/**
	 * Get a elements object.
	 *
	 * @see     FieldsandfiltersElements
	 * @since   1.2.0
	 */
	public static function getElements()
	{
		if (!self::$elements)
		{
			self::$elements = FieldsandfiltersElements::getInstance();
		}

		return self::$elements;
	}

	/**
	 * Get a extensions object.
	 *
	 * @see     FieldsandfiltersExtensions
	 * @since   1.2.0
	 */
	public static function getExtensions()
	{
		if (!self::$extensions)
		{
			self::$extensions = FieldsandfiltersExtensions::getInstance();
		}

		return self::$extensions;
	}

	/**
	 * Get a types object.
	 *
	 * @see     FieldsandfiltersTypes
	 * @since   1.2.0
	 */
	public static function getTypes()
	{
		if (!self::$types)
		{
			self::$types = FieldsandfiltersTypes::getInstance();
		}

		return self::$types;
	}

	/**
	 * Get version compare.
	 *
	 * @param   string $operator The possible operators are: <, lt, <=, le, >, gt, >=, ge, ==, =, eq, !=, <>, ne respectively. Default: >=
	 * @param   string $need     First version number. Default: 3.0
	 * @param   string $version  Second version number. . Default: JVERSION
	 *
	 * @since   1.1.0
	 */
	public static function isVersion($operator = '>=', $need = 3.0, $version = JVERSION)
	{
		static $versions;

		$key = ($operator . $need . $version);
		if (!isset($versions[$key]))
		{
			$versions[$key] = version_compare($version, $need, $operator);
		}

		return $versions[$key];
	}

	/**
	 * @since         1.2.0
	 * @deprecated    1.2.0
	 */
	public static function useOldStructure()
	{
		static $useOldStructure;

		if (is_null($useOldStructure))
		{
			$useOldStructure = JComponentHelper::getParams('com_fieldsandfilters')->get('use_old_structure', false);
		}

		return $useOldStructure;
	}

	/**
	 * Get dispatcher object.
	 *
	 * @see         JEventDispatcher or JDispatcher
	 * @since       1.1.0
	 * @deprecated  1.2.0
	 */
	/*
	public static function getDispatcher()
	{
		if( !self::$dispatcher )
		{
			if( self::isVersion() )
			{
				self::$dispatcher = JEventDispatcher::getInstance();
			}
			else
			{
				self::$dispatcher = JDispatcher::getInstance();
			}
		}
		
		return self::$dispatcher;
	}
	*/

	/**
	 * @since       1.0.0
	 * @deprecated  1.2.0
	 */
	/*
	public static function getPluginHelper( $type, $name, $helper = 'helper' )
	{
		$key = strtolower( $type . $name . $helper );
		if( !array_key_exists( $key, self::$instances ) )
		{
			$class = 'plg' . ucfirst( $type ) . ucfirst( $name ) . ucfirst( $helper );
			if( JLoader::import( ( $type . '.' . $name . '.' . $helper ), JPATH_PLUGINS ) && class_exists( $class ) )
			{
				if( method_exists( $class, 'getInstance' ) )
				{
					$instance = call_user_func_array( array( $class, 'getInstance' ), array() );
				}
				else
				{
					$instance = new $class;
				}
				
				self::$instances[$key] = $instance;
			}
			else
			{
				throw new InvalidArgumentException( 'Method not exists ' . $name );
				return false;
			}
		}
		return self::$instances[$key];
	}
	*/

	/**
	 * @since       1.1.0
	 * @deprecated  1.2.0
	 */
	/*
        public static function __callStatic( $name, $arguments = array() )
        {
		static $instances = array();
		$name = strtolower( $name );
		
		switch( $name )
		{
			case 'getfieldssites':
				if( !isset( $instances[$name] ) )
				{
					$instances[$name] =  new FieldsandfiltersFieldsHelper;
				}
				return $instances[$name];
			break;
			case 'getfilterssites':
				if( !isset( $instances[$name] ) )
				{
					$instances[$name] =  new FieldsandfiltersFiltersHelper;
				}
				return $instances[$name];
			break;
			case 'getpluginextensions':
				return self::getExtensions();
			break;
			case 'getplugintypes':
				return self::getTypes();
			break;
			default:
				throw new InvalidArgumentException( 'Method not exists ' . $name );
			break;
		}
        }
        */
}
