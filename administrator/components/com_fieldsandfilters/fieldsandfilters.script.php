<?php
/**
 * @version     1.1.1
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JLoader::import( 'joomla.filesystem.path' );
JLoader::import( 'joomla.filesystem.folder' );

/**
 * Script file of FieldsAndFilters Installer component
 */
class com_fieldsandfiltersInstallerScript
{
	protected $helper;
	
	protected $_extensions = array(
			'plg_system_fieldsandfilters',
			'plg_fieldsandfiltersextensions_content',
			'plg_fieldsandfilterstypes_checkboxlist',
			'plg_fieldsandfilterstypes_image',
			'plg_fieldsandfilterstypes_input',
			'plg_fieldsandfilterstypes_textarea',
			'mod_fieldsandfilters'
	);
	
	/**
         * Method to run before an install/update/uninstall method
         * $adapter is the class calling this method
         * $type is the type of change (install, update or discover_install)
         *
         * @return void
         */
        function preflight( $type, $adapter ) 
        {
		if( !$this->createHelper( $type, $adapter ) )
		{
			return;
		}
		
		$helper = $this->getHelper();
		
		if( $type == 'update' && version_compare( $helper->getOldVersion(), 1.2, '<' ) )
		{
			self::changePlugins( 'fieldsandfiltersExtensions' );
			self::changePlugins( 'fieldsandfiltersTypes' );
			
			self::changeModules( 'mod_fieldsandfilters', 'mod_fieldsandfilters_filters' );

		}
	}
	
	/**
         * Method to run after an install/update/uninstall method
         * $adapter is the class calling this method
         * $type is the type of change (install, update or discover_install)
         *
         * @return void
         */
        function postflight( $type, $adapter ) 
        {
		if( !$this->createHelper( $type, $adapter ) )
		{
			return;
		}
		
		$helper = $this->getHelper();
		
		if( $type == 'install' || ( $type == 'update' ) )
		{
			$helper->checkContentType();
			return $this->addExtensions();
		}
		
		return true;
        }
	
	/**
         * Method to uninstall the extension
         * $adapter is the class calling this method
         *
         * @return void
         */
        public function uninstall( $adapter ) 
        {
		if( empty( $this->_extensions ) || !is_array( $this->_extensions ) )
		{
			return;
		}
		
		$app 	= JFactory::getApplication();
		$table 	= JTable::getInstance('extension');
		
		$eid	= $app->input->get( 'cid', array(), 'array' );
		JArrayHelper::toInteger( $eid, array() );
		
		while( $extension = array_shift( $this->_extensions ) )
		{
			$installer      = new JInstaller;
			
			$table->reset();
			switch( substr( $extension, 0, 4 ) )
			{
				case 'mod_' :
					$table->load( array( 'element' => $extension ) );
				break;
				case 'plg_' :
					list( $type, $folder, $element ) = explode( '_', $extension );
					$table->load( array(
							'folder' 	=> $folder,
							'element' 	=> $element,
					) );
				break;
				default :
					continue;
				break;
			}
			
			if( in_array( $table->extension_id, $eid ) || !$table->extension_id || !$table->type )
			{
				continue;
			}
			
			if( $installer->uninstall( $table->type, $table->extension_id ) )
			{
				// Extension uninstalled sucessfully
				$msg = JText::sprintf( 'COM_FIELDSANDFILTERS_SUCCESS_UNINSTALL_EXTENSION', $extension );
				$app->enqueueMessage( $msg );
			}
			else
			{
				// Extension uninstalled error
				$msg = JText::sprintf( 'COM_FIELDSANDFILTERS_ERROR_NOT_UNINSTALL_EXTENSION', $extension );
				$app->enqueueMessage( $msg, 'error' );
			}
		}
		
		return true;
        }
	
	protected function createHelper( $type, $adapter )
	{
		if( !( self::loadClass( 'script', $adapter ) && self::loadClass( 'contenttype', $adapter ) ) )
		{
			return false;
		}
		
		if( !$this->helper instanceof FieldsandfiltersInstallerScript )
		{
			$this->helper 	= new FieldsandfiltersInstallerScript( $type, $adapter, 'allextensions' );
			if( $type = 'uninstall' )
			{
				$contentType 	= $this->helper->getContentType()
					->set( 'type_title', 'Fieldsandfilters Field' )
					->set( 'type_alias', 'com_fieldsandfilters.field' )
					->set( 'table.special', array(
						'dbtable' => '#__fieldsandfilters_fields',
						'key'     => 'field_id',
						'type'    => 'Field',
						'prefix'  => 'FieldsandfiltersTable',
					) )
					->set( 'table.common', new stdClass )
					->set( 'field_mappings.common', array(
						'core_content_item_id'	=> 'field_id',
						'core_title'		=> 'field_name',
						'core_state'		=> 'state',
						'core_alias'		=> 'field_alias',
						'core_body'		=> 'description',
						'core_access'		=> 'access',
						'core_params'		=> 'params',
						'core_language'		=> 'language',
						'core_ordering'		=> 'ordering'
					) )
					->set( 'field_mappings.special', array(
						'field_type'		=> 'field_type',
						'content_type_id'	=> 'content_type_id',
						'mode'			=> 'mode',
						'required'		=> 'required'
					) )
					->set( 'content_history_options.formFile', 'administrator/components/com_fieldsandfilters/models/forms/field.xml' )
					->addHistoryOptions( 'hideFields', 'mode' )
					->addHistoryOptions( 'convertToInt', array( 'content_type_id', 'mode', 'ordering', 'state', 'required' ) )
					->addDisplayLookup( 'content_type_id', '#__content_types', 'type_id', 'type_title' );
			}
		}
		else
		{
			$this->helper->setType( $type );
		}
		
		return true;
	}
	
