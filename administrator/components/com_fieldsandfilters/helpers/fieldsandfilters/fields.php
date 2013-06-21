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

// Load pluginTypes Helper
JLoader::import( 'helpers.fieldsandfilters.plugintypes', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );

/**
 * FieldsandfiltersFieldsHelper
 *
 * @package     com_fieldsandfilters
 * @since       1.0.0
 */
class FieldsandfiltersFieldsHelper extends FieldsandfiltersCacheHelper
{
	/**
         * An array of names that don't exists
         * 
	 * @var    array 
	 * @since  1.00
	 */
	protected $_not = array( '__modes', 'fields' => '__notFields' );
	
	protected $_extension_locatnion_default = 'onBeforeRender';
	
	protected $_valuesModes = array();
	
	public function __construct( $debug = false )
	{
		parent::__construct( $debug );
		
		$this->_valuesModes = FieldsandfilterspluginTypesHelper::getInstance()->getMode( 'values' );
	}
/**
 * Get Fields item method
 **/
	public function getFields( $types, $states = null, $values = null, $without = true )
	{
		$this->_setConfigFields( $values, $without );
		
		return parent::_getCache( $types, null, $states );
	}
	
	protected function _setConfigFields( $values = false, $without = true )
	{
		$this->_config['typeName'] = 'extension_type_id';
		$this->_config['elementName'] = 'field_id';
		
		if( !$without )
		{
			$this->_config['elemntsWithoutValues'] = $without;
		}
		
		if( $values )
		{
			$this->_config['valuesName'] 		= 'values';
			$this->_config['getCacheValues'] 	= '_getCacheValues';
			$this->_config['getQueryValues'] 	= '_getQueryFieldValues';
			$this->_config['addValue']		= '_addFieldValue';
		}
	}
	
	public function getFieldsPivot( $pivot, $types, $states = null, $values = null, $without = true )
	{
		$this->_setConfigFields( $values, $without );
		
		return $this->_getCachePivot( $pivot, $types, null, $states, '_getCache' );
	}
	
	public function getFieldsColumn( $column, $types, $states = null )
	{
		$this->_setConfigFields();
		
		return $this->_getCacheColumn( $column, $types, null, $states, '_getCache' );
	}
	
	protected function __getQuery()
	{
		// Get db and query
		$query = $this->_db->getQuery( true );
			
		$query->select( '*' )
			->from( $this->_db->quoteName( '#__fieldsandfilters_fields' ) )
			->where( $this->_db->quoteName( 'state' ) . ' IN (' . implode( ',', $this->_vars->states ) . ')' )		// Fiels where states
			->where( $this->_db->quoteName( 'extension_type_id' ) . ' IN(' . implode( ',', $this->_vars->types ) . ')' ); 	// Fields where extensions type id
		
		// We no need same elements id
		if( !empty( $this->_vars->notElements ) )
		{
			JArrayHelper::toInteger( $this->_vars->notElements  );
			$query->where( $this->_db->quoteName( 'field_id' ) . ' NOT IN (' . implode( ',', $this->_vars->notElements  ) . ')' );
		}
		
		$query->order( $this->_db->quoteName( 'ordering' ) . ' ASC' );
		
		return $query;
	}
	
	protected function __setData( &$_field )
        {
		$this->_getData( $_field->extension_type_id )->elements->set( $_field->field_id, $_field );
		
		$_field->params = new JRegistry( $_field->params );
		
		$_field->location = $_field->params->get( 'extension.location', $this->_extension_locatnion_default );
		
		$this->_cache->{$_field->field_id} = $_field;
        }
	
	protected function _getQueryFieldValues()
	{
		$query = $this->_db->getQuery( true );
		
		$query->select( '*' )
			->from( $this->_db->quoteName( '#__fieldsandfilters_field_values' ) )
			->where( $this->_db->quoteName( 'field_id' ) . ' IN (' . implode( ',', $this->_vars->values->elements ) . ')' )
			->where( $this->_db->quoteName( 'state' ) . ' = 1' );
			
		$query->order( $this->_db->quoteName( 'ordering' ) . ' ASC' );
		
		return $query;
	}
	
