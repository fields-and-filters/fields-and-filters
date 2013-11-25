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
 
/**
 * Script file of FieldsAndFilters Installer component
 */
class com_fieldsandfiltersInstallerScript
{
	protected $adapter;
	protected $type;
	
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
         * Method to install the extension
         * $adapter is the class calling this method
         *
         * @return void
         */
	/*
        public function install( $adapter ) 
        {
		$this->_checkTypeContent();
                return $this->_addExtensions( $adapter );
        }
	*/
	/**
         * Method to update the extension
         * $adapter is the class calling this method
         *
         * @return void
         */
	/*
        public function update( $adapter ) 
        {
		$this->_checkTypeContent();
		return $this->_addExtensions( $adapter );
        }
	*/
	
	
	
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
				// Extension uninstalled sucessfully
				$msg = JText::sprintf( 'COM_FIELDSANDFILTERS_ERROR_NOT_UNINSTALL_EXTENSION', $extension );
				$app->enqueueMessage( $msg, 'error' );
			}
		}
		
		return true;
        }
	
	 /**
         * Method to run before an install/update/uninstall method
         * $adapter is the class calling this method
         * $type is the type of change (install, update or discover_install)
         *
         * @return void
         */
        function preflight( $type, $adapter ) 
        {
		$this->adapter = $adapter;
		$this->type = $type;
		
		if( $type == 'update' && version_compare( $this->_getOldVersion(), 1.2, '<' ) )
		{
			self::_changePlugins('fieldsandfiltersExtensions');
			self::_changePlugins('fieldsandfiltersTypes');
		}
	}
	
	
	
	/**
         * Method to run after an install/update/uninstall method
         * $adapter is the class calling this method
         * $type is the type of change (install, update or discover_install)
         *
         * @return void
         */
        function postflight($type, $adapter) 
        {
		$this->adapter = $adapter;
		$this->type = $type;
		
		if( $type == 'install' || ( $type == 'update' && version_compare( $this->_getOldVersion(), 1.2, '<' ) ) )
		{
			$this->_checkContentType();
			return $this->_addExtensions();
		}
		
		return true;
        }
	
	protected function _getVersion()
	{
		static $version;
		
		if( is_null( $version ) && $this->adapter instanceof JAdapterInstance )
		{
			$version = (float) $this->adapter->getParent()->manifest['version'];
		}
		
		return $version;
	}
	
	protected function _getOldVersion()
	{
		static $oldVersion;
		
		if( is_null( $oldVersion ) && $this->adapter instanceof JAdapterInstance )
		{
			// load problem
			$table = JTable::getInstance('extension');
			
			$version = $this->_getVersion();
			if( $table->load( array( 'element' => $this->adapter->get('element'), 'type' => 'component' ) ) )
			{
				$manifestCache = new JRegistry( $table->manifest_cache );
				$version = ( $manifestCache->get('version') ) ? (float) $manifestCache->get('version') : $version;
			}
			
			$oldVersion = $version;
		}
		
		return $oldVersion;
	}
	
	protected function _addExtensions()
	{
		// Get an installer instance
		$app	        = JFactory::getApplication();
		$parent		= $this->adapter->getParent();
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
		$this->adapter->loadLanguage( $adminPath );
		
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
	
	protected function _checkContentType()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select( $db->quoteName( 'type_alias' ) );
		$query->from( $db->quoteName( '#__content_types' ) );
		$query->where( $db->quoteName( 'type_alias' ) . '=' . $db->quote( 'com_fieldsandfilters.field' ) );
                
		if( !$db->setQuery( $query )->loadResult() )
		{
			$columns 	= $db->getTableColumns( '#__content_types' );
			$contentType 	= array_intersect_key( self::_prepareContentType(), $columns );
			
                        $query->clear();
			$query->insert( $db->quoteName( '#__content_types' ) );
			$query->columns( $db->quoteName( array_keys( $contentType ) ) );
			$query->values( implode( ', ', $db->quote( array_values( $contentType ), false ) ) );
			$db->setQuery( $query );
			$db->execute();
		}
		
		if( $this->type == 'update' && version_compare( $this->_getOldVersion(), 1.2, '<' ) ) // [TODO] version
		{
			self::_updateContentType( 'allextensions', 'com_fieldsandfilters.field' );
			// $this->updateContentType( 'content', 'com_content.article' ); <- [TODO] to musi isc do pluginu, ponieważ jeszcze nie mamy dodanej content type
		}
	}
	
	protected static function _prepareContentType()
	{
		$contentType = new stdClass();
		$contentType->type_title = 'Fieldsandfilters Field';
		$contentType->type_alias = 'com_fieldsandfilters.field';
		$contentType->table = json_encode(
			array(
				'special' => array(
					'dbtable' => '#__fieldsandfilters_fields',
					'key'     => 'field_id',
					'type'    => 'Field',
					'prefix'  => 'FieldsandfiltersTable',
					'config'  => 'array()'
				),
				'common' => array()
			)
		);
		
		$contentType->rules = '';
		
		$contentType->field_mappings = json_encode(
			array(
				'common' => array(
					'core_content_item_id'	=> 'field_id',
					'core_title'		=> 'field_name',
					'core_state'		=> 'state',
					'core_alias'		=> 'field_alias',
					'core_created_time'	=> 'null', // null
					'core_modified_time'	=> 'null', // null
					'core_body'		=> 'description',
					'core_hits'		=> 'null', // null
					'core_publish_up'	=> 'null', // null
					'core_publish_down'	=> 'null', // null
					'core_access'		=> 'access',
					'core_params'		=> 'params',
					'core_featured'		=> 'null', // null
					'core_metadata'		=> 'null', // null
					'core_language'		=> 'language',
					'core_images'		=> 'null', // null
					'core_urls'		=> 'null', // null
					'core_version'		=> 'null', // null
					'core_ordering'		=> 'ordering',
					'core_metakey'		=> 'null', // null
					'core_metadesc'		=> 'null', // null
					'core_catid'		=> 'null', // null
					'core_xreference'	=> 'null', // null
					'asset_id'		=> 'null' // null
				),
				'special' => array(
					'field_type'		=> 'field_type',
					'content_type_id'	=> 'content_type_id',
					'mode'			=> 'mode',
					'required'		=> 'required'
				)
			)
		);
		
		$contentType->router = '';
		
		$contentType->content_history_options = json_encode(
			array(
				'formFile' 		=> 'administrator/components/com_fieldsandfilters/models/forms/field.xml',
				'hideFields' 		=> array( 'mode' ),
				'ignoreChanges' 	=> array(),
				'convertToInt'		=> array( 'content_type_id', 'mode', 'ordering', 'state', 'required' ),
				'displayLookup'		=> array(
					array(
						'sourceColumn'		=> 'content_type_id',
						'targetTable'		=> '#__content_types',
						'targetColumn'		=> 'type_id',
						'displayColumn'		=> 'type_title'
					)
				)
				
				
				
			)
		);
		
		return (array) $contentType;
	}
	
	protected function _updateContentType( $extensionTypeName, $contentTypeName )
	{
		$db = JFactory::getDbo();	
		$query = $db->getQuery( true );
		
		// get Content Type ID
		$query->select( $db->quoteName( 'type_id' ) );
		$query->from( $db->quoteName( '#__content_types' ) );
		$query->where( $db->quoteName( 'type_alias' ) . ' = ' . $db->quote( $contentTypeName ) );
		
		$contentTypeID = (int) $db->setQuery( $query )->loadResult();
		
		$query->clear();
		
		if( !$contentTypeID )
		{
			return;
		}
		
		// get Extension Type ID
		$query->clear();
		$query->select( $db->quoteName( 'extension_type_id' ) );
		$query->from( $db->quoteName( '#__fieldsandfilters_extensions_type' ) );
		$query->where( $db->quoteName( 'extension_name' ) . ' = ' . $db->quote( $extensionTypeName ) );
		
		$extensionTypeID = (int) $db->setQuery( $query )->loadResult();
		
		$query->clear();
		
		if( !$extensionTypeID )
		{
			return;
		}
		
		// update old extension type id
		$query->update( array(
			$db->quoteName( '#__fieldsandfilters_connections' ),
			$db->quoteName( '#__fieldsandfilters_data' ),
			$db->quoteName( '#__fieldsandfilters_elements' ),
			$db->quoteName( '#__fieldsandfilters_fields' )
		) );
		$query->set( 'content_type_id = ' . (int) $contentTypeID );
		$query->where( 'content_type_id = ' . (int) $extensionTypeID );
		
		$db->setQuery( $query )->execute();
	}
	
	protected static function _changePlugins( $folder )
	{
		jimport('joomla.filesystem.folder');
		
		$db = JFactory::getDbo();
		$query = $db->getQuery( true );
		$query->select( '*' );
		$query->from( $db->quoteName( '#__extensions' ) );
		$query->where( $db->quoteName( 'folder' ) . ' = ' . $db->quote( $folder ) );
		$query->where( $db->quoteName( 'type' ) . ' = ' . $db->quote( 'plugin' ) );
		
		$plugins = $db->setQuery( $query )->loadObjectList();
		
		while( $plugin = array_shift( $plugins ) )
		{
			$plugin->folder = strtolower( $plugin->folder );
			$plugin->name = 'plg_' . $plugin->folder . '_' . $plugin->element;
			
			$db->updateObject( '#__extensions', $plugin, 'extension_id' );
		}
		
		$langs = JLanguage::getKnownLanguages(JPATH_ADMINISTRATOR);
		
		foreach( $langs AS $lang )
		{
			$path = JPATH_ADMINISTRATOR . '/language/' . $lang['tag'] . '/';
			$filter = '^' . $lang['tag'] . '.plg_' . $folder . '_(.*).ini$'; 
			$files = JFolder::files( $path, $filter );
			
			foreach( $files AS $file )
			{
				$newFile = strtolower( $file );
				rename( $path . $file, $path . $newFile );
			}
		}
		
		$path = JPATH_PLUGINS . '/' . $folder;
		if( is_dir( $path ) )
		{
			$newPath = JPATH_PLUGINS . '/' . strtolower( $folder );
			rename( $path, $newPath );
		}
		
	}
}