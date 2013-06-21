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
 * FieldsandfiltersElementsHelper
 *
 * @package     com_fieldsandfilters
 * @since       1.0.0
 */

class FieldsandfiltersElementsHelper extends FieldsandfiltersCacheHelper
{
/**
 * Get Elements method
 **/
	public function getElements( $types, $states = null, $values = null, $without = true )
	{
		$this->_setConfigElements( $values, $without );
		
		return parent::_getCache( $types, null, $states );
	}
	
	protected function _setConfigElements( $values = null, $without = true )
	{
		$this->_config['typeName'] = 'extension_type_id';
		$this->_config['elementName'] = 'element_id';
		
		if( !$without )
		{
			$this->_config['elemntsWithoutValues'] = $without;
		}
		
		switch( $values )
		{
			case 1:
				$this->_config['valuesName'] 		= 'connections';
				$this->_config['getCacheValues'] 	= '_getCacheValues';
				$this->_config['getQueryValues'] 	= '_getQueryValuesConnections';
				$this->_config['addValue']		= '_addValueConnections';
			break;
			case 2:
				$this->_config['valuesName'] 		= 'data';
				$this->_config['getCacheValues'] 	= '_getCacheValues';
				$this->_config['getQueryValues'] 	= '_getQueryValuesData';
				$this->_config['addValue']		= '_addValueData';
			break;
			case 3:
				$this->_config['getCacheValues'] = array(
					'_getCacheValuesConnections' => array(
							'valuesName' 		=> 'connections',
							'getCacheValues' 	=> '_getCacheValues',
							'getQueryValues' 	=> '_getQueryValuesConnections',
							'addValue'		=> '_addValueConnections'
					),
					'_getCacheValuesData' => array(
							'valuesName' 		=> 'data',
							'getCacheValues' 	=> '_getCacheValues',
							'getQueryValues' 	=> '_getQueryValuesData',
							'addValue'		=> '_addValueData'
					)
				);
			break;
		}
	}
	
	public function getElementsPivot( $pivot, $types, $states = null, $values = null, $without = true )
	{
		$this->_setConfigElements( $values, $without );
		
		return $this->_getCachePivot( $pivot, $types, null, $states, '_getCache' );
	}
	
	public function getElementsColumn( $column, $types, $states = null )
	{
		$this->_setConfigElements();
		
		return $this->_getCacheColumn( $column, $types, null, $states, '_getCache' );
	}
	
	protected function __getQuery()
	{
		// Get db and query
		$query = $this->_db->getQuery( true );
			
		$query->select( '*' )
			->from( $this->_db->quoteName( '#__fieldsandfilters_elements' ) )
			->where( $this->_db->quoteName( 'state' ) . ' IN (' . implode( ',', $this->_vars->states ) . ')' );	// Elements where states
		
		$types = $this->_vars->types;
		
		$query->where( $this->_db->quoteName( 'extension_type_id' ) . ' IN(' . implode( ',', $types ) . ')' ); 	// Elements where extensions type id
		
		// We no need same elements id
		if( !empty( $this->_vars->notElements ) )
		{
			JArrayHelper::toInteger( $this->_vars->notElements  );
			$query->where( $this->_db->quoteName( 'element_id' ) . ' NOT IN (' . implode( ',', $this->_vars->notElements  ) . ')' );
		}
		
		$query->order( $this->_db->quoteName( 'ordering' ) . ' ASC' );
		
		return $query;
	}
	
	protected function _getQueryValuesConnections()
	{
		$query = $this->_db->getQuery( true );
		
		$query->select( '*' )
			->from( $this->_db->quoteName( '#__fieldsandfilters_connections' ) )
			->where( $this->_db->quoteName( 'element_id' ) . ' IN (' . implode( ',', $this->_vars->values->elements ) . ')' ); 		// Filter on the published state
		
		return $query;
	}
	