	protected function __searchValuesElement( &$element )
        {
                $elementName    = $this->_vars->elementName;
                $_vars          = $this->_vars->values;
                // if element don't have filter_connection, add to arrry
                if( in_array( $element->mode, $this->_valuesModes ) && !isset( $element->{$this->_vars->valuesName} ) )
                {
                        array_push( $_vars->elements, $element->$elementName );
                }
                // if we need only elements with filter_connections isn't empty
                elseif( !$this->_config['elemntsWithoutValues'] && isset( $element->{$this->_vars->valuesName} ) )
                {
                        $_values = get_object_vars( $element->{$this->_vars->valuesName} );
                        
                        if( empty( $_values ) )
                        {
                                unset( $this->_cache->{$element->$elementName} );
                        }
                        
                        unset( $_values );
                }
        }
	
	protected function _addFieldValue( &$_value )
	{
		$element = $this->_cache->get( $_value->{$this->_vars->elementName} );
		
		$valuesName = $this->_vars->valuesName;
		
		if( !( isset( $element->$valuesName ) && $element->$valuesName instanceof JObject ) )
		{
		     $element->$valuesName = new JObject();
		}
		
		if( isset( $_value->field_value_id ) )
		{
			unset( $_value->field_id );
			
			$element->$valuesName->set( $_value->field_value_id, $_value );
		}
	}
	
/**
 * @end Get Fields method
 **/

/**
 * Get Fields by ID method
 **/
	
	public function getFieldsByID( $types, $ids, $states = null , $values = null, $without = true )
	{
		$this->_config['notName'] 		= 'fields';
		$this->_config['beforeQuery'] 		= '__beforeQueryElements';
		$this->_config['preparationVars'] 	= '_preparationVarsByID';
		$this->_config['testQueryVars'] 	= '_testQueryVarsByID';
		$this->_config['getQuery'] 		= '_getQueryByID';
		$this->_config['setData'] 		= '_setDataByID';
		$this->_config['afterQuery'] 		= '_afterQueryByID';
		
		$this->_setConfigFields( $values, $without );
		
		return parent::_getCache( $types, $ids, $states );
	}
	
	public function getFieldsByIDPivot( $pivot, $types, $ids, $states = null, $values = null, $without = true )
	{
		$this->_setConfigFields( $values, $without );
		
		return $this->_getCachePivot( $pivot, $types, $ids, $states, 'getFieldsByID' );
	}
	
	public function getFieldsByIDColumn( $column, $types, $ids, $states = null )
	{
		$this->_setConfigFields();
		
		return $this->_getCacheColumn( $column, $types, $ids, $states, 'getFieldsByID' );
	}
	
	protected function _preparationVarsByID()
        {
                // We take elements from cache, when they aren't in the cache, we add they to query variables
		$this->_vars->fieldsID = array();
		
		return parent::__preparationVars();
        }
	
	protected function _testQueryVarsByID()
        {
                return ( !empty( $this->_vars->types ) && !empty( $this->_elements ) );
        }
	
	protected function _getQueryByID()
	{
		// Get db and query
		$query = $this->_db->getQuery( true );
			
		$query->select( '*' )
			->from( $this->_db->quoteName( '#__fieldsandfilters_fields' ) )
			->where( $this->_db->quoteName( 'state' ) . ' IN (' . implode( ',', $this->_vars->states ) . ')' )			// Elements where states
			->where( $this->_db->quoteName( 'extension_type_id' ) . ' IN(' . implode( ',', $this->_vars->types ) . ')' )		// Elements where extensions type id
			->where( $this->_db->quoteName( 'field_id' ) . ' IN (' . implode( ',', $this->_elements ) . ')' );			// Elements where elements id
		
		$query->order( $this->_db->quoteName( 'ordering' ) . ' ASC' );
		
		return $query;
	}

	protected function _setDataByID( &$_field )
        {
                $this->__setData( $_field );
		
		array_push( $this->_vars->fieldsID, $_field->field_id );
        }
	
	protected function _afterQueryByID()
        {		
		if( $this->_testQueryVarsByID() )
		{
			$this->_setNot( array_diff( $this->_elements, $this->_vars->fieldsID ), 'fields' );
		}
        }
	
/**
 * @end Get Fields by ID method
 **/


/**
 * Get Fields by Mode ID method
 **/
	
