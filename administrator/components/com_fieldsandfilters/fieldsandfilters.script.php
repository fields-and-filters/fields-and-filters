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
         * method to install the component
         *
         * @return void
         */
        public function install( $adapter ) 
        {
                return $this->_addExtensions( $adapter );
        }
	
	/**
         * method to update the component
         *
         * @return void
         */
        public function update( $adapter ) 
        {
		return $this->_addExtensions( $adapter );
        }
	
	protected function _addExtensions( $adapter )
	{
		// Get an installer instance
		$app	        = JFactory::getApplication();
		$parent		= $adapter->getParent();
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
		$adapter->loadLanguage( $adminPath );
		
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
 
        /**
         * method to uninstall the component
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
}