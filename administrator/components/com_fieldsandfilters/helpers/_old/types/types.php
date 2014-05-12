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
 * FieldsandfiltersTypes
 *
 * @package     com_fieldsandfilters
 * @since       1.0.0
 */
class FieldsandfiltersTypes extends KextensionsBufferCore
{
	/**
	 * @since       1.2.0
	 **/
	const PLUGIN_FOLDER = 'fieldsandfilterstypes';

	/**
	 * @since       1.1.0
	 **/
	public function getTypes($withXML = false)
	{
		if (!property_exists($this->_data, self::PLUGIN_FOLDER))
		{
			$data     = $this->_getData(self::PLUGIN_FOLDER);
			$elements = $data->elements;

			$data->set('xml', (boolean) $withXML);

			$pluginsTypes = $this->_db->setQuery($this->_getQuery())->loadObjectList();

			if (!empty($pluginsTypes))
			{
				while ($pluginType = array_shift($pluginsTypes))
				{
					$elements->set($pluginType->name, $pluginType);

					if ($data->xml)
					{
						FieldsandfiltersXML::getPluginOptionsForms($elements->get($pluginType->name), array());
					}
				}
			}
		}
		elseif ($withXML && !$this->_getData(self::PLUGIN_FOLDER)->xml)
		{
			$data = $this->_getData(self::PLUGIN_FOLDER);

			$data->set('xml', (boolean) $withXML);

			$elements = get_object_vars($data->elements);

			while ($element = array_shift($elements))
			{
				FieldsandfiltersXML::getPluginOptionsForms($element, array());
			}
		}

		return $this->_getData(self::PLUGIN_FOLDER)->elements;
	}

	/**
	 * @since       1.1.0
	 **/
	public function getTypesByName($names, $withXML = false)
	{
		$names = array_unique((array) $names);

		if (!empty($names))
		{
			$types = get_object_vars($this->getTypes($withXML));

			while ($type = array_shift($types))
			{
				if (in_array($type->name, $names))
				{
					$this->buffer->set($type->name, $type);
				}
			}
		}

		return $this->_returnBuffer(true);
	}

	/**
	 * @since       1.1.0
	 **/
	public function getTypesGroup()
	{
		static $group;

		if (is_null($group))
		{
			$group   = new JRegistry;
			$plugins = get_object_vars($this->getTypes(true));

			while ($plugin = array_shift($plugins))
			{
				if (isset($plugin->forms))
				{
					$forms = $plugin->forms->getProperties();
					while ($form = array_shift($forms))
					{
						$group->set(($form->group->name . '.' . $plugin->name), $plugin);
					}
				}
			}
		}

		return $group;
	}

	/**
	 * @since       1.0.0
	 **/
	protected function _getQuery()
	{
		// Get db and query
		$query = $this->_db->getQuery(true);

		$query->select(array(
			$this->_db->quoteName('folder', 'type'),
			$this->_db->quoteName('element', 'name'),
			$this->_db->quoteName('params')
		))
			->from($this->_db->quoteName('#__extensions'))
			->where(array(
				$this->_db->quoteName('type') . ' = ' . $this->_db->quote('plugin'), // Extension mast by a plugin
				$this->_db->quoteName('folder') . ' = ' . $this->_db->quote(self::PLUGIN_FOLDER), // Extension where plugin folder
				$this->_db->quoteName('enabled') . ' = 1' // Extension where enabled
			));

		$query->order($this->_db->quoteName('ordering') . ' ASC');

		return $query;
	}

	/** __call method generator methods:
	 * getTypesPivot($pivot, $withXML = false)
	 * getTypesColumn($column, $withXML = false)
	 *
	 * getTypesByNamePivot($pivot, $names, $withXML = false)
	 * getTypesByNameColumn($column, $names, $withXML = false)
	 *
	 * @since       1.1.0
	 **/
}