<?php
/**
 * @package     lib_kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.path');

/**
 * KextensionsPlugin
 *
 * @since       1.0.0
 */
class KextensionsPlugin
{
	/**
	 * @since       1.0.0
	 */
	protected static $paths = array();

	/**
	 * @since       1.1.0
	 */
	public static function renderLayout($plugin, $layout = 'default')
	{
		if (!isset($plugin->type) || !isset($plugin->name))
		{
			return null;
		}

		// Start capturing output into a buffer
		ob_start();

		// Include the requested template filename in the local scope
		// (this will execute the view logic).
		include self::getLayoutPath($plugin->type, $plugin->name, $layout);

		// Done with the requested template; get the buffer and
		// clear it.
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	/**
	 * @since       1.1.0
	 */
	public static function getLayoutPath($type, $name, $layout = 'default')
	{
		// Create the plugin name
		$extension = 'plg_' . $type . '_' . $name;

		if (!($path = self::getPath($extension, $layout)))
		{
			if (FieldsandfiltersFactory::isVersion())
			{
				$path = JPluginHelper::getLayoutPath($type, $name, $layout);
			}
			else
			{
				$template      = JFactory::getApplication()->getTemplate();
				$defaultLayout = $layout;

				if (strpos($layout, ':') !== false)
				{
					// Get the template and file name from the string
					$temp          = explode(':', $layout);
					$template      = ($temp[0] == '_') ? $template : $temp[0];
					$layout        = $temp[1];
					$defaultLayout = ($temp[1]) ? $temp[1] : 'default';
				}

				// Build the template and base path for the layout
				$tPath = JPATH_THEMES . '/' . $template . '/html/plg_' . $type . '_' . $name . '/' . $layout . '.php';
				$bPath = JPATH_BASE . '/plugins/' . $type . '/' . $name . '/tmpl/' . $defaultLayout . '.php';
				$dPath = JPATH_BASE . '/plugins/' . $type . '/' . $name . '/tmpl/default.php';

				// If the template has a layout override use it
				if (file_exists($tPath))
				{
					$path = $tPath;
				}
				elseif (file_exists($bPath))
				{
					$path = $bPath;
				}
				else
				{
					$path = $dPath;
				}
			}

			self::setPath($path, $extension, $layout);
		}

		return $path;
	}

	/**
	 * @since       1.1.0
	 */
	public static function getParams($type, $plugin)
	{
		static $_params;

		$key = strtolower($type . $plugin);
		if (!isset($_params[$key]))
		{
			$params = new JRegistry();
			if ($plugin = JPluginHelper::getPlugin($type, $plugin))
			{
				$params->loadString($plugin->params);
			}

			$_params[$key] = $params;
		}

		return $_params[$key];
	}

	/**
	 * @since       1.1.0
	 */
	protected static function setPath($path, $extension, $layout = 'default')
	{
		self::$paths[$extension][$layout] = $path;
	}

	/**
	 * @since       1.1.0
	 */
	protected static function getPath($extension, $layout = 'default')
	{
		return (isset(self::$paths[$extension][$layout]) ? self::$paths[$extension][$layout] : null);
	}
}