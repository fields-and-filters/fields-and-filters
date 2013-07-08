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

// Load the BufferCore Helper
JLoader::import( 'fieldsandfilters.buffer.core', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/helpers' );

/**
 * FieldsandfiltersPluginExtensionsHelper
 *
 * @package     com_fieldsandfilters
 * @since       1.1.0
 */

class FieldsandfiltersPluginExtensionsHelper extends FieldsandfiltersBufferCoreHelper
{
	protected $_plugins_folder 	= 'fieldsandfiltersExtensions';
        protected $_extension_default   = 'allextensions';
        
	/**
	 * Constructor
	 * 
	 * @since       1.0.0
	 */
	public function __construct( $debug = null )
	{
		parent::__construct( $debug );
		
		if( FieldsandfiltersFactory::isVersion() )
		{
			$this->_dispatcher = JEventDispatcher::getInstance();
		}
		else
		{
			$this->_dispatcher = JDispatcher::getInstance();
		}
	
	}
	
	/**
	 * @since       1.1.0
	**/
	public function getExtensions( $withXML = false )
	{
                if( !property_exists( $this->_data, $this->_plugins_folder ) )
                {
                        $data           = $this->_getData( $this->_plugins_folder );
                        $elements       = $data->elements;
                        
                        $data->set( 'xml', (boolean) $withXML );
                        
                        $pluginsExtensions = $this->_db->setQuery( $this->_getQuery() )->loadObjectList();
			
			if( !empty( $pluginsExtensions ) )
			{
				while( $pluginExtension = array_shift( $pluginsExtensions ) )
				{
					if( $pluginExtension->name == $this->_extension_default )
					{
						$pluginExtension->type             = 'com_fieldsandfilters';
						$pluginExtension->forms_dir        = JPATH_ADMINISTRATOR . '/components/' . $pluginExtension->type . '/models/forms/allextensions';
						$pluginExtension->params           = '{}';
					}
					
					
					$elements->set( $pluginExtension->name, $pluginExtension );
					
					if( $data->xml )
					{
						FieldsandfiltersFactory::getXML()->getPluginOptionsForms( $elements->get( $pluginExtension->name ), array() );
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
                                FieldsandfiltersFactory::getXML()->getPluginOptionsForms( $element, array() );
                        }
                }
                
                return $this->_getData( $this->_plugins_folder )->elements;
	}
	
	/**
	 * @since       1.1.0
	**/
        public function getExtensionsByID( $ids, $withXML = false )
        {
                $this->vars->elementName = 'extension_type_id';
                
                return $this->_getExtensionsBy( $ids, $withXML );     
        }
        
	/**
	 * @since       1.1.0
	**/
        public function getExtensionsByName( $names, $withXML = false )
        {
                $this->vars->elementName = 'name';
                $this->config->def( 'elementsString', true );
                
                return $this->_getExtensionsBy( $names, $withXML );   
        }
	
	/**
	 * @since       1.1.0
	**/
	public function getExtensionsGroup()
        {
		static $group;
		
                if( is_null( $group ) )
                {
			$group 		= new JRegistry;
                        $plugins	= get_object_vars( $this->getExtensions( true ) );
                        
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
	 * @since       1.1.0
	**/
	public function getExtensionsNameByOption( $option = null, $default = null )
	{
		if( !property_exists( $this->_data, 'options' ) )
                {
                        $data           = $this->_getData( 'options' );
			
			JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
			
			// Trigger the onFieldsandfiltersPrepareFormField event.
			$options = $this->_dispatcher->trigger( 'onFieldsandfiltersPreparePluginExtensionsHelper', array( 'pluginextensions.options', $data->elements ) );			
		}
		
		if( $option )
		{
			return $this->_getData( 'options' )->elements->get( $option, $default );
		}
		
		return $this->_getData( 'options' )->elements;
	}
	
	/**
	 * @since       1.0.0
	**/
	protected function _getQuery()
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
	
	/**
	 * @since       1.1.0
	**/
	protected function _getExtensionsBy( $elements, $withXML = false )
        {
                $elements = array_unique( (array) $elements );
                
                if( !$this->config->def( 'elementsString' ) )
                {
                        JArrayHelper::toInteger( $elements );
                }
                
                if( !empty( $elements ) )
                {
                        $extensions = get_object_vars( $this->getExtensions( $withXML ) );
                        
                        while( $extension = array_shift( $extensions ) )
                        {
                                if( in_array( $extension->{$this->vars->elementName}, $elements ) )
                                {
                                        $this->buffer->set( $extension->name, $extension );
                                }
                        }
                }
                
                return $this->_returnBuffer( true ); 
        }
	
	
	/** __call method generator methods:
	 * getExtensionsPivot( $pivot, $withXML = false )
	 * getExtensionsColumn( $column, $withXML = false )
	 *
	 * getExtensionsByIDPivot( $pivot, $ids, $withXML = false )
	 * getExtensionsByIDColumn( $column, $ids, $withXML = false )
	 *
	 * getExtensionsByNamePivot( $pivot, $names, $withXML = false )
	 * getExtensionsByNameColumn( $column, $names, $withXML = false )
	 *
	 * @since       1.1.0
	**/
}