<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JLoader::import('joomla.filesystem.path');
JLoader::import('joomla.filesystem.folder');

/**
 * Script file of FieldsAndFilters Installer package
 */
class pkg_fieldsandfiltersInstallerScript
{

	/**
	 * Method to run after an install/update/uninstall method
	 * $adapter is the class calling this method
	 * $type is the type of change (install, update or discover_install)
	 *
	 * @return void
	 */
	function postflight($type, $adapter, $results)
	{
		if ($type !== 'install' || !$adapter->manifest->files)
		{
			return;
		}

		$table      = JTable::getInstance('extension');
		$plugins    = array();

		foreach ($adapter->manifest->files AS $file)
		{
			$attributes = $file->attributes();

			if ((string) $attributes->type != 'plugin')
			{
				continue;
			}

			$plugins[$attributes->id] = array(
				'type'      => 'plugin',
				'element'   => str_replace(sprintf('plg_%s_', $attributes->group), '', $attributes->id),
				'folder'    => $attributes->group
			);
		}

		foreach ($results AS $extension)
		{
			$name = $extension['name'];
			if (!$extension['result'] || !array_key_exists($name, $plugins))
			{
				continue;
			}

			if ($table->load($plugins[$name]))
			{
				$table->enabled = 0;
				$table->store();
			}
		}
	}
}