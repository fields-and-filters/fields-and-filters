<?php
/**
 * @version     1.1.1
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */
defined( 'JPATH_PLATFORM' ) or die;

/**
 * FieldsandfiltersMoudleHelper
 *
 * @package     com_fieldsandfilters
 * @since       1.1.0
 */
class FieldsandfiltersModuleHelper extends JModuleHelper
{
        /**
	 * @since       1.1.0
	 */
	public static function &getModuleByID( $id )
	{
                $result = false;
                if( $id = (int) $id )
                {
                        $result = null;
                        $modules =& self::_load();
                        $total = count( $modules );
        
                        for( $i = 0; $i < $total; $i++ )
                        {
                                // Match the name of the module
                                if ($modules[$i]->id == $id )
                                {
                                        // Found it
                                        $result = &$modules[$i];
                                        break;
                                }
                        }
                }
                
		return $result;
	}
        
        /**
	 * @since       1.1.0
	 */
        public static function getModuleParams( $id )
        {
                static $_params;
                
                if( !isset( $_params[$id] ) )
                {
                        $params = new JRegistry();
                        if( $module = self::getModuleByID( $id ) )
                        {
                                $params->loadString($module->params);
                        }
                        
                        $_params[$id] = $params;
                }
                
                return $_params[$id];
        }
}