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
class FieldsandfiltersHtmlOptions
{
	/**
	 * Returns an array of standard states filter options.
	 *
	 * @param   array $config   An array of configuration options.
	 *                          This array can contain a list of key/value pairs where values are boolean
	 *                          and keys can be taken from 'published', 'unpublished', 'archived', 'trash', 'all'.
	 *                          These pairs determine which values are displayed.
	 *
	 * @return  string  The HTML code for the select tag
	 *
	 * @since       1.0.0
	 */
	public static function states($config = array())
	{
		// Build the active state filter options.
		$options = array();
		if (!array_key_exists('published', $config) || $config['published'])
		{
			$options[] = JHtml::_('select.option', '1', JText::_('JPUBLISHED'));
		}
		if (!array_key_exists('unpublished', $config) || $config['unpublished'])
		{
			$options[] = JHtml::_('select.option', '0', JText::_('JUNPUBLISHED'));
		}
		if (!array_key_exists('adminonly', $config) || $config['adminonly'])
		{
			$options[] = JHtml::_('select.option', '-1', JText::_('COM_FIELDSANDFILTERS_HTML_ONLYADMIN'));
		}

		return $options;
	}

	/**
	 * Method to get the available options plugin item type. value & text
	 *
	 * @return    array    array associate value and text
	 * @since       1.1.0
	 */
	public static function types($excluded = array())
	{
		$options = array();

		if ($types = get_object_vars(FieldsandfiltersFactory::getTypes()->getTypes()))
		{
			while ($type = array_shift($types))
			{
				if (!in_array($type->name, $excluded))
				{
					// load plugin language
					$extension = 'plg_' . $type->type . '_' . $type->name;
					KextensionsLanguage::load($extension, JPATH_ADMINISTRATOR);

					$options[] = JHtml::_('select.option', $type->name, JText::_(strtoupper($extension)));
				}
			}
		}

		return $options;
	}

	/**
	 * Method to get the available options plugin item type. value & text
	 *
	 * @return    array    array associate value and text
	 * @since       1.1.0
	 */
	public static function extensions($excluded = array())
	{
		$options = array();

		if ($extensions = get_object_vars(FieldsandfiltersFactory::getExtensions()->getExtensions()))
		{
			while ($extension = array_shift($extensions))
			{
				if (!in_array($extension->name, $excluded))
				{
					// load plugin language
					if ($extension->name != FieldsandfiltersExtensions::EXTENSION_DEFAULT)
					{
						$extensionName = 'plg_' . $extension->type . '_' . $extension->name;
						KextensionsLanguage::load($extensionName, JPATH_ADMINISTRATOR);
					}
					else
					{
						$extensionName = $extension->type . '_' . $extension->name;
					}

					$options[] = JHtml::_('select.option', $extension->content_type_id, JText::_(strtoupper($extensionName)));
				}
			}
		}

		return $options;
	}

	/**
	 * Method to get the available options fields item. value & text
	 *
	 * @return    array    array associate value and text
	 * @since       1.1.0
	 */
	public static function fields($modes = FieldsandfiltersModes::MODE_FILTER, $state = 1)
	{
		$options = array();

		// Load Extensions Helper
		$extenionsID = FieldsandfiltersFactory::getExtensions()->getExtensionsColumn('content_type_id');

		$modes = FieldsandfiltersModes::getModes($modes, array(), true);

		// Load Fields Helper
		$fieldsHelper = FieldsandfiltersFactory::getFields();

		if (!empty($modes))
		{
			$fields = $fieldsHelper->getFieldsByModeID($extenionsID, $modes, $state);
		}
		else
		{
			$fields = $fieldsHelper->getFields($extenionsID, $state);
		}

		if ($fields = get_object_vars($fields))
		{
			while ($field = array_shift($fields))
			{
				$options[] = JHtml::_('select.option', $field->id, $field->name);
			}
		}

		return $options;
	}

	/**
	 * Method to get the available options field values item. value & text
	 *
	 * @return    array    array associate value and text
	 * @since       1.1.0
	 */
	public static function fieldValues($fieldID, $state = 1)
	{
		$options = array();

		$extenionsID = FieldsandfiltersFactory::getExtensions()->getExtensionsColumn('content_type_id');
		$field       = FieldsandfiltersFactory::getFields()->getFieldsByID($extenionsID, $fieldID, $state, true);

		if (($field = $field->get($fieldID)) && ($values = $field->values->getProperties(true)))
		{
			while ($value = array_shift($values))
			{
				$options[] = JHtml::_('select.option', $value->id, $value->value);
			}
		}

		return $options;
	}
}
