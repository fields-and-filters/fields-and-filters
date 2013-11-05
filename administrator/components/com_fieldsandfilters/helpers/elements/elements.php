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
 * FieldsandfiltersElements
 *
 * @package     com_fieldsandfilters
 * @since       1.1.0
 */
class FieldsandfiltersElements extends KextensionsBufferValues
{
	/**
         * An array of names that don't exists
         * 
	 * @var    array 
	 * @since       1.1.0
	 */
	protected $_not = array( 'items' => '__notItems', 'elements' => '__notElements' );
	
	/**
	 * 
	 * @since       1.1.0
	 */
	public function getElements( $types, $states = null, $values = null, $without = true )
	{
		$this->method = __FUNCTION__;
		
		$this->_setConfigElements( $values, $without );
		
		return $this->_getBuffer( $types, null, $states );
	}
	
	/**
	 * 
	 * @since       1.1.0
	 */
	public function getElementsByID( $types, $ids, $states = null , $values = null, $without = true )
	{
		$this->method = __FUNCTION__;
		
		$this->_setConfigElements( $values, $without );
		
		$this->config->def( 'notName', 'elements' );
		
		return $this->_getBuffer( $types, $ids, $states );
	}
	
	/**
	 * 
	 * @since       1.1.0
	 */
	public function getElementsByItemID( $types, $ids, $states = null, $values = null, $without = true )
	{
		$this->method = __FUNCTION__;
		
		$this->_setConfigElements( $values, $without );
		
		$this->config->def( 'notName', 'items' );
		
		return $this->_getBuffer( $types, $ids, $states );
	}
	
	/**
	 * Method get element id, return only first element id if exists
	 *
	 * @param	int/array       	$extensionTypeID	intiger or array of extensions type id
	 * @param	int/array       	$itemID			intiger or array of items id
	 * @param	int/array/null		$states		        intiger or array of states
	 * 
	 * @return	if exists return int element id, empty return null.
	 * @since	1.0.0
	 */
	public function getElementID( $types, $id, $states = null )
        {
		// Get elements
		$elements = (array) $this->getElementsByItemIDColumn( 'element_id', $types, $id, $states );
		if( !empty( $elements ) )
		{
			reset( $elements );
			return current( $elements );
		}
		
		return null;
        }
	
