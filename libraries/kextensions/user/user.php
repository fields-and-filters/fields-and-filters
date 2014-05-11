<?php
/**
 * @package     lib_kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('JPATH_PLATFORM') or die;

/**
 * KextensionsUser
 *
 * @since       1.0.0
 */
class KextensionsUser
{
	/**
	 * @since       1.0.0
	 */
	public static function getIP()
	{
		static $ip;

		if (is_null($ip))
		{
			// Check for proxies as well.
			$ip = false;
			if (isset($_SERVER['HTTP_CLIENT_IP']))
			{
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			}
			else
			{
				if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
				{
					$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
				}
				else
				{
					if (isset($_SERVER['HTTP_X_FORWARDED']))
					{
						$ip = $_SERVER['HTTP_X_FORWARDED'];
					}
					else
					{
						if (isset($_SERVER['HTTP_FORWARDED_FOR']))
						{
							$ip = $_SERVER['HTTP_FORWARDED_FOR'];
						}
						else
						{
							if (isset($_SERVER['HTTP_FORWARDED']))
							{
								$ip = $_SERVER['HTTP_FORWARDED'];
							}
							else
							{
								if (isset($_SERVER['REMOTE_ADDR']))
								{
									$ip = $_SERVER['REMOTE_ADDR'];
								}
							}
						}
					}
				}
			}
		}

		return $ip;
	}
}