	protected function _addValueConnections( $_value )
	{
		$element = $this->_cache->get( $_value->{$this->_vars->elementName} );
		
		$valuesName = $this->_vars->valuesName;
		
		if( !( isset( $element->$valuesName ) && $element->$valuesName instanceof JObject ) )
		{
		     $element->$valuesName = new JObject();
		}
		
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
	
	protected function _getQueryValuesData()
	{
		$query = $this->_db->getQuery( true );
		
		$query->select( '*' )
			->from( $this->_db->quoteName( '#__fieldsandfilters_data' ) )
			->where( $this->_db->quoteName( 'element_id' ) . ' IN (' . implode( ',', $this->_vars->values->elements ) . ')' ); 		// Filter on the published state
		
		return $query;
	}
	
	protected function _addValueData( $_value )
	{
		$element = $this->_cache->get( $_value->{$this->_vars->elementName} );
		
		$valuesName = $this->_vars->valuesName;
		
		if( !( isset( $element->$valuesName ) && $element->$valuesName instanceof JObject ) )
		{
		     $element->$valuesName = new JObject();
		}
		
		if( isset( $_value->field_id ) && isset( $_value->field_data ) )
		{
			if( !isset( $element->$valuesName->{$_value->field_id} ) )
			{
				$element->$valuesName->set( $_value->field_id, $_value->field_data );
			}
		}
	}
	
/**
 * @end Get Elements method
 **/

/**
 * Get Elements by ID method
 **/
	
	public function getElementsByID( $types, $ids, $states = null , $values = null, $without = true )
	{
		$this->_config['notName'] 		= 'elements';
		$this->_config['beforeQuery'] 		= '__beforeQueryElements';
		$this->_config['preparationVars'] 	= '_preparationVarsByID';
		$this->_config['testQueryVars'] 	= '_testQueryVarsByID';
		$this->_config['getQuery'] 		= '_getQueryByID';
		$this->_config['setData'] 		= '_setDataByID';
		$this->_config['afterQuery'] 		= '_afterQueryByID';
		
		$this->_setConfigElements( $values, $without );
		
		return parent::_getCache( $types, $ids, $states );
	}
	
	public function getElementsByIDPivot( $pivot, $types, $ids, $states = null, $values = null, $without = true )
	{
		$this->_setConfigElements( $values, $without );
		
		return $this->_getCachePivot( $pivot, $types, $ids, $states, 'getElementsByID' );
	}
	
	public function getElementsByIDColumn( $column, $types, $states = null )
	{
		$this->_setConfigElements();
		
		return $this->_getCacheColumn( $column, $types, null, $states, 'getElementsByID' );
	}
	
	protected function _preparationVarsByID()
        {
                // We take elements from cache, when they aren't in the cache, we add they to query variables
		$this->_vars->elementsID = array();
		
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
			->from( $this->_db->quoteName( '#__fieldsandfilters_elements' ) )
			->where( $this->_db->quoteName( 'state' ) . ' IN (' . implode( ',', $this->_vars->states ) . ')' )			// Elements where states
			->where( $this->_db->quoteName( 'extension_type_id' ) . ' IN(' . implode( ',', $this->_vars->types ) . ')' )	// Elements where extensions type id
			->where( $this->_db->quoteName( 'element_id' ) . ' IN (' . implode( ',', $this->_elements ) . ')' );			// Elements where elements id
		
		$query->order( $this->_db->quoteName( 'ordering' ) . ' ASC' );
		
		return $query;
	}

	protected function _setDataByID( &$_element )
        {
                $this->_getData( $_element->extension_type_id )->elements->set( $_element->element_id, $_element );
		
		$this->_cache->{$_element->element_id} = $_element;
		
		array_push( $this->_vars->elementsID, $_element->element_id );
        }
	
	protected function _afterQueryByID()
        {		
		if( $this->_testQueryVarsByID() )
		{
			$this->_setNot( array_diff( $this->_elements, $this->_vars->elementsID ), 'elements' );
		}
        }
	
/**
 * @end Get Elements by ID method
 **/


/**
 * Get Elements by ID method
 **/
	
	public function getElementsByItemID( $types, $ids, $states = null, $values = null, $without = true )
	{
		
		$this->_config['notName'] 		= 'items';
		$this->_config['beforeQuery'] 		= '__beforeQueryElements';
		$this->_config['preparationVars'] 	= '_preparationVarsByItemID';
		$this->_config['searchElements'] 	= '_searchElementsByItemID';
		$this->_config['testQueryVars'] 	= '_testQueryVarsByItemID';
		$this->_config['getQuery'] 		= '_getQueryByItemID';
		$this->_config['setData'] 		= '_setDataByItemID';
		$this->_config['afterQuery'] 		= '_afterQueryByItemID';
		
		$this->_setConfigElements( $values, $without );
		
		return parent::_getCache( $types, $ids, $states );
	}
	
	public function getElementsByItemIDPivot( $pivot, $types, $ids, $states = null, $values = null, $without = true )
	{
		$this->_setConfigElements( $values, $without );
		
		return $this->_getCachePivot( $pivot, $types, $ids, $states, 'getElementsByItemID' );
	}
	
	public function getElementsByItemIDColumn( $column, $types, $states = null )
	{
		$this->_setConfigElements();
		
		return $this->_getCacheColumn( $column, $types, null, $states, 'getElementsByItemID' );
	}
	
	/**
	 * Method get elements id
	 *
	 * @param	int/array       	$extensionsTypeID	intiger or array of extensions type id
	 * @param	int/array       	$itemsID		intiger or array of items id
	 * @param	int/array/null		$states		        intiger or array of states
	 * @param	bollean			$itemsKey		when we need key array is items id and value array is elements id
	 * 
	 * @return	array elements id.
	 * @since	1.0
	 */
        public function getElementsID( $types, $ids, $states = null, $itemsKey = true )
        {
		// Get elements
                $elements = (array) get_object_vars( $this->getElementsByItemID( $types, $ids, $states ) );
		
		if( !empty( $elements ) )
		{
			return FieldsandfiltersArrayHelper::getColumn( $elements, 'element_id', ( $itemsKey ? 'item_id' : null ) );
		}
		
		return array();
        }
	
	/**
	 * Method get element id, return only first element id if exists
	 *
	 * @param	int/array       	$extensionTypeID	intiger or array of extensions type id
	 * @param	int/array       	$itemID			intiger or array of items id
	 * @param	int/array/null		$states		        intiger or array of states
	 * 
	 * @return	if exists return int element id, empty return null.
	 * @since	1.0
	 */
	public function getElementID( $types, $id, $states = null )
        {
		// Get elements
		$elements = (array) get_object_vars( $this->getElementsByItemID( $types, $id, $states ) );
		if( !empty( $elements ) )
		{
			reset( $elements );
			return current( $elements )->element_id;
		}
		
		return null;
        }
	
	protected function _preparationVarsByItemID()
        {
                $this->_vars->itemsID = array();
		$this->_vars->elementsItemsID = array();
		
		// We take elements from cache, when they aren't in the cache, we add they to query variables
		return parent::__preparationVars();
        }
	
	protected function _beforeSearchElementsByItemID( &$data )
	{
		// Get elements id from elements where is items id
		$this->_vars->elementsItemsID = FieldsandfiltersArrayHelper::getColumn( $data->get( 'elements', new stdClass ), 'item_id', 'element_id' );
	}
	
	protected function _searchElementsByItemID( &$data, $itemID, &$_itemsID, &$_notItems )
        {
                // We take element from cache and this id add to array
                if( ( $elementID = array_search( $itemID, $this->_vars->elementsItemsID ) ) !== false )
                {
			$_element = $data->elements->get( $elementID );
			
                        if( in_array( $_element->state, $this->_states ) )
                        {
                                $this->_cache->{$_element->element_id} = $_element;	
                        }
                        
                        array_push( $_itemsID, $itemID );
                }
                // If argument element id in array ids not exist, add that id to array exist id, because we know that id isn't exist
                elseif( in_array( $itemID, $_notItems ) )
                {
                        array_push( $_itemsID, $itemID );
                }
        }
	
	protected function _testQueryVarsByItemID()
        {
                return ( !empty( $this->_vars->types ) && !empty( $this->_elements ) );
        }
	
	protected function _getQueryByItemID()
	{
		// Get db and query
		$query = $this->_db->getQuery( true );
			
		$query->select( '*' )
			->from( $this->_db->quoteName( '#__fieldsandfilters_elements' ) )
			->where( $this->_db->quoteName( 'state' ) . ' IN (' . implode( ',', $this->_vars->states ) . ')' )			// Elements where states
			->where( $this->_db->quoteName( 'extension_type_id' ) . ' IN(' . implode( ',', $this->_vars->types ) . ')' )		// Elements where extensions type id
			->where( $this->_db->quoteName( 'item_id' ) . ' IN (' . implode( ',', $this->_elements ) . ')' );			// Elements where elements id
		
		$query->order( $this->_db->quoteName( 'ordering' ) . ' ASC' );
		
		return $query;
	}

	protected function _setDataByItemID( &$_element )
        {
                $this->_getData( $_element->extension_type_id )->elements->set( $_element->element_id, $_element );
		
		$this->_cache->{$_element->element_id} = $_element;
		
		array_push( $this->_vars->itemsID, $_element->item_id );
        }
	
	protected function _afterQueryByItemID()
        {		
		if( $this->_testQueryVarsByID() )
		{
			$this->_setNot( array_diff( $this->_elements, $this->_vars->itemsID ), 'items' );
		}
        }
}