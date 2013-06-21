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
 * FieldsandfiltersPluginTypesHelper
 *
 * @package     com_fieldsandfilters
 * @since       1.0.0
 */
class FieldsandfiltersPluginTypesHelper extends FieldsandfiltersCacheHelper
{
        protected $_not 		= array();
        protected $_group 		= null;
	protected $_plugins_folder 	= 'fieldsandfiltersTypes';
	
	/**
	 * Constructor
	 * 
	 * @since   1.00
	 */
	public function __construct( $debug = null )
	{
		parent::__construct( $debug );
		
		$this->_getData( 'modes' )->elements->setProperties(
			array(
				'values' => array(
							'single' 	=> 1,
							'multi' 	=> 2
				),
				'data' 	 => array(
							'text' 		=> -1,
							'json' 		=> -2
				),
				'static' => array(
							'static' 	=> -6,
							'json' 		=> -7
				)
			)
		);
	}
        
	public function getTypes( $withXML = false )
	{
                if( !property_exists( $this->_data, $this->_plugins_folder ) )
                {
                        $data           = $this->_getData( $this->_plugins_folder );
                        $elements       = $data->elements;
                        
                        $data->set( 'xml', (boolean) $withXML );
                        
                        $pluginsTypes = $this->_db->setQuery( $this->__getQuery() )->loadObjectList();
			
			if( !empty( $pluginsTypes ) )
			{
				if( $data->xml )
				{
					// Load the XML Helper
					JLoader::import( 'helpers.fieldsandfilters.xmlhelper', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
				}
				
				while( $pluginType = array_shift( $pluginsTypes ) )
				{
				      $elements->set( $pluginType->name, $pluginType );
					
					if( $data->xml )
					{
						FieldsandfiltersXMLHelper::getOptionsFromXML( $elements->get( $pluginType->name ), $this->_config );
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
	
	public function getTypesGroup()
        {
                if( is_null( $this->_group ) )
                {
                        $plugins        = get_object_vars( $this->getTypes( true ) );
                        
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
	
	public function getTypesPivot( $pivot, $withXML = false )
        {
		return $this->_getCachePivot( $pivot, $withXML, null, null, 'getTypes' );
        }
	
	public function getTypesColumn( $column, $withXML = false )
	{
		return $this->_getCacheColumn( $column, $withXML, null, null, 'getTypes' );
	}
	
	protected function __getQuery()
	{
		// Get db and query
		$query = $this->_db->getQuery( true );
			
		$query->select( array(
                                        $this->_db->quoteName( 'folder', 'type' ),
                                        $this->_db->quoteName( 'element', 'name' ),
					$this->_db->quoteName( 'params' )
                        ) )
			->from( $this->_db->quoteName( '#__extensions' ) )                        
			->where( array(
                               $this->_db->quoteName( 'type' ) . ' = ' . $this->_db->quote( 'plugin' ),                       // Extension mast by a plugin
                               $this->_db->quoteName( 'folder' ) . ' = ' . $this->_db->quote( $this->_plugins_folder ),       // Extension where plugin folder
                               $this->_db->quoteName( 'enabled' ) . ' = 1'                                                    // Extension where enabled
                        ) );
		
		$query->order( $this->_db->quoteName( 'ordering' ) . ' ASC' );
                        
		return $query;
	}
	
	public function getTypesByName( $names, $withXML = false )
	{
		$names = array_unique( (array) $names );
                
                if( !empty( $names ) )
                {
                        $types = get_object_vars( $this->getTypes( $withXML ) );
                        
                        while( $type = array_shift( $types ) )
                        {
                                if( in_array( $type->name, $names ) )
                                {
                                        $this->_cache->set( $type->name, $type );
                                }
                        }
                }
                
                return $this->_returnCache( true ); 
	}
	
	public function getTypesByNamePivot( $pivot, $names, $withXML = false )
        {
		return $this->_getCachePivot( $pivot, $names, $withXML, null, 'getTypesByName' );
        }
	
	public function getTypesByNameColumn( $column, $names, $withXML = false )
	{
		return $this->_getCacheColumn( $column, $names, $withXML, null, 'getTypesByName' );
	}
	
	/**
	 * Get a mode type value.
	 *
	 * @param   string  $type     Type name ( filters/fields )
	 * @param   string  $path     Mode path ( e.g. values.single )
	 * @param   mixed   $default  Optional default value, returned if the internal value is null.
	 *
	 * @return  mixed  Value of entry or null
	 *
	 * @since   2.5
	 */
	public function getMode( $path = null, $default = null )
	{
		if( is_null( $path ) )
		{
			return $this->_getData( 'modes' )->elements;
		}
		elseif( strpos( $path, '.' ) )
		{
			// Explode the mode path into an array
			$nodes = explode( '.', $path, 2 );
			$mode = $this->_getData( 'modes' )->elements->get( $nodes[0], array() );
			
			return ( array_key_exists( $nodes[1], $mode ) ? $mode[$nodes[1]] : $default );
		}
		
		return $this->_getData( 'modes' )->elements->get( $path, $default );
	}
	
	/**
	 * Get a mode type values.
	 *
	 * @param   string  $type     		Type name ( filters/fields )
	 * @param   string  $paths     		Array mode paths ( e.g. array( values.single, values.multi )
	 * @param   mixed   $default  		Optional default value, returned if the internal value is null.
	 *
	 * @return  mixed  Value of entry or null
	 *
	 * @since   2.5
	 */
	public function getModes( $paths, $default = array(), $pathKey = false )
	{
		$modes = array();
		
		if( is_array( $paths ) )
		{
			while( $path = array_shift( $paths ) )
			{
				if( $mode = $this->getMode( $path, false ) )
				{
					if( $pathKey )
					{
						$modes[$path] = $mode;
					}
					else
					{
						$modes[] = $mode;
					}
				}
			}
		}
		
		return ( !empty( $modes ) ? $modes : $default );
	}
}