	protected function getHelper()
	{
		return $this->helper;
	}
	
	protected function addExtensions()
	{
		$helper = $this->getHelper();
		
		// Get an installer instance
		$app	        = JFactory::getApplication();
		$parent		= $helper->getAdapter()->getParent();
		$manifest	= $parent->getManifest();
		
		if( $manifest->administration->files )
		{
			$element = $manifest->administration->files;
		}
		elseif( $manifest->files )
		{
			$element = $manifest->files;
		}
		else
		{
			$element = null;
		}
		
		$folder = $element ? ( '/' . (string) $element->attributes()->folder ) : '/administrator';
		
		$sourcePath     = $parent->getPath( 'source' );
		$adminPath	= $sourcePath . $folder;
		$extensionsPath = $adminPath . '/extensions';
		
		// Load language
		$helper->getAdapter()->loadLanguage( $adminPath );
		
		if( !is_dir( $extensionsPath ) || empty( $this->_extensions ) || !is_array( $this->_extensions ) )
		{
			$app->enqueueMessage( JText::_( 'COM_FIELDSANDFILTERS_ERROR_NOT_EXISTS_EXTENSIONS' ), 'error' );
			return false;
		}
		
		$table = JTable::getInstance( 'extension' );
		while( $extension = array_shift( $this->_extensions ) )
		{
			$installer      = new JInstaller;
			$extensionDir 	= $extensionsPath . '/' . $extension;
			
			if( !( $installer->install( $extensionDir ) ) )
			{
				// There was an error installing the package
				$msg = JText::sprintf( 'COM_FIELDSANDFILTERS_ERROR_NOT_INSTALL_EXTENSION', $extension );
				$app->enqueueMessage( $msg, 'error' );
			}
			else
			{
				$type = (string) $installer->manifest->attributes()->type;
				if( $type == 'plugin' )
				{
					$table->reset();
					unset( $element );
					
					// Set the installation path
					if( count( $installer->manifest->files->children() ) )
					{
						foreach( $installer->manifest->files->children() as $file )
						{
							if( (string) $file->attributes()->$type )
							{
								$element = (string) $file->attributes()->$type;
								break;
							}
						}
					}
					
					$folder = (string) $installer->manifest->attributes()->group;
					
					if( !empty( $folder ) && !empty( $element ) && $table->load( array( 'folder' => $folder, 'element' => $element, 'enabled' => 0 ) ) )
					{
						$table->enabled = 1;
						
						if( !$table->store() )
						{
							// false
						}
					}	
				}
				
				// Extension installed/updated sucessfully
				$route = strtolower( $installer->getAdapter( $type )->get( 'route' ) );
				$text = ( $route == 'update' ) ? 'COM_FIELDSANDFILTERS_SUCCESS_UPGRADE_EXTENSION' : 'COM_FIELDSANDFILTERS_SUCCESS_INSTALL_EXTENSION';
				$msg = JText::sprintf( $text, $extension );
				$app->enqueueMessage( $msg );
			}
		}
		
		return true;
	}
	
	protected static function loadInstallClass( $class, $adapter )
	{
		$installerClass = 'FieldsandfiltersInstaller' . ucfirs( $class );
		if( !class_exists( $installerClass ) )
		{
			$path = 'administrator.helpers.installer.' . strtolower( $class );
			JLoader::import( $path, $adapter->getParent()->getPath('source') );
			
			if( !class_exists( $installerClass ) )
			{
				// FieldsandfiltersInstallerScript error
				JFactory::getApplication()->enqueueMessage( $installerClass . ' class not exists', 'error' );
				return false;
			}
		}
		
		return true;
	}
	