	public function getFieldsByModeID( $types, $ids, $states = null, $values = null, $without = true )
	{
		$this->_config['beforeQuery'] 		= '_beforeQueryByModeID';
		$this->_config['preparationVars'] 	= '_preparationVarsByModeID';
		$this->_config['getQuery'] 		= '_getQueryByModeID';
		$this->_config['setData'] 		= '_setDataByModeID';
		$this->_config['afterQuery'] 		= '_afterQueryModeID';
		
		$this->_setConfigFields( $values, $without );
		
		return parent::_getCache( $types, $ids, $states );
	}
	
	
	public function getFieldsByModeIDPivot( $pivot, $types, $ids, $states = null, $values = null, $without = true )
	{
		$this->_setConfigFields( $values, $without );
		
		return $this->_getCachePivot( $pivot, $types, $ids, $states, 'getFieldsByModeID' );
	}
	
	public function getFieldsByModeIDColumn( $column, $types, $ids, $states = null )
	{
		$this->_setConfigFields();
		
		return $this->_getCacheColumn( $column, $types, $ids, $states, 'getFieldsByModeID' );
	}
	
	protected function _preparationVarsByModeID()
        {
                $this->_vars->modes = array();
		
		// We take elements from cache, when they aren't in the cache, we add they to query variables
		return parent::__preparationVars();
        }
	
	protected function _beforeQueryByModeID( $type )
        {
                // Get extension type id from cache
                $data  = $this->_getData( $type );
                
                // The difference states between argument states and cache states
                $dataStates	= $data->get( '__states', array() );
                $_states        = array_diff( $this->_states, $dataStates );
		
		// The diffrence modes between argument modes and cache modes
		$dataModes	= $data->get( '__modes', array() );
		$_modes		= array_diff( $this->_elements, $dataModes );
		
		if( !empty( $_modes ) && !empty( $_states ) )
                {
			// Add difference states to query varible
			$this->_vars->states += $_states;
			
			// Add difference modes to query varible
			$this->_vars->modes += $_modes;
			
			// When the get modes of the need, then add modes to the cache extenion type, because we don't need them next time
			$data->set( '__modes', array_merge( $dataModes, $_modes ) );
			
			// Get elements id from cache, because we don't need get that id's second time from database 
			$this->_vars->notElements = array_merge( $this->_vars->notElements, array_keys( get_object_vars( $data->get( 'elements', new stdClass ) ) ) );
			
			// Add extension type id to query varible
			array_push( $this->_vars->types, $type );
                }
        }
	
	protected function _getQueryByModeID()
	{
		// Get db and query
		$query = $this->_db->getQuery( true );
			
		$query->select( '*' )
			->from( $this->_db->quoteName( '#__fieldsandfilters_fields' ) )
			->where( $this->_db->quoteName( 'state' ) . ' IN (' . implode( ',', $this->_vars->states ) . ')' )			// Fields where states
			->where( $this->_db->quoteName( 'extension_type_id' ) . ' IN(' . implode( ',', $this->_vars->types ) . ')' )		// Fields where extensions type id
			->where( $this->_db->quoteName( 'mode' ) . ' IN (' . implode( ',', $this->_vars->modes ) . ')' );				// Fields where mode id
		
		// We no need same elements id
		if( !empty( $this->_vars->notElements ) )
		{
			JArrayHelper::toInteger( $this->_vars->notElements  );
			$query->where( $this->_db->quoteName( 'field_id' ) . ' NOT IN (' . implode( ',', $this->_vars->notElements  ) . ')' );
		}
		
		$query->order( $this->_db->quoteName( 'ordering' ) . ' ASC' );
		
		return $query;
	}

	protected function _setDataByModeID( &$_field )
        {
                $this->_getData( $_field->extension_type_id )->elements->set( $_field->field_id, $_field );
		
		$_field->params = new JRegistry( $_field->params );
		
		$_field->location = $_field->params->get( 'extension.location', $this->_extension_locatnion_default );
		
		$this->_cache->{$_field->field_id} = $_field;
        }
	
	protected function _afterQueryModeID()
        {
                // Get elements from cahce
		while( !empty( $this->_types ) )
		{
			$_elements = get_object_vars( $this->_getData( array_shift( $this->_types ) )->get( 'elements', new JObject ) ) ;
                        
			// Add only those elements are suitable states
			while( $_element = current( $_elements ) )
			{
				if( in_array( $_element->mode, $this->_elements ) && in_array( $_element->state, $this->_states ) )
				{
					$this->_cache->set( $_element->{$this->_vars->elementName}, $_element );
				}
				
				next( $_elements );
			}
		}
        }
}