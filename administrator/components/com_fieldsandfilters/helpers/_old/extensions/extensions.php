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
 * FieldsandfiltersExtensions
 *
 * @package     com_fieldsandfilters
 * @since       1.1.0
 */
class FieldsandfiltersExtensions extends KextensionsBufferCore
{
	/**
	 * @since       1.2.0
	 **/
	const EXTENSION_DEFAULT = 'allextensions';

	/**
	 * @since       1.2.0
	 **/
	const PLUGIN_FOLDER = 'fieldsandfiltersextensions';

	/**
	 * Temporarily element name var
	 *
	 * @since       1.0.0
	 */
	protected $_elementName;

	/**
	 * @since       1.1.0
	 **/
	public function getExtensions($withXML = false)
	{
		if (!property_exists($this->_data, self::PLUGIN_FOLDER))
		{
			$data     = $this->_getData(self::PLUGIN_FOLDER);
			$elements = $data->elements;

			$default                     = new JObject;
			$default->name               = self::EXTENSION_DEFAULT;
			$default->type               = 'com_fieldsandfilters';
			$default->option             = self::EXTENSION_DEFAULT;
			$default->content_type_alias = 'com_fieldsandfilters.' . $default->option;

			$data->elements->set($default->option, $default);

			JPluginHelper::importPlugin(self::PLUGIN_FOLDER);
			// Trigger the onFieldsandfiltersPrepareFormField event.
			JFactory::getApplication()->triggerEvent('onFieldsandfiltersPrepareExtensions', array($data->elements));

			$typesAlias = KextensionsArray::getColumn($data->elements, 'content_type_alias');
			$query      = $this->_getQuery($typesAlias);
			$types      = (array) $this->_db->setQuery($query)->loadObjectList('type_alias');

			$data->set('xml', (boolean) $withXML);

			foreach ($data->elements AS &$element)
			{
				$element->content_type_id = (int) array_key_exists($element->content_type_alias, $types) ? $types[$element->content_type_alias]->type_id : 0;

				/* @deprecated  1.2.0 */
				// $element->extension_type_id = $element->content_type_id;
				/* @end deprecated  1.2.0 */

				if ($data->xml)
				{
					FieldsandfiltersXml::getPluginOptionsForms($element);
				}
			}
		}
		elseif ($withXML && !$this->_getData(self::PLUGIN_FOLDER)->xml)
		{
			$data = $this->_getData(self::PLUGIN_FOLDER);

			$data->set('xml', (boolean) $withXML);

			foreach ($data->elements AS &$element)
			{
				FieldsandfiltersXml::getPluginOptionsForms($element);
			}
		}

		return $this->_getData(self::PLUGIN_FOLDER)->elements;
	}

	/**
	 * @since       1.1.0
	 **/
	public function getExtensionsByName($names, $defaultProperty = true, $withXML = false)
	{
		$this->_elementName = 'name';
		$this->config->def('elementsString', true);

		return $this->_getExtensionsBy($names, $defaultProperty, $withXML);
	}

	/**
	 * @since       1.2.0
	 **/
	public function getExtensionsByTypeID($ids, $defaultProperty = true, $withXML = false)
	{
		$this->_elementName = 'content_type_id';

		return $this->_getExtensionsBy($ids, $defaultProperty, $withXML);
	}

	/**
	 * @since       1.2.0
	 **/
	public function getExtensionsByTypeAlias($alias, $defaultProperty = true, $withXML = false)
	{
		$this->_elementName = 'content_type_alias';
		$this->config->def('elementsString', true);

		return $this->_getExtensionsBy($alias, $defaultProperty, $withXML);
	}

	/**
	 * @since       1.2.0
	 **/
	public function getExtensionsByOption($option, $defaultProperty = true, $withXML = false)
	{
		$this->_elementName = 'option';
		$this->config->def('elementsString', true);

		return $this->_getExtensionsBy($option, $defaultProperty, $withXML);
	}

	/**
	 * @since       1.1.0
	 **/
	public function getExtensionsGroup()
	{
		static $group;

		if (is_null($group))
		{
			$group   = new JRegistry;
			$plugins = get_object_vars($this->getExtensions(true));

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
	protected function _getQuery(array $typesAlias = array())
	{
		// Get db and query
		$query = $this->_db->getQuery(true)
			->select(array(
				$this->_db->quoteName('type_id'),
				$this->_db->quoteName('type_alias'),
			))
			->from($this->_db->quoteName('#__content_types'));

		if (!empty($typesAlias))
		{
			$query->where($this->_db->quoteName('type_alias') . ' IN (' . implode(',', array_map(array($this->_db, 'quote'), $typesAlias)) . ')');
		}

		return $query;
	}

	/**
	 * @since       1.1.0
	 **/
	protected function _getExtensionsBy($elements, $defaultProperty = true, $withXML = false)
	{
		$elements = array_unique((array) $elements);

		if (!$this->config->def('elementsString'))
		{
			JArrayHelper::toInteger($elements);
		}

		if (!empty($elements))
		{
			$extensions = get_object_vars($this->getExtensions($withXML));

			while ($extension = array_shift($extensions))
			{
				if (in_array($extension->{$this->_elementName}, $elements))
				{
					$property = $defaultProperty ? $this->_elementName : 'extension';
					$this->buffer->set($extension->$property, $extension);
				}
			}
		}

		return $this->_returnBuffer(true);
	}

	/**
	 * Reset arguments
	 *
	 * @param    boolean $reset reset arguments if you need
	 *
	 * @return      boolean         if is reset
	 * @since       1.0.0
	 **/
	protected function _resetArgs($reset = null)
	{
		$reset = parent::_resetArgs($reset);

		if ($reset)
		{
			$this->_elementName = null;
		}

		return $reset;
	}

	/** __call method generator methods:
	 * getExtensionsPivot( $pivot, $withXML = false )
	 * getExtensionsColumn( $column, $withXML = false )
	 *
	 * getExtensionsByIDPivot( $pivot, $ids, $defaultProperty = true, $withXML = false )
	 * getExtensionsByIDColumn( $column, $ids, $defaultProperty = true, $withXML = false )
	 *
	 * getExtensionsByNamePivot( $pivot, $names, $defaultProperty = true, $withXML = false )
	 * getExtensionsByNameColumn( $column, $names, $defaultProperty = true, $withXML = false )
	 *
	 * getExtensionsByTypeIDPivot( $pivot, $ids, $defaultProperty = true, $withXML = false )
	 * getExtensionsByTypeIDColumn( $column, $ids, $defaultProperty = true, $withXML = false )
	 *
	 * getExtensionsByTypeAliasPivot( $pivot, $alias, $defaultProperty = true, $withXML = false )
	 * getExtensionsByTypeAliasColumn( $column, $alias, $defaultProperty = true, $withXML = false )
	 *
	 * @since       1.1.0
	 **/
}