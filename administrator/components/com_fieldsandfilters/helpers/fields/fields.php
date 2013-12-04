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
 * FieldsandfiltersFieldsHelper
 *
 * @package     com_fieldsandfilters
 * @since       1.1.0
 */
class FieldsandfiltersFields extends KextensionsBufferValues
{
	/**
	 * @since       1.2.0
	 **/
	const VALUES_VALUES = 1;
	
	/**
	 * @since       1.2.0
	 **/
	const VALUES_DATA = 2;
	
	/**
	 * @since       1.2.0
	 **/
	const VALUES_BOTH = 3;
	
	/**
         * An array of names that don't exists
         * 
	 * @var    array 
	 * @since  1.10
	 */
	protected $_not = array( '__modes', 'fields' => '__notFields' );
	
	/**
	 * 
	 * @since       1.1.0
	 */
	protected $_extension_locatnion_default = 'onBeforeRender';
	
	/**
	 * 
	 * @since       1.1.0
	 */
	protected $_valuesModes = array();
	
	/**
	 * 
	 * @since       1.1.0
	 */
	protected $_staticModes = array();
	
	/**
	 * 
	 * @since       1.1.0
	 */
	public function getFields( $types, $states = null, $values = null, $without = true )
	{
		$this->method = __FUNCTION__;
		
		$this->_setConfigFields( $values, $without );
		
		return $this->_getBuffer( $types, null, $states );
	}
	
	/**
	 * 
	 * @since       1.1.0
	 */
	public function getFieldsByID( $types, $ids, $states = null , $values = null, $without = true )
	{
		$this->method = __FUNCTION__;
		
		$this->_setConfigFields( $values, $without );
		
		$this->config->def( 'notName', 'fields' );
		
		return $this->_getBuffer( $types, $ids, $states );
	}
	
	/**
	 * 
	 * @since       1.1.0
	 */
	public function getFieldsByModeID( $types, $ids, $states = null, $values = null, $without = true )
	{
		$this->method = __FUNCTION__;
		
		$this->_setConfigFields( $values, $without );
		
		return $this->_getBuffer( $types, $ids, $states );
	}
	
	/**
	 * 
	 * @since       1.1.0
	 */
	public function __construct( $debug = false )
	{
		parent::__construct( $debug );
		
		$typesHelper =  FieldsandfiltersFactory::getTypes();
		
		$this->_valuesModes = (array) $typesHelper->getMode( 'filter' );
		$this->_staticModes = (array) $typesHelper->getMode( 'static' );
	}
	
	/**
	 * 
	 * @since       1.1.0
	 */
	protected function _setConfigFields( $values = false, $without = true )
	{
		if( !$without )
		{
			$this->config->def( 'elemntsWithoutValues', $without );
		}
		
		if( $values )
		{
			switch( $values )
			{
				case self::VALUES_VALUES:
					$this->config->def( 'getValues', 'values' );
				break;
				case self::VALUES_DATA:
					$this->config->def( 'getValues', 'data' );
				break;
				case self::VALUES_BOTH;
					$this->config->def( 'getValues', array( 'values', 'data' ) );
				break;
			}
		}
	}
	
	/**
	 * 
	 * @since       1.1.0
	 */
	protected function _prepareVars()
	{
		$this->vars->typeName 		= 'content_type_id';
		$this->vars->elementName 	= 'field_id';
		
		if( $this->method == 'getFieldsByID' )
		{
			// We take elements from cache, when they aren't in the cache, we add they to query variables
			$this->vars->fieldsID = array();
		}
		else if( $this->method == 'getFieldsByModeID' )
		{
			$this->vars->modes = array();
		}
		
		return parent::_prepareVars();
	}
	