	protected static function changePlugins( $folder )
	{
		$db = JFactory::getDbo();
		
		// change folder and name plugins in table extensions
		$query = $db->getQuery( true )
			->select( '*' )
			->from( $db->quoteName( '#__extensions' ) )
			->where( array(
				$db->quoteName( 'folder' ) . ' = ' . $db->quote( $folder ),
				$db->quoteName( 'type' ) . ' = ' . $db->quote( 'plugin' )
			) );
		
		$plugins = (array) $db->setQuery( $query )->loadObjectList();
		
		while( $plugin = array_shift( $plugins ) )
		{
			$plugin->folder = strtolower( $plugin->folder );
			$plugin->name = 'plg_' . $plugin->folder . '_' . $plugin->element;
			
			$db->updateObject( '#__extensions', $plugin, 'extension_id' );
		}
		
		$langs = JLanguage::getKnownLanguages(JPATH_ADMINISTRATOR);
		
		// change language name files to lower case
		foreach( $langs AS $lang )
		{
			$path = JPATH_ADMINISTRATOR . '/language/' . $lang['tag'] . '/';
			$filter = '^' . $lang['tag'] . '\.plg_' . $folder . '_(.*)\.ini$'; 
			$files = JFolder::files( $path, $filter );
			
			foreach( $files AS $file )
			{
				$newFile = strtolower( $file );
				@rename( $path . $file, $path . $newFile );
			}
		}
		
		// change plugin folder name to lower case
		$path = JPath::clean( JPATH_PLUGINS . '/' . $folder );
		if( is_dir( $path ) )
		{
			$newPath = JPath::clean( JPATH_PLUGINS . '/' . strtolower( $folder ) );
			@rename( $path, $newPath );
		}
		
		// change plugin folder ane to lowe case in templates
		$templates = self::getTempaltes();
		
		if( !empty( $templates ) )
		{
			foreach( $templates as $template )
			{
				$path = JPath::clean( JPATH_SITE . '/templates/' . $template->element . '/html/' );
				$filter = '^plg_' . $folder . '_(.*)$';
				
				if( is_dir( $path ) && ( $files = JFolder::folders( $path, $filter ) ) )
				{
					foreach( $files as $file )
					{
						$newFile = strtolower( $file );
						@rename( $path . $file, $path . $newFile );
					}
				}
			}
		}
	}
	
	protected static function changeModules( $old_module, $new_module )
	{
		$db = JFactory::getDbo();
		
		// change name modules in table extensions
		$query = $db->getQuery( true )
			->select( '*' )
			->from( $db->quoteName( '#__extensions' ) )
			->where( array(
				$db->quoteName( 'type' ) . ' = ' . $db->quote( 'module' ),
				$db->quoteName( 'element' ) . ' = ' . $db->quote( $old_module )
			) );
		
		$modules = (array) $db->setQuery( $query )->loadObjectList();
		
		while( $module = array_shift( $modules ) )
		{
			$module->name = $new_module;
			$module->element = $new_module;
			
			$db->updateObject( '#__extensions', $module, 'extension_id' );
		}
		
		$query->clear();
		
		// change name modules in table modules
		$query->update( $db->quoteName( '#__modules' ) )
			->set( $db->quoteName('module') . ' = ' . $db->quote( $new_module ) )
			->where( $db->quotename('module') . ' = ' . $db->quote( $old_module ) );
			
		$db->setQuery( $query )->execute();
		
		$langs = JLanguage::getKnownLanguages(JPATH_SITE);
		
		// change language name files to lower case
		foreach( $langs AS $lang )
		{
			$path = JPATH_SITE . '/language/' . $lang['tag'] . '/';
			$filter = '^' . $lang['tag'] . '\.' . $old_module . '\.ini$'; 
			$files = JFolder::files( $path, $filter );
			
			foreach( $files AS $file )
			{
				$newFile = str_replace( $old_module, $new_module, $file );
				@rename( $path . $file, $path . $newFile );
			}
		}
		
		// change plugin folder name to lower case
		$path = JPath::clean( JPATH_SITE . '/modules/' . $old_module . '/' );
		
		if( is_dir( $path ) )
		{
			$filter = '^' . $old_module . '\.(php|xml)$'; 
			$files = JFolder::files( $path, $filter );
			
			foreach( $files AS $file )
			{
				$newFile = str_replace( $old_module, $new_module, $file );
				@rename( $path . $file, $path . $newFile );
			}
			
			$newPath = JPath::clean( JPATH_SITE . '/modules/' . $new_module );
			@rename( $path, $newPath );
		}
		
		// change plugin folder ane to lowe case in templates
		$templates = self::getTempaltes();
		
		if( !empty( $templates ) )
		{
			foreach( $templates as $template )
			{
				$path = JPath::clean( JPATH_SITE . '/templates/' . $template->element . '/html/' . $old_module );
				
				// change plugin folder name to lower case
				if( is_dir( $path ) )
				{
					$newPath = JPath::clean( JPATH_SITE . '/templates/' . $template->element . '/html/' . $new_module );
					@rename( $path, $newPath );
				}
			}
		}
		
	}
	
	protected static function getTempaltes()
	{
		static $templates;
		
		if( is_null( $templates ) )
		{
			$db = JFactory::getDbo();
			// Build the query.
			$query = $db->getQuery( true );
			$query->select( array(
				       $db->quoteName( 'element' ),
				       $db->quoteName( 'name' )
				) )
				->from( $db->quoteName( '#__extensions', 'e' ) )
				->where( $db->quoteName( 'e.client_id' ) . ' = ' . 0 )
				->where( $db->quoteName( 'e.type' ) . ' = ' . $db->quote( 'template' ) )
				->where( $db->quoteName( 'e.enabled' ) . ' = 1');
			
			$templates = (array) $db->setQuery($query)->loadObjectList( 'element' );
		}
		
		return $templates;
	}
}