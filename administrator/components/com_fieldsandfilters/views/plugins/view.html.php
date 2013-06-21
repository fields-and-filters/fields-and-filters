<?php
/**
 * @version     1.0.0
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kulka.tomek@gmail.com> - 
 */

// No direct access
defined( '_JEXEC' ) or die;

/**
 * View class for a list of plugins types.
 */
class FieldsandfiltersViewPlugins extends JViewLegacy
{
	// Array of plugin types or groups plugin types
	protected $_plugins = null;
	
	/**
	 * Display the view
	 */
	public function display( $tpl = null )
	{
		switch( $this->getLayout() )
		{
			case 'types':
				// Load PluginTypes Helper
				JLoader::import( 'helpers.fieldsandfilters.plugintypes', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
				
				$this->_plugins = FieldsandfiltersPluginTypesHelper::getInstance()->getTypesGroup();
			break;
			case 'extensions':
				// Load PluginExtensions Helper
				JLoader::import( 'helpers.fieldsandfilters.pluginextensions', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
				
				$this->_plugins = FieldsandfiltersPluginExtensionsHelper::getInstance()->getExtensionsGroup();
				
			break;
		}
		
		if( is_null( $this->_plugins ) )
		{
			echo JText::sprintf( 'COM_FIELDSANDFILTERS_ERROR_NOT_PLUGINS_TPL', $this->getLayout() );
			return false;
		}
		
		$this->addToolbar();
		parent::display( $tpl );
	}
	
	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar()
	{
		JToolBarHelper::title( JText::_( 'COM_FIELDSANDFILTERS_HEADER_PLUGIN_' . strtoupper( $this->getLayout() ) ) );
	}
}