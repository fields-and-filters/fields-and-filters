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
	 * @since       1.1.0
	 */
        protected static $instances = array();
        
	/**
	 * @since       1.1.0
	 */
        protected static $path = '/components/com_fieldsandfilters/helpers';
        
	/**
	 * @since       1.0.0
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
	 */
        public static function __callStatic( $name, $arguments = array() )
        {
                $hash = md5( $name . serialize( $arguments ) );
                if( !array_key_exists( $hash, self::$instances ) )
		{
                        if( substr( $name, 0, 3 ) == 'get' )
                        {
                                $file           = substr( $name, 3 );
                                $class          = 'Fieldsandfilters' . ( ( strpos( $file, 'Helper' ) === 0 ) ? $file : $file . 'Helper' );
                                $instance       = false;
                                
                                if( substr( $name, -4 ) == 'Site' )
                                {
                                        $path =  JPATH_SITE . self::$path;
                                }
                                else
                                {
                                        $path =  JPATH_ADMINISTRATOR . self::$path;
                                }
                                
                                if( JLoader::import( 'fieldsandfilters.' . strtolower( $file ), $path ) && class_exists( $class ) )
                                {
                                        if( method_exists( $class, 'getInstance' ) )
                                        {
                                                $instance = call_user_func_array( array( $class, 'getInstance' ), $arguments );
                                        }
                                        else
                                        {
                                                $instance = new $class;
                                        }
                                        
                                        self::$instances[$hash] = $instance;
                                }
                                else
                                {
                                        throw new InvalidArgumentException( 'Method not exists ' . $name );
                                        return false;
                                }
                        }
                        else
                        {
                                throw new InvalidArgumentException( 'Method not exists ' . $name );
                                return false;
                        }
		}
                
                return self::$instances[$hash];
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
}
