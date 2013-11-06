<?php
/**
 * @version     1.1.1
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access
defined('_JEXEC') or die;

/**
 * FieldsandfiltersTypes
 *
 * @package     com_fieldsandfilters
 * @since       1.0.0
 */
class FieldsandfiltersTypes extends KextensionsBufferCore
{
	protected $_plugins_folder 	= 'fieldsandfiltersTypes';
	
	/**
	 * Constructor
	 * 
	 * @since       1.0.0
	 */
	public function __construct( $debug = null )
	{
		parent::__construct( $debug );
		
		$this->_getData( 'modes' )->elements->setProperties(
			array(
				'filter' => array(
							'single' 	=> 1,
							'multi' 	=> 2
				),
				'field'	=> array(
							'text' 		=> -1,
							'json' 		=> -2
				),
				'static' => array(
							'text'	 	=> -6,
							'json' 		=> -7
				)
			)
		);
	}
        
	/**
	 * @since       1.1.0
	**/
	public function getTypes( $withXML = false )
	{
                if( !property_exists( $this->_data, $this->_plugins_folder ) )
                {
                        $data           = $this->_getData( $this->_plugins_folder );
                        $elements       = $data->elements;
                        
                        $data->set( 'xml', (boolean) $withXML );
                        
                        $pluginsTypes = $this->_db->setQuery( $this->_getQuery() )->loadObjectList();
			
			if( !empty( $pluginsTypes ) )
			{
				while( $pluginType = array_shift( $pluginsTypes ) )
				{
					$elements->set( $pluginType->name, $pluginType );
					
					if( $data->xml )
					{
						FieldsandfiltersXML::getPluginOptionsForms( $elements->get( $pluginType->name ), array() );
					}  
				}
			}
                }
                elseif( $withXML && !$this->_getData( $this->_plugins_folder )->xml )
                {
                        $data = $this->_getData( $this->_plugins_folder );
                        
                        $data->set( 'xml', (boolean) $withXML );
                        
                        $elements = get_object_vars( $data->elements );
                        
                        while( $element = array_shift( $elements ) )
                        { 
                                FieldsandfiltersXML::getPluginOptionsForms( $element, array() );
                        }
                }
		
                return $this->_getData( $this->_plugins_folder )->elements;
	}
	
	/**
	 * @since       1.1.0
	**/
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
                                        $this->buffer->set( $type->name, $type );
                                }
                        }
                }
                
                return $this->_returnBuffer( true ); 
	}
	
	/**
	 * @since       1.1.0
	**/
	public function getTypesGroup()
        {
		static $group;
		
                if( is_null( $group ) )
                {
			$group 		= new JRegistry;
                        $plugins        = get_object_vars( $this->getTypes( true ) );
			
                        while( $plugin = array_shift( $plugins ) )
                        {
				if( isset( $plugin->forms ) )
				{
					$forms = $plugin->forms->getProperties();
					while( $form = array_shift( $forms ) )
					{
						$group->set( ( $form->group->name . '.' . $plugin->name ), $plugin );
					}
				}
                        }
                }
                
                return $group; 
        }
	
	/**
	 * Get a mode type value.
	 *
	 * @param   string  $path     Mode path ( e.g. values.single )
	 * @param   mixed   $default  Optional default value, returned if the internal value is null.
	 *
	 * @return  mixed  Value of entry or null
	 *
	 * @since       1.0.0
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
	 * @param   string  $paths     		Array mode paths ( e.g. array( values.single, values.multi )
	 * @param   mixed   $default  		Optional default value, returned if the internal value is null.
	 * @param   boolean   $pathKey  	Keys of array is the name of modes
	 * @param   boolean   $flatten  	Flatten array
	 * @param   array     $excluded		Excluded items array
	 *
	 * @return  mixed  Value of entry or null
	 *
	 * @since       1.1.0
	 */
	public function getModes( $paths = null, $default = array(), $flatten = false, $excluded = false, $pathKey = false )
	{
		$modes 		= array();
		$isExcluded	= ( $excluded && is_array( $excluded ) );
		
		if( is_null( $paths ) )
		{
			$modes = $this->_getData( 'modes' )->elements->getProperties();
		}
		else if( is_array( $paths ) )
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
		else if( is_string( $paths ) )
		{
			$modes = (array) $this->getMode( $paths );
		}
		
		if( !empty( $modes ) )
		{
			if( $flatten || $isExcluded )
			{
				$modes = KextensionsArray::getArray()->flatten( $modes );
			}
			
			if( $isExcluded )
			{
				$modes = array_diff( $modes, $excluded );
			}
		}
		else
		{
			$modes = $default;
		}
		
		return $modes;
	}
	
	/**
	 * @since       1.0.0
	**/
	public function getModeName( $id, $name = 'type', $default = null )
	{
		if( $id = (int) $id )
		{
			$modes = $this->_getData( 'modes' )->elements->getProperties();
			
			foreach( $modes AS $typeName => &$mode )
			{
				if( $modeName = array_search( $id, $mode ) )
				{
					switch( $name )
					{
						case 1:
						case 'type':
							return $typeName;
						break;
						case 2:
						case 'mode':
							return $modeName;
						break;
						case 3:
						case 'path':
							return ( $typeName . '.' . $modeName );
						break;
					}
					
					break;
				}
			}
		}
		
		return $default;
	}
	
	/**
	 * @since       1.0.0
	**/
	protected function _getQuery()
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
	
	/** __call method generator methods:
	 * getTypesPivot( $pivot, $withXML = false )
	 * getTypesColumn( $column, $withXML = false )
	 *
	 * getTypesByNamePivot( $pivot, $names, $withXML = false )
	 * getTypesByNameColumn( $column, $names, $withXML = false )
	 *
	 * @since       1.1.0
	**/
}