<?php
/**
 * @version     1.0.0
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access
defined('_JEXEC') or die;

// Load the Array Helper
JLoader::import( 'helpers.fieldsandfilters.cachehelper', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );

/**
 * FieldsandfiltersPluginExtensionsHelper
 *
 * @package     com_fieldsandfilters
 * @since       1.0.0
 */

class FieldsandfiltersPluginExtensionsHelper extends FieldsandfiltersCacheHelper
{
        protected $_not 		= array();
        protected $_group 		= null;
	protected $_plugins_folder 	= 'fieldsandfiltersExtensions';
        protected $_extension_default   = 'allextensions';
        
	public function getExtensions( $withXML = false )
	{
                if( !property_exists( $this->_data, $this->_plugins_folder ) )
                {
			
                        $data           = $this->_getData( $this->_plugins_folder );
                        $elements       = $data->elements;
                        
                        $data->set( 'xml', (boolean) $withXML );
                        
                        $pluginsExtensions = $this->_db->setQuery( $this->__getQuery() )->loadObjectList();
                        
			if( !empty( $pluginsExtensions ) )
			{
				if( $data->xml )
				{
					// Load the XML Helper
					JLoader::import( 'helpers.fieldsandfilters.xmlhelper', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
				}
				
				while( $pluginExtension = array_shift( $pluginsExtensions ) )
				{
					if( $pluginExtension->name == $this->_extension_default )
					{
						$pluginExtension->type             = 'com_fieldsandfilters';
						$pluginExtension->file_path        = JPATH_ADMINISTRATOR . '/components/' . $pluginExtension->type . '/models/forms/' . $pluginExtension->name . '.xml';
						$pluginExtension->params           = '{}';
					}
					
					
					$elements->set( $pluginExtension->name, $pluginExtension );
					
					if( $data->xml )
					{
						FieldsandfiltersXMLHelper::getOptionsFromXML( $elements->get( $pluginExtension->name ), $this->_config );
					}  
				}
			}
                }
                elseif( $withXML && !$this->_getData( $this->_plugins_folder )->xml )
                {
                        $data = $this->_getData( $this->_plugins_folder );
                        
                        $data->set( 'xml', (boolean) $withXML );
                        
			if( $data->xml )
			{
				// Load the XML Helper
				JLoader::import( 'helpers.fieldsandfilters.xmlhelper', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
			}
			
			$elements = get_object_vars( $data->elements );
                        
                        while( $element = array_shift( $elements ) )
                        {
                                FieldsandfiltersXMLHelper::getOptionsFromXML( $element, $this->_config );
                        }
                }
                
                return $this->_getData( $this->_plugins_folder )->elements;
	}
	
	public function getExtensionsGroup()
        {
                if( is_null( $this->_group ) )
                {
                        $plugins        = get_object_vars( $this->getExtensions( true ) );
                        
                        $this->_group 	= new JRegistry;
                        
                        while( $plugin = array_shift( $plugins ) )
                        {
                                if( empty( $plugin->group['type'] ) || empty( $plugin->name ) )
                                {
                                        continue;
                                }
                                
                                $this->_group->set( ( $plugin->group['type'] . '.' . $plugin->name ), $plugin );
                        }
                }
                
                return $this->_group; 
        }
        
        public function getExtensionsPivot( $pivot, $withXML = false )
        {
		return $this->_getCachePivot( $pivot, $withXML, null, null, 'getExtensions' );
        }
	
	public function getExtensionsColumn( $column, $withXML = false )
	{
		return $this->_getCacheColumn( $column, $withXML, null, null, 'getExtensions' );
	}
	
	protected function __getQuery()
	{
		// Get db and query
		$query = $this->_db->getQuery( true );
			
		$query->select( array(
                                        $this->_db->quoteName( 'et.extension_type_id' ),
                                        $this->_db->quoteName( 'e.folder', 'type' ),
                                        $this->_db->quoteName( 'et.extension_name', 'name' ),
					$this->_db->quoteName( 'e.params' )
                        ) )
			->from( $this->_db->quoteName( '#__fieldsandfilters_extensions_type', 'et' ) )
                        ->join( 'LEFT', $this->_db->quoteName( '#__extensions', 'e' ) . ' ON ' . $this->_db->quoteName( 'e.element' ) . ' = ' . $this->_db->quoteName( 'et.extension_name' ) )
                        ->where( $this->_db->quoteName( 'et.extension_name' ) . ' = ' . $this->_db->quote( $this->_extension_default ), 'OR' ); 
                        
                $where = array(
                               $this->_db->quoteName( 'e.type' ) . ' = ' . $this->_db->quote( 'plugin' ),                       // Extension mast by a plugin
                               $this->_db->quoteName( 'e.folder' ) . ' = ' . $this->_db->quote( $this->_plugins_folder ),       // Extension where plugin folder
                               $this->_db->quoteName( 'e.enabled' ) . ' = 1'                                                    // Extension where enabled
                        );
                        
                $query->where( '(' . implode( ' AND ', $where ) . ')' );
		
		$query->order( $this->_db->quoteName( 'ordering' ) . ' ASC' );
                        
		return $query;
	}
        
        public function getExtensionsByID( $ids, $withXML = false )
        {
                $this->_vars->elementName               = 'extension_type_id';
                $this->_config['elementsString']        = false;
                
                return $this->_getExtensionsBy( $ids, $withXML );     
        }
	
	public function getExtensionsByIDPivot( $pivot, $ids, $withXML = false )
        {
		return $this->_getCachePivot( $pivot, $ids, $withXML, null, 'getExtensionsByID' );
        }
	
	public function getExtensionsByIDColumn( $column, $ids, $withXML = false )
	{
		return $this->_getCacheColumn( $column, $ids, $withXML, null, 'getExtensionsByID' );
	}
        
        public function getExtensionsByName( $names, $withXML = false )
        {
                $this->_vars->elementName               = 'name';
                $this->_config['elementsString']        = true;
                
                return $this->_getExtensionsBy( $names, $withXML );   
        }
	
	public function getExtensionsByNamePivot( $pivot, $names, $withXML = false )
        {
		return $this->_getCachePivot( $pivot, $names, $withXML, null, 'getExtensionsByName' );
        }
	
	public function getExtensionsByNameColumn( $column, $names, $withXML = false )
	{
		return $this->_getCacheColumn( $column, $names, $withXML, null, 'getExtensionsByName' );
	}
        
        protected function _getExtensionsBy( $elements, $withXML = false )
        {
                $elements = array_unique( (array) $elements );
                
                if( !$this->_config['elementsString'] )
                {
                        JArrayHelper::toInteger( $elements );
                }
                
                if( !empty( $elements ) )
                {
                        $extensions = get_object_vars( $this->getExtensions( $withXML ) );
                        
                        while( $extension = array_shift( $extensions ) )
                        {
                                if( in_array( $extension->{$this->_vars->elementName}, $elements ) )
                                {
                                        $this->_cache->set( $extension->name, $extension );
                                }
                        }
                }
                
                return $this->_returnCache( true ); 
        }
}