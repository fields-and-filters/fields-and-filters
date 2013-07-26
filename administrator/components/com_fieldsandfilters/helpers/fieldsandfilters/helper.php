<?php
/**
 * @version     1.1.0
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
	public static function addSubmenu( $vName = '' )
	{
		$htmlClass = FieldsandfiltersFactory::isVersion() ? 'JHtmlSidebar' : 'JSubMenuHelper';
		
		$htmlClass::addEntry(
			JText::_( 'COM_FIELDSANDFILTERS_LEGEND_PANEL' ),
			'index.php?option=com_fieldsandfilters',
			$vName == 'cpanel'
		);
		
		$htmlClass::addEntry(
			JText::_( 'COM_FIELDSANDFILTERS_LEGEND_FIELDS' ),
			'index.php?option=com_fieldsandfilters&view=fields',
			$vName == 'fields'
		);
		
		$htmlClass::addEntry(
			JText::_( 'COM_FIELDSANDFILTERS_LEGEND_ELEMENTS' ),
			'index.php?option=com_fieldsandfilters&view=elements',
			$vName == 'elements'
		);
		
		$htmlClass::addEntry(
			JText::_( 'COM_FIELDSANDFILTERS_LEGEND_PLUGIN_TYPES' ),
			'index.php?option=com_plugins&filter_folder=fieldsandfiltersTypes',
			$vName == 'plugin_types'
		);
		
		$htmlClass::addEntry(
			JText::_( 'COM_FIELDSANDFILTERS_LEGEND_PLUGIN_EXTENSIONS' ),
			'index.php?option=com_plugins&filter_folder=fieldsandfiltersExtensions',
			$vName == 'plugin_extensions'
		);
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	JObject
	 * @since       1.1.0
	 */
	public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;
		
		$assetName = 'com_fieldsandfilters';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);
		
		foreach( $actions as $action )
		{
			$result->set( $action, $user->authorise( $action, $assetName ) );
		}
		
		return $result;
	}
}
