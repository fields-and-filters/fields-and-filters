<?php
/**
 * @version     1.1.1
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Fieldsandfilters Factory.
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
	 * Get a fields object.
	 *
	 * @see     FieldsandfiltersFields
	 * @since   1.2.0
	 */
	public static function getFields()
	{
		if( !self::$fields )
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
		if( !self::$elements )
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
		if( !self::$extensions )
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
		if( !self::$types )
		{
			self::$types = FieldsandfiltersTypes::getInstance();
		}
		
		return self::$types;
	}
	
	/**
	 * @since       1.1.0
	 */
        public static function isVersion( $operator = '>=', $need = 3.0, $version = JVERSION )
        {
                static $versions;
		
                $key = ( $operator . $need . $version );
                if( !isset( $versions[$key] ) )
                {
                        $versions[$key] = version_compare( $version, $need, $operator );
                }
		
                return $versions[$key];
        }
	
	/**
        * @since       1.1.0
        */
	public static function getDispatcher()
	{
		static $_dispatcher;
		
		if( is_null( $_dispatcher ) )
		{
			if( self::isVersion() )
			{
				$_dispatcher = JEventDispatcher::getInstance();
			}
			else
			{
				$_dispatcher = JDispatcher::getInstance();
			}
		}
		
		return $_dispatcher;
	}
	
	
	/**
	 * @since       1.0.0
	 * @deprecated  1.2.0
	 */
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
	
	/**
	 * @since       1.1.0
	 * @deprecated  1.2.0
	 */
        public static function __callStatic( $name, $arguments = array() )
        {
		switch( $name )
		{
			case 'getPluginExtensions':
				return self::getExtensions();
			break;
			case 'getPluginTypes':
				return self::getTypes();
			break;
			default:
				throw new InvalidArgumentException( 'Method not exists ' . $name );
			break;
		}
        }
}