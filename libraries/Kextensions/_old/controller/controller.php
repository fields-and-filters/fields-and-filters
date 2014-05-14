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
 * KextensionsController
 *
 * @since       1.0.0
 */
class KextensionsController
{
	/**
	 * @since       1.0.0
	 */
	protected static $paths = array();

	/**
	 * Returns a Controller object, always creating it
	 *
	 * @param   string $type   The contlorer type to instantiate
	 * @param   string $prefix Prefix for the controller class name. Optional.
	 * @param   array  $config Configuration array for controller. Optional.
	 *
	 * @return  mixed   A model object or false on failure
	 *
	 * @since       1.1.0
	 */
	public static function getInstance($type, $prefix = '', $config = array())
	{
		// Check for array format.
		$filter = JFilterInput::getInstance();

		$type            = $filter->clean($type, 'cmd');
		$prefix          = $filter->clean($prefix, 'cmd');
		$controllerClass = $prefix . ucfirst($type);

		if (!class_exists($controllerClass))
		{
			if (!isset(self::$paths[$controllerClass]))
			{
				// Get the environment configuration.
				$basePath   = JArrayHelper::getValue($config, 'base_path', JPATH_COMPONENT);
				$nameConfig = empty($type) ? array('name' => 'controller') : array('name' => $type, 'format' => JFactory::getApplication()->input->get('format', '', 'word'));

				// Define the controller path.
				$paths[] = $basePath . '/controllers';
				$paths[] = $basePath;

				$path = JPath::find($paths, self::createFileName($nameConfig));

				self::$paths[$controllerClass] = $path;

				// If the controller file path exists, include it.
				if ($path)
				{
					require_once $path;
				}
			}

			if (!class_exists($controllerClass))
			{
				JLog::add(JText::sprintf('JLIB_APPLICATION_ERROR_INVALID_CONTROLLER', $controllerClass), JLog::WARNING, 'kextensions');

				return false;
			}
		}

		return new $controllerClass($config);
	}

	/**
	 * Create the filename for a resource.
	 *
	 * @param   string $type  The resource type to create the filename for.
	 * @param   array  $parts An associative array of filename information. Optional.
	 *
	 * @return  string  The filename.
	 *
	 * @since       1.0.0
	 */
	protected static function createFileName($parts = array())
	{
		if (!empty($parts['format']))
		{
			if ($parts['format'] == 'html')
			{
				$parts['format'] = '';
			}
			else
			{
				$parts['format'] = '.' . $parts['format'];
			}
		}
		else
		{
			$parts['format'] = '';
		}

		$filename = strtolower($parts['name'] . $parts['format'] . '.php');

		return $filename;
	}
}