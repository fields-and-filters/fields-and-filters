<?php
/**
 * @version     1.1.1
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kulka.tomek@gmail.com> - 
 */

// No direct access
defined('_JEXEC') or die;

/**
 * HTML View class for the Cpanel component
 */
class FieldsandfiltersViewCpanel extends JViewLegacy
{
	protected $buttons = array();
	
	/**
	 * Display the view
	 * 
	 * @since	1.0.0
	 */
	public function display( $tpl = null )
	{
		$option = JFactory::getApplication()->input->get( 'option' );
		
		$this->buttons['base'] = array(
			// button - fields list
			array(
				'link' => JRoute::_( 'index.php?option=' . $option . '&view=fields' ),
				'image' => 'media/fieldsandfilters/administrator/images/icons/icon-48-fields.png',
				'text' => JText::_( 'COM_FIELDSANDFILTERS_QUICKICON_FIELDS' ),
				'access' => array( 'core.manage', $option, 'core.create', $option )
			),
			// button - field add
			array(
				'link' => JRoute::_( 'index.php?option=' . $option . '&task=field.add' ),
				'image' => 'media/fieldsandfilters/administrator/images/icons/icon-48-add-field.png',
				'text' => JText::_( 'COM_FIELDSANDFILTERS_QUICKICON_ADD_NEW_FIELD' ),
				'access' => array( 'core.manage', $option, 'core.create', $option )
			),
			// button - elements
			array(
				'link' => JRoute::_( 'index.php?option=' . $option . '&view=elements' ),
				'image' => 'media/fieldsandfilters/administrator/images/icons/icon-48-elements.png',
				'text' => JText::_( 'COM_FIELDSANDFILTERS_QUICKICON_ELEMENTS' ),
				'access' => array( 'core.manage', $option, 'core.create', $option )
			)
		);
		
		$this->buttons['modules'] = array(
			// button - module filters
			array(
				'link' => JRoute::_( 'index.php?option=com_modules&filter_module=mod_fieldsandfilters' ),
				'image' => 'media/fieldsandfilters/administrator/images/icons/icon-48-fields.png',
				'text' => JText::_( 'COM_FIELDSANDFILTERS_QUICKICON_MODULE_FILTERS' ),
				'access' => array( 'core.manage', 'com_modules', 'core.create', 'com_modules' )
			),
		);
		
		$this->buttons['plugins'] = array(
			// button - plugin types
			array(
				'link' => JRoute::_( 'index.php?option=com_plugins&filter_folder=fieldsandfiltersTypes&filter_search=' ),
				'image' => 'media/fieldsandfilters/administrator/images/icons/icon-48-plugin-types.png',
				'text' => JText::_( 'COM_FIELDSANDFILTERS_QUICKICON_PLUGIN_TYPES' ),
				'access' => array( 'core.manage', 'com_plugins', 'core.create', 'com_plugins' )
			),
			// button - plugin extensions
			array(
				'link' => JRoute::_( 'index.php?option=com_plugins&filter_folder=fieldsandfiltersExtensions&filter_search=' ),
				'image' => 'media/fieldsandfilters/administrator/images/icons/icon-48-plugin-extensions.png',
				'text' => JText::_( 'COM_FIELDSANDFILTERS_QUICKICON_PLUGIN_EXTENSIONS' ),
				'access' => array( 'core.manage', 'com_plugins', 'core.create', 'com_plugins' )
			),
			// button - plugin system
			array(
				'link' => JRoute::_( 'index.php?option=com_plugins&filter_folder=system&filter_search=fieldsandfilters' ),
				'image' => 'media/fieldsandfilters/administrator/images/icons/icon-48-plugin-extensions.png',
				'text' => JText::_( 'COM_FIELDSANDFILTERS_QUICKICON_PLUGIN_SYSTEM' ),
				'access' => array( 'core.manage', 'com_plugins', 'core.create', 'com_plugins' )
			)
		);
		
		$this->addToolbar();
		parent::display( $tpl );
	}
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.1.0
	 */
	protected function addToolbar()
	{
		$canDo = FieldsandfiltersHelper::getActions();
		
		JToolBarHelper::title( JText::_( 'COM_FIELDSANDFILTERS' ), 'fieldsandfilters.png' );
		
		if( $canDo->get( 'core.admin' ) )
		{
			JToolBarHelper::preferences( 'com_fieldsandfilters' );
		}
	}
}
