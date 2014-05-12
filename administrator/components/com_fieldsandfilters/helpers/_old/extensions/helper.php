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
 * FieldsandfiltersExtensionsHelper
 *
 * @package     com_fieldsandfilters
 * @since       1.2.0
 */
class FieldsandfiltersExtensionsHelper
{
	/**
	 * @since       1.2.0
	 *
	 */
	public static function getParams($key, JObject $extensions, $default = null)
	{
		$value        = $default;
		$isEmptyValue = true;

		// get module param
		if (!property_exists($extensions, 'module.off'))
		{
			$valueName = 'module.value';
			if (property_exists($extensions, $valueName))
			{
				$value = $extensions->$valueName;
				if ($value !== null && $value !== '')
				{
					$isEmptyValue = false;
				}
			}
			else
			{
				if ($moduleID = (int) $extensions->get('module.id'))
				{
					if (!is_null($value = KextensionsModule::getParams($moduleID)->get($key)))
					{
						$isEmptyValue = false;
					}
				}
			}
		}

		// get plugin param
		if ($isEmptyValue && !property_exists($extensions, 'plugin.off'))
		{
			$valueName = 'plugin.value';
			if (property_exists($extensions, $valueName))
			{
				$value = $extensions->$valueName;
				if ($value !== null && $value !== '')
				{
					$isEmptyValue = false;
				}
			}
			else
			{
				if (($type = $extensions->get('plugin.type', 'fieldsandfiltersExtensions')) && ($name = $extensions->get('plugin.name')))
				{
					if (!is_null($value = KextensionsPlugin::getParams($type, $name)->get($key)))
					{
						$isEmptyValue = false;
					}
				}
			}
		}

		// get component param
		if ($isEmptyValue && !property_exists($extensions, 'component.off'))
		{
			$valueName = 'component.value';
			if (property_exists($extensions, $valueName))
			{
				$value = $extensions->$valueName;
				if ($value !== null && $value !== '')
				{
					$isEmptyValue = false;
				}
			}
			else
			{
				if ($option = $extensions->get('component.option', 'com_fieldsandfilters'))
				{
					if (!is_null($value = JComponentHelper::getParams($option)->get($key)))
					{
						$isEmptyValue = false;
					}
				}
			}
		}

		return (!$isEmptyValue ? $value : $default);
	}
}