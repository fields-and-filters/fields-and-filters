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
 * FieldsandfiltersModes
 *
 * @package     com_fieldsandfilters
 * @since       1.2.0
 */
abstract class FieldsandfiltersModes
{
	/**
	 * @since       1.2.0
	 **/
	const MODE_FILTER = 'filter';

	/**
	 * @since       1.2.0
	 **/
	const MODE_FIELD = 'field';

	/**
	 * @since       1.2.0
	 **/
	const MODE_STATIC = 'static';

	/**
	 * @since       1.2.0
	 **/
	const MODE_NAME_TYPE = 1;

	/**
	 * @since       1.2.0
	 **/
	const MODE_NAME_MODE = 2;

	/**
	 * @since       1.2.0
	 **/
	const MODE_NAME_PATH = 3;

	protected static $modes = array(
		'filter' => array(
			'single' => 1,
			'multi'  => 2
		),
		'field'  => array(
			'text' => -1,
			'json' => -2
		),
		'static' => array(
			'text' => -6,
			'json' => -7
		)
	);

	/**
	 * Get a mode type value.
	 *
	 * @param   string $path    Mode path (e.g. values.single)
	 * @param   mixed  $default Optional default value, returned if the internal value is null.
	 *
	 * @return  mixed  Value of entry or null
	 *
	 * @since       1.0.0
	 */
	public static function getMode($path = null, $default = null)
	{
		if (is_null($path))
		{
			return new JObject(self::$modes);
		}
		elseif (strpos($path, '.'))
		{
			// Explode the mode path into an array
			list($type, $name) = explode('.', $path, 2);

			return isset(self::$modes[$type][$name]) ? self::$modes[$type][$name] : $default;
		}

		return isset(self::$modes[$path]) ? self::$modes[$path] : $default;
	}

	/**
	 * Get a mode type values.
	 *
	 * @param   string  $paths    Array mode paths (e.g. array(values.single, values.multi)
	 * @param   mixed   $default  Optional default value, returned if the internal value is null.
	 * @param   boolean $pathKey  Keys of array is the name of modes
	 * @param   boolean $flatten  Flatten array
	 * @param   array   $excluded Excluded items array
	 *
	 * @return  mixed  Value of entry or null
	 *
	 * @since       1.1.0
	 */
	public static function getModes($paths = null, $default = array(), $flatten = false, $excluded = false, $pathKey = false)
	{
		$modes      = array();
		$isExcluded = ($excluded && is_array($excluded));

		if (is_null($paths))
		{
			$modes = self::$modes;
		}
		else
		{
			if (is_array($paths))
			{
				while ($path = array_shift($paths))
				{
					if ($mode = self::getMode($path, false))
					{
						if ($pathKey)
						{
							$modes[$path] = $mode;
						}
						else
						{
							$modes[] = $mode;
						}
					}
				}
			}
			else
			{
				if (is_string($paths))
				{
					$modes = (array) self::getMode($paths);
				}
			}
		}

		if (!empty($modes))
		{
			if ($flatten || $isExcluded)
			{
				$modes = KextensionsArray::flatten($modes);
			}

			if ($isExcluded)
			{
				$modes = array_diff($modes, $excluded);
			}
		}
		else
		{
			$modes = $default;
		}

		return $modes;
	}

	/**
	 * @since       1.0.0
	 **/
	public static function getModeName($id, $name = FieldsandfiltersModes::MODE_NAME_TYPE, $default = null)
	{
		if ($id = (int) $id)
		{
			foreach (self::$modes AS $typeName => &$mode)
			{
				if ($modeName = array_search($id, $mode))
				{
					switch ($name)
					{
						case self::MODE_NAME_TYPE:
							return $typeName;
							break;
						case self::MODE_NAME_MODE:
							return $modeName;
							break;
						case self::MODE_NAME_PATH:
							return ($typeName . '.' . $modeName);
							break;
					}

					break;
				}
			}
		}

		return $default;
	}
}