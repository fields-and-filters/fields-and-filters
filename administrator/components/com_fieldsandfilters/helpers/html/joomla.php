<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package           fieldsandfilters.administrator
 * @subpackage        com_fieldsandfilters
 *
 * @since             1.2.0
 */
abstract class FieldsandfiltersHtmlJoomla
{
	/**
	 * @var    array  Array containing information for loaded files
	 * @since  1.2.0
	 */
	protected static $loaded = array();

	/**
	 * Internal method to get a JavaScript object notation string from an array
	 *
	 * @param   array $array The array to convert to JavaScript object notation
	 *
	 * @return  string  JavaScript object notation representation of the array
	 *
	 * @since   1.1.0
	 */
	public static function getJSObject(array $array = array())
	{
		$elements = array();

		foreach ($array as $k => $v)
		{
			// Don't encode either of these types
			if (is_null($v) || is_resource($v))
			{
				continue;
			}

			// Safely encode as a Javascript string
			$key = json_encode((string) $k);

			if (is_bool($v))
			{
				$elements[] = $key . ': ' . ($v ? 'true' : 'false');
			}
			elseif (is_numeric($v))
			{
				$elements[] = $key . ': ' . ($v + 0);
			}
			elseif (is_string($v))
			{
				if (strpos($v, '\\') === 0)
				{
					// Items such as functions and JSON objects are prefixed with \, strip the prefix and don't encode them
					$elements[] = $key . ': ' . substr($v, 1);
				}
				else
				{
					// The safest way to insert a string
					$elements[] = $key . ': ' . json_encode((string) $v);
				}
			}
			else
			{
				$elements[] = $key . ': ' . static::getJSObject(is_object($v) ? get_object_vars($v) : $v);
			}
		}

		return '{' . implode(',', $elements) . '}';
	}

	/**
	 * Method to load the jQuery JavaScript framework into the document head
	 *
	 * If debugging mode is on an uncompressed version of jQuery is included for easier debugging.
	 *
	 * @param   boolean $noConflict True to load jQuery in noConflict mode [optional]
	 * @param   mixed   $debug      Is debugging mode on? [optional]
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	public static function jquery($noConflict = true)
	{
		// Only load once
		if (!empty(static::$loaded[__METHOD__]))
		{
			return;
		}

		JHtml::_('script', 'fieldsandfilters/core/jquery-1.10.2.min.js', false, true, false, false, false);

		// Check if we are loading in noConflict
		if ($noConflict)
		{
			JHtml::_('script', 'fieldsandfilters/core/jquery-noconflict.js', false, true, false, false, false);
		}

		static::$loaded[__METHOD__] = true;

		return;
	}
}