	/**
	 * 
	 * @since       1.1.0
	 */
	protected function _setConfigElements( $values = null, $without = true )
	{
		if( !$without )
		{
			$this->config->def( 'elemntsWithoutValues', $without );
		}
		
		if( $values )
		{			
			switch( $values )
			{
				case 1:
				case 'connections':
					$this->config->def( 'getValues', 'connections' );
				break;
				case 2:
				case 'data':
					$this->config->def( 'getValues', 'data' );
				break;
				case 3:
				case 'both':
					$this->config->def( 'getValues', array( 'data', 'connections' ) );
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
		$this->vars->typeName 		= 'extension_type_id';
		$this->vars->elementName 	= 'element_id';
		
		if( $this->method == 'getElementsByID' )
		{
			// We take elements from cache, when they aren't in the cache, we add they to query variables
			$this->vars->elementsID = array();
		}
		else if( $this->method == 'getElementsByItemID' )
		{
			$this->vars->itemsID = array();
			$this->vars->elementsItemsID = array();
		}
		
		return parent::_prepareVars();
	}
	
	/**
	 * 
	 * @since       1.1.0
	 */
	protected function _beforeQuery( $type )
	{
		if( $this->method == 'getElementsByID' || $this->method == 'getElementsByItemID' )
		{
			parent::_beforeQueryElements( $type );
		}
		else
		{
			parent::_beforeQuery( $type );
		}
	}
	
	/**
	 * 
	 * @since       1.1.0
	 */
	protected function _beforeSearchElements( $data )
	{
		if( $this->method == 'getElementsByItemID' )
		{
			// Get elements id from elements where is items id
			$this->vars->elementsItemsID = KextensionsArray::getColumn( $data->get( 'elements', new stdClass ), 'item_id', 'element_id' );
		}
	}
	
	/**
	 * 
	 * @since       1.1.0
	 */
	protected function _searchElements( &$data, $itemID, &$_itemsID, &$_notItems )
        {
		if( $this->method == 'getElementsByItemID' )
		{
			// We take element from cache and this id add to array
			if( ( $elementID = array_search( $itemID, $this->vars->elementsItemsID ) ) !== false )
			{
				$_element = $data->elements->get( $elementID );
				
				if( in_array( $_element->state, $this->states ) )
				{
					$this->buffer->{$_element->element_id} = $_element;	
				}
				
				array_push( $_itemsID, $itemID );
			}
			// If argument element id in array ids not exist, add that id to array exist id, because we know that id isn't exist
			elseif( in_array( $itemID, $_notItems ) )
			{
				array_push( $_itemsID, $itemID );
			}
		}
		else
		{
			parent::_searchElements( $data, $itemID, $_itemsID, $_notItems );
		}
        }
	
	/**
	 * 
	 * @since       1.1.0
	 */
	protected function _testQueryVars()
	{
		if( $this->method == 'getElementsByID' || $this->method == 'getElementsByItemID' )
		{
			return ( !empty( $this->vars->types ) && !empty( $this->elements ) );
		
		}
		else
		{
			return parent::_testQueryVars();
		}
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
			->from( $this->_db->quoteName( '#__fieldsandfilters_elements' ) )
			->where( $this->_db->quoteName( 'state' ) . ' IN (' . implode( ',', $this->vars->states ) . ')' )	// Elements where states
			->where( $this->_db->quoteName( 'extension_type_id' ) . ' IN(' . implode( ',', $this->vars->types ) . ')' ); 	// Elements where extensions type id
		
		if( $this->method == 'getElementsByID' )
		{
			$query->where( $this->_db->quoteName( 'element_id' ) . ' IN (' . implode( ',', $this->elements ) . ')' );
		}
		else if( $this->method == 'getElementsByItemID' )
		{
			$query->where( $this->_db->quoteName( 'item_id' ) . ' IN (' . implode( ',', $this->elements ) . ')' );
		}
		
		// We no need same elements id
		if( !empty( $this->vars->notElements ) )
		{
			JArrayHelper::toInteger( $this->vars->notElements  );
			$query->where( $this->_db->quoteName( 'element_id' ) . ' NOT IN (' . implode( ',', $this->vars->notElements  ) . ')' );
		}
		
		$query->order( $this->_db->quoteName( 'ordering' ) . ' ASC' );
		
		return $query;
	}
	
	/**
	 * 
	 * @since       1.1.0
	 */
	protected function _setData( &$_element )
        {
		if( ( $byID = $this->method == 'getElementsByID' ) || ( $byItem = $this->method == 'getElementsByItemID' ) )
		{
			$this->_getData( $_element->extension_type_id )->elements->set( $_element->element_id, $_element );
			$this->buffer->{$_element->element_id} = $_element;
			
			if( $byID )
			{
				array_push( $this->vars->elementsID, $_element->element_id );
			}
			else if( $byItem )
			{
				array_push( $this->vars->itemsID, $_element->item_id );
			}
		}
		else
		{
			parent::_setData( $_element );
		}
        }
	
	/**
	 * 
	 * @since       1.1.0
	 */
	protected function _afterQuery()
	{
		if( ( $byID = $this->method == 'getElementsByID' ) || ( $byItem = $this->method == 'getElementsByItemID' ) )
		{
			if( $this->_testQueryVars() )
			{
				if( $byID )
				{
					$this->_setNot( array_diff( $this->elements, $this->vars->elementsID ), 'elements' );
				}
				else if( $byItem )
				{
					$this->_setNot( array_diff( $this->elements, $this->vars->itemsID ), 'items' );
				}
			}
		}
		else
		{
			parent::_afterQuery();
		}
	}
	
	/**
	 * 
	 * @since       1.1.0
	 */
	protected function _getQueryValues()
	{
		// Get db and query
		$query = $this->_db->getQuery( true );
		
		$query->select( '*' );
		
		if( $this->methodValues == 'data' )
		{
			$query->from( $this->_db->quoteName( '#__fieldsandfilters_data' ) );
		}
		else if( $this->methodValues == 'connections' )
		{
			$query->from( $this->_db->quoteName( '#__fieldsandfilters_connections' ) );
		}
		
		$query->where( $this->_db->quoteName( 'element_id' ) . ' IN (' . implode( ',', $this->vars->values->elements ) . ')' );
		
		return $query;
	}
	
	/**
	 * 
	 * @since       1.1.0
	 */
	protected function _addValue( &$_value )
	{
		$element 	= $this->buffer->get( $_value->{$this->vars->elementName} );
		$valuesName 	= $this->vars->valuesName;
		
		if( !( isset( $element->$valuesName ) && $element->$valuesName instanceof JObject ) )
		{
		     $element->$valuesName = new JObject();
		}
		
		if( $this->methodValues == 'data' )
		{
			if( isset( $_value->field_id ) && isset( $_value->field_data ) )
			{
				if( !isset( $element->$valuesName->{$_value->field_id} ) )
				{
					$element->$valuesName->set( $_value->field_id, $_value->field_data );
				}
			}
		}
		else if( $this->methodValues == 'connections' )
		{
			if( isset( $_value->field_id ) && isset( $_value->field_value_id ) )
			{
				if( !isset( $element->$valuesName->{$_value->field_id} ) )
				{
					$element->$valuesName->set( $_value->field_id, array() );
				}
				
				if( is_array( $_value->field_value_id ) )
				{
					$element->$valuesName->set( $_value->field_id, array_merge( $element->$valuesName->{$_value->field_id}, $_value->field_value_id ) );
				}
				else
				{
					array_push( $element->$valuesName->{$_value->field_id}, $_value->field_value_id );
				}
			}
		}	
	}
	
	/** __call method generator methods:
	 * getElementsPivot( $pivot, $types, $states = null, $values = null, $without = true )
	 * getElementsColumn( $column, $types, $states = null )
	 * getElementsByIDPivot( $pivot, $types, $ids, $states = null, $values = null, $without = true )
	 * getElementsByIDColumn( $column, $types, $states = null )
	 * getElementsByItemIDPivot( $pivot, $types, $ids, $states = null, $values = null, $without = true )
	 * getElementsByItemIDColumn( $column, $types, $states = null )
	 * 
	 * @since       1.1.0
	**/
	protected function _beforeCall( $type, $method, $name, &$arguments )
	{
		if( $type == 'Pivot' )
		{
			if( $method == 'getFilters' )
			{
				$values 	= isset( $arguments[3] ) ? $arguments[3] : null;
				$without 	= isset( $arguments[4] ) ? $arguments[4] : true;
			}
			else
			{
				$values 	= isset( $arguments[4] ) ? $arguments[4] : null;
				$without 	= isset( $arguments[5] ) ? $arguments[5] : true;
			}
			
			$this->_setConfigElements( $values, $without );	
		}
	}
}