	/**
	 * 
	 * @since       1.1.0
	 */
	protected function _beforeQuery( $type )
	{
		switch( $this->method )
		{
			case 'getFieldsByID':
				parent::_beforeQueryElements( $type );
			break;
			case 'getFieldsByModeID':
				// Get content type id from cache
				$data  = $this->_getData( $type );
				
				// The difference states between argument states and cache states
				$dataStates	= $data->get( '__states', array() );
				$_states        = array_diff( $this->states, $dataStates );
				
				// The diffrence modes between argument modes and cache modes
				$dataModes	= $data->get( '__modes', array() );
				$_modes		= array_diff( $this->elements, $dataModes );
				
				if( !empty( $_modes ) && !empty( $_states ) )
				{
					// Add difference states to query varible
					$this->vars->states += $_states;
					
					// Add difference modes to query varible
					$this->vars->modes += $_modes;
					
					// When the get modes of the need, then add modes to the cache content type, because we don't need them next time
					$data->set( '__modes', array_merge( $dataModes, $_modes ) );
					
					// Get elements id from cache, because we don't need get that id's second time from database 
					$this->vars->notElements = array_merge( $this->vars->notElements, array_keys( get_object_vars( $data->get( 'elements', new stdClass ) ) ) );
					
					// Add content type id to query varible
					array_push( $this->vars->types, $type );
				}
			break;
			default:
				parent::_beforeQuery( $type );
			break;
		}
	}
	
	/**
	 * 
	 * @since       1.1.0
	 */
	protected function _testQueryVars()
	{
		if( $this->method == 'getFieldsByID' )
		{
			return ( !empty( $this->vars->types ) && !empty( $this->elements ) );
		}
		
		return parent::_testQueryVars();
	}
	
	/**
	 * 
	 * @since       1.1.0
	 */
	protected function _getQuery()
	{
		// Get db and query
		$query = $this->_db->getQuery( true );
			
		$query->select( '*' )
			->from( $this->_db->quoteName( '#__fieldsandfilters_fields' ) )
			->where( $this->_db->quoteName( 'state' ) . ' IN (' . implode( ',', $this->vars->states ) . ')' )		// Fiels where states
			->where( $this->_db->quoteName( 'content_type_id' ) . ' IN(' . implode( ',', $this->vars->types ) . ')' ); 	// Fields where contents type id
		
		if( $this->method == 'getFieldsByID' )
		{
			$query->where( $this->_db->quoteName( 'field_id' ) . ' IN (' . implode( ',', $this->elements ) . ')' );
		}
		else if( $this->method == 'getFieldsByModeID' )
		{
			$query->where( $this->_db->quoteName( 'mode' ) . ' IN (' . implode( ',', $this->vars->modes ) . ')' );
		}
		
		// We no need same elements id
		if( !empty( $this->vars->notElements ) )
		{
			JArrayHelper::toInteger( $this->vars->notElements  );
			$query->where( $this->_db->quoteName( 'field_id' ) . ' NOT IN (' . implode( ',', $this->vars->notElements  ) . ')' );
		}
		
		$query->order( $this->_db->quoteName( 'ordering' ) . ' ASC' );
		
		return $query;
	}
	
	/**
	 * 
	 * @since       1.1.0
	 */
	protected function _setData( &$_field )
        {
		$this->_getData( $_field->content_type_id )->elements->set( $_field->field_id, $_field );
		$_field->params = new JRegistry( $_field->params );
		$_field->location = (array) $_field->params->get( 'extension.location', $this->_extension_locatnion_default );
		
		
		if( ( $byID = $this->method == 'getFieldsByID' ) || $this->method == 'getFieldsByModeID' )
		{
			$this->buffer->set( $_field->field_id, $_field );
		}
		
		if( $byID )
		{
			array_push( $this->vars->fieldsID, $_field->field_id );
		}
        }
	
	/**
	 * 
	 * @since       1.1.0
	 */
	protected function _afterQuery()
	{
		switch( $this->method )
		{
			case 'getFieldsByID':
				if( $this->_testQueryVars() )
				{
					$this->_setNot( array_diff( $this->elements, $this->vars->fieldsID ), 'fields' );
				}
			break;
			case 'getFieldsByModeID':
				// Get elements from cahce
				while( !empty( $this->types ) )
				{
					$_elements = get_object_vars( $this->_getData( array_shift( $this->types ) )->get( 'elements', new JObject ) ) ;
					
					// Add only those elements are suitable states
					while( $_element = current( $_elements ) )
					{
						if( in_array( $_element->mode, $this->elements ) && in_array( $_element->state, $this->states ) )
						{
							$this->buffer->set( $_element->{$this->vars->elementName}, $_element );
						}
						
						next( $_elements );
					}
				}
			break;
			default:
				parent::_afterQuery();
			break;
		}
	}
	
	/**
	 * 
	 * @since       1.1.0
	 */
	protected function _prepareVarsValues()
	{
		if( $this->methodValues == 'values' )
		{
			$this->vars->valuesName = 'values';
		}
		else if( $this->methodValues == 'data' )
		{
			$this->vars->valuesName = 'data';
		}
		
		return parent::_prepareVarsValues();
	}
	
