<?php
/**
 * @package     lib_kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('JPATH_PLATFORM') or die;

/**
 * KextensionsLanguage
 *
 * @since       1.0.0
 */
class KextensionsLanguage
{
	/**
	 * @since       1.0.0
	 */
	public static function load($extension, $basePath = JPATH_BASE)
	{
		$lang = JFactory::getLanguage();
		$type = strtolower(substr($extension, 0, 3));

		switch ($type)
		{
			case 'com' :
				$path = JPATH_BASE . '/components/' . $extension;
				break;
			case 'mod' :
				$path = JPATH_BASE . '/modules/' . $extension;
				break;
			case 'plg' :
				list(, $type, $name) = explode('_', $extension, 3);
				$path = JPATH_PLUGINS . '/' . $type . '/' . $name;
				break;
			case 'tpl' :
				$path = JPATH_BASE . '/templates/' . $extension;
				break;
			default :
				return;
		}

		return $lang->load($extension, $basePath, null, false, false)
		|| $lang->load($extension, $basePath, $lang->getDefault(), false, false)
		|| $lang->load($extension, $path, null, false, false)
		|| $lang->load($extension, $path, $lang->getDefault(), false, false);
	}
}