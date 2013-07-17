<?php
/**
 * @version     1.0.0
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access
defined( '_JEXEC' ) or die;

/**
 * Fieldsandfilters User.
 * @since       1.1.0
 */
class FieldsandfiltersUserHelper
{
        /**
        * @since       1.1.0
        */
        public static function getUserIP()
	{
		static $ip;
		
		if( is_null( $ip ) )
		{
			// Check for proxies as well.
			$ip = false;
			if( isset( $_SERVER['HTTP_CLIENT_IP'] ) )
			{
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			}
			else if( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
			{
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
			else if( isset( $_SERVER['HTTP_X_FORWARDED'] ) )
			{
				$ip = $_SERVER['HTTP_X_FORWARDED'];
			}
			else if( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) )
			{
				$ip = $_SERVER['HTTP_FORWARDED_FOR'];
			}
			else if( isset( $_SERVER['HTTP_FORWARDED'] ) )
			{
				$ip = $_SERVER['HTTP_FORWARDED'];
			}
			else if( isset( $_SERVER['REMOTE_ADDR'] ) )
			{
				$ip = $_SERVER['REMOTE_ADDR'];
			}
		}
		
		return $ip;
	}
}