	/**
	 * 
	 * @since       1.1.0
	 */
	protected function _getQueryValues()
	{
		$query = $this->_db->getQuery( true );
		
		$query->select( '*' )
			->where( $this->_db->quoteName( 'field_id' ) . ' IN (' . implode( ',', $this->vars->values->elements ) . ')' );
			
		if( $this->methodValues == 'values' )
		{
			$query->from( $this->_db->quoteName( '#__fieldsandfilters_field_values' ) );
			$query->where( $this->_db->quoteName( 'state' ) . ' = 1' );
			$query->order( $this->_db->quoteName( 'ordering' ) . ' ASC' );
		}
		else if( $this->methodValues == 'data' )
		{
			$query->from( $this->_db->quoteName( '#__fieldsandfilters_data' ) );
			$query->where( $this->_db->quoteName( 'element_id' ) . ' = ' .  0 );
			$query->where( $this->_db->quoteName( 'content_type_id' ) . ' IN(' . implode( ',', $this->vars->types ) . ')' );
		}
				
		return $query;
	}
	
	/**
	 * 
	 * @since       1.1.0
	 */
	protected function _searchValuesElement( &$element )
        {
		$elementName    = $this->vars->elementName;
		$_vars          = $this->vars->values;
		
		if( $this->methodValues == 'values' )
		{
			$modes = $this->_valuesModes;
		}
		else if( $this->methodValues == 'data' )
		{
			$modes = $this->_staticModes;
		}
		else
		{
			return false;
		}
		
		// if element don't have filter_connection, add to arrry
		if( in_array( $element->mode, $modes ) && !isset( $element->{$this->vars->valuesName} ) )
		{
			array_push( $_vars->elements, $element->$elementName );
		}
		// if we need only elements with filter_connections isn't empty
		elseif( !$this->config->get( 'elemntsWithoutValues', true ) && isset( $element->{$this->vars->valuesName} ) )
		{
			if( $this->methodValues == 'values' )
			{
				$_values = get_object_vars( $element->{$this->vars->valuesName} );
			}
			else if( $this->methodValues == 'data' )
			{
				$_values = $element->{$this->vars->valuesName};
			}	
			
			if( empty( $_values ) )
			{
				unset( $this->buffer->{$element->$elementName} );
			}
			
			unset( $_values );
		}
        }
	
	/**
	 * 
	 * @since       1.1.0
	 */
	protected function _addValue( &$_value )
	{
		$element 	= $this->buffer->get( $_value->{$this->vars->elementName} );
		$valuesName 	= $this->vars->valuesName;
		
		if( $this->methodValues == 'values' )
		{
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
		else if( $this->methodValues == 'data' )
		{
			if( isset( $_value->field_id ) && isset( $_value->field_data ) )
			{
				if( !isset( $element->$valuesName ) )
				{
					$element->$valuesName = $_value->field_data;
				}
			}
			else if( !isset( $element->$valuesName ) )
			{
				$element->$valuesName = '';
			}
		}
	}

	/** __call method generator methods:
	 * getFieldsPivot( $pivot, $types, $states = null, $values = null, $without = true )
	 * getFieldsColumn( $column, $types, $states = null )
	 *
	 * getFieldsByIDPivot( $pivot, $types, $ids, $states = null, $values = null, $without = true )
	 * getFieldsByIDColumn( $column, $types, $ids, $states = null )
	 *
	 * getFieldsByModeIDPivot( $pivot, $types, $ids, $states = null, $values = null, $without = true )
	 * getFieldsByModeIDColumn( $column, $types, $ids, $states = null )
	 *
	 * @since       1.1.0
	**/
	protected function _beforeCall( $type, $method, $name, &$arguments )
	{
		if( $type == 'Pivot' )
		{
			if( $method == 'getFields' )
			{
				$values 	= isset( $arguments[3] ) ? $arguments[3] : null;
				$without 	= isset( $arguments[4] ) ? $arguments[4] : true;
			}
			else
			{
				$values 	= isset( $arguments[4] ) ? $arguments[4] : null;
				$without 	= isset( $arguments[5] ) ? $arguments[5] : true;
			}
			
			$this->_setConfigFields( $values, $without );	
		}
	}
	
}