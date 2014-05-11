<?php
/**
 * @package     lib_kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('JPATH_PLATFORM') or die;

/**
 * KextensionsModule
 *
 * @since       1.0.0
 */
class KextensionsModule extends JModuleHelper
{
	/**
	 * @since       1.0.0
	 */
	public static function &getModuleByID($id)
	{
		$result = false;
		if ($id = (int) $id)
		{
			$result  = null;
			$modules =& self::_load();
			$total   = count($modules);

			for ($i = 0; $i < $total; $i++)
			{
				// Match the name of the module
				if ($modules[$i]->id == $id)
				{
					// Found it
					$result = & $modules[$i];
					break;
				}
			}
		}

		return $result;
	}

	/**
	 * @since       1.0.0
	 */
	public static function getParams($id)
	{
		static $params;

		if (!isset($params[$id]))
		{
			$registry = new JRegistry();
			if ($module = self::getModuleByID($id))
			{
				$registry->loadString($module->params);
			}

			$params[$id] = $registry;
		}

		return $params[$id];
	}
}