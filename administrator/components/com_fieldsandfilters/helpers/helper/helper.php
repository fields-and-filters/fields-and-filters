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
 * Fieldsandfilters helper.
 */
class FieldsandfiltersHelper
{

	/**
	 * Configure the Linkbar.
	 *
	 * @since       1.0.0
	 */
	public static function addSubmenu($vName = '')
	{
		$htmlClass = FieldsandfiltersFactory::isVersion() ? 'JHtmlSidebar' : 'JSubMenuHelper';

		foreach (self::getButtons() AS $name => $button)
		{
			$htmlClass::addEntry(
				$button['text'],
				$button['link'],
				$vName == $name
			);
		}
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return    JObject
	 * @since       1.1.0
	 */
	public static function getActions()
	{
		$user   = JFactory::getUser();
		$result = new JObject;

		$assetName = 'com_fieldsandfilters';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}

	/**
	 * Gets a list of the buttons.
	 *
	 * @param bool  $groups
	 * @param array $exclude
	 *
	 * @return array
	 * @since       1.2.0
	 */
	public static function getButtons($groups = false, $exclude = array())
	{
		$buttons = array(
			'cpanel'            => array(
				'link'   => JRoute::_('index.php?option=com_fieldsandfilters'),
				'image'  => '',
				'icon'   => '',
				'text'   => JText::_('COM_FIELDSANDFILTERS_LEGEND_PANEL'),
				'access' => array('core.manage', 'com_fieldsandfilters', 'core.create', 'com_fieldsandfilters'),
				'group'  => 'COM_FIELDSANDFILTERS_HEADER_BASE'
			),
			'field'             => array(
				'link'   => JRoute::_('index.php?option=com_fieldsandfilters&task=field.add'),
				'image'  => 'faf-field',
				'icon'   => 'media/fieldsandfilters/administrator/images/icons/icon-48-add-field.png',
				'text'   => JText::_('COM_FIELDSANDFILTERS_QUICKICON_ADD_NEW_FIELD'),
				'access' => array('core.manage', 'com_fieldsandfilters', 'core.create', 'com_fieldsandfilters'),
				'group'  => 'COM_FIELDSANDFILTERS_HEADER_BASE'
			),
			'fields'            => array(
				'link'   => JRoute::_('index.php?option=com_fieldsandfilters&view=fields'),
				'image'  => 'faf-fields',
				'icon'   => 'media/fieldsandfilters/administrator/images/icons/icon-48-fields.png',
				'text'   => JText::_('COM_FIELDSANDFILTERS_QUICKICON_FIELDS'),
				'access' => array('core.manage', 'com_fieldsandfilters', 'core.create', 'com_fieldsandfilters'),
				'group'  => 'COM_FIELDSANDFILTERS_HEADER_BASE'
			),
			'elements'          => array(
				'link'   => JRoute::_('index.php?option=com_fieldsandfilters&view=elements'),
				'image'  => 'faf-contents',
				'icon'   => 'media/fieldsandfilters/administrator/images/icons/icon-48-elements.png',
				'text'   => JText::_('COM_FIELDSANDFILTERS_QUICKICON_ELEMENTS'),
				'access' => array('core.manage', 'com_fieldsandfilters', 'core.create', 'com_fieldsandfilters'),
				'group'  => 'COM_FIELDSANDFILTERS_HEADER_BASE'
			),
			'module.filters'    => array(
				'link'   => JRoute::_('index.php?option=com_modules&filter_module=mod_fieldsandfilters_filters'),
				'image'  => 'faf-module-filters',
				'icon'   => 'media/fieldsandfilters/administrator/images/icons/icon-48-fields.png',
				'text'   => JText::_('COM_FIELDSANDFILTERS_QUICKICON_MODULE_FILTERS'),
				'access' => array('core.manage', 'com_modules', 'core.create', 'com_modules'),
				'group'  => 'COM_FIELDSANDFILTERS_HEADER_MODULES'
			),
			'plugin.types'      => array(
				'link'   => JRoute::_('index.php?option=com_plugins&filter_folder=fieldsandfilterstypes&filter_search='),
				'image'  => 'faf-plugin-types',
				'icon'   => 'media/fieldsandfilters/administrator/images/icons/icon-48-plugin-types.png',
				'text'   => JText::_('COM_FIELDSANDFILTERS_QUICKICON_PLUGIN_TYPES'),
				'access' => array('core.manage', 'com_plugins', 'core.create', 'com_plugins'),
				'group'  => 'COM_FIELDSANDFILTERS_HEADER_PLUGINS'
			),
			'plugin.extensions' => array(
				'link'   => JRoute::_('index.php?option=com_plugins&filter_folder=fieldsandfiltersextensions&filter_search='),
				'image'  => 'faf-plugin-extensions',
				'icon'   => 'media/fieldsandfilters/administrator/images/icons/icon-48-plugin-extensions.png',
				'text'   => JText::_('COM_FIELDSANDFILTERS_QUICKICON_PLUGIN_EXTENSIONS'),
				'access' => array('core.manage', 'com_plugins', 'core.create', 'com_plugins'),
				'group'  => 'COM_FIELDSANDFILTERS_HEADER_PLUGINS'
			),
			'plugin.system'     => array(
				'link'   => JRoute::_('index.php?option=com_plugins&filter_folder=system&filter_search=fieldsandfilters'),
				'image'  => 'faf-plugin-system',
				'icon'   => 'media/fieldsandfilters/administrator/images/icons/icon-48-plugin-extensions.png',
				'text'   => JText::_('COM_FIELDSANDFILTERS_QUICKICON_PLUGIN_SYSTEM'),
				'access' => array('core.manage', 'com_plugins', 'core.create', 'com_plugins'),
				'group'  => 'COM_FIELDSANDFILTERS_HEADER_PLUGINS'
			)
		);

		if (!empty($exclude))
		{
			$buttons = array_diff_key($buttons, array_flip($exclude));
		}

		if ($groups)
		{
			foreach ($buttons AS $name => $button)
			{
				$group = $button['group'];

				if (!isset($buttons[$group]))
				{
					$buttons[$group] = array();
				}
				$buttons[$group][$name] = $button;

				unset($buttons[$name]);
			}
		}

		return $buttons;
	}

	public static function setUserStateFieldID($context, $name = 'field_id')
	{
		$app       = JFactory::getApplication();
		$userState = (array) $app->getUserState($context);

		if ($fieldID = $app->input->getInt($name))
		{
			if ($fieldID != JArrayHelper::getValue($userState, $name))
			{
				$userState[$name] = $fieldID;
				$app->setUserState($context, $userState);

				return true;
			}
		}
		else
		{
			if (!JArrayHelper::getValue($userState, $name))
			{
				$filterMode   = FieldsandfiltersModes::getMode(FieldsandfiltersModes::MODE_FILTER);
				$extensionsID = FieldsandfiltersFactory::getExtensions()->getExtensionsColumn('content_type_id');
				$fieldsID     = FieldsandfiltersFactory::getFields()->getFieldsByModeIDColumn('id', $extensionsID, $filterMode, array(1, -1));
				$fieldID      = current($fieldsID);

				$userState[$name] = $fieldID;
				$app->setUserState($context, $userState);

				return true;
			}
		}

		return false;
	}
}
