<?php
/**
 * @version     1.0.1
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Fieldsandfilters Factory.
 * since       1.1.0
 */
class FieldsandfiltersFactory
{
        protected static $instances = array();
        
        protected static $path = '/components/com_fieldsandfilters/helpers';
        
        /**
	 * @since       1.1.0
	 */
        public static function __callStatic( $name, $arguments = array() )
        {
                $hash = md5( $name . serialize( $arguments ) );
                if( !array_key_exists( $hash, self::$instances ) && substr( $name, 0, 3 ) == 'get' )
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
                        }
                        else
                        {
                                throw new InvalidArgumentException( 'Method not exists ' . $name );
                        }
                        
			self::$instances[$hash] = $instance;
		}
                
                return self::$instances[$hash];
        }
}
