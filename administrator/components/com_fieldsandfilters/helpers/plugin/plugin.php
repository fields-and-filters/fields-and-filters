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
 * FieldsandfiltersPlugin
 *
 * @package     com_fieldsandfilters
 * @since       1.2.0
 */
abstract class FieldsandfiltersPlugin
{
	/**
	 * @since       1.2.0
	 **/
	public static function getLayout(JRegistry $params, $name, $folder = null, $path = null)
	{
		$path   = ($path) ? $path . '.' . $name : $name;
		$layout = $params->get($path, 'default');

		if (!$params->get('is.' . $name, false))
		{
			if ($folder)
			{
				$layout = (strpos($layout, ':') !== false) ? str_replace(':', ':' . $folder . '/', $layout) : $folder . '/' . $layout;
			}

			$params->set('is.' . $name, true);
			$params->set($path, $layout);
		}

		return $layout;
	}
}