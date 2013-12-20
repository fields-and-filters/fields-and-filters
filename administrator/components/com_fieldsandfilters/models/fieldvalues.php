<?php
/**
 * @version     1.1.1
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kulka.tomek@gmail.com> - 
 */

defined('_JEXEC') or die;

if( !FieldsandfiltersFactory::isVersion() )
{
	jimport( 'joomla.application.component.modellist' );
}

/**
 * Methods supporting a list of Fieldsandfilters records.
 * @since	1.1.0
 */
class FieldsandfiltersModelFieldvalues extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param    array    An optional associative array of configuration settings.
	 * @see        JController
	 * @since	1.0.0
	 */
	public function __construct( $config = array() )
	{
		if( empty( $config['filter_fields'] ) )
		{
			$config['filter_fields'] = array(
				'id', 		'fv.id',
				'field_id', 	'fv.field_id',
				'value',	'fv.value',
				'alias', 	'fv.alias',
				'ordering', 	'fv.ordering',
				'state',	'fv.state',
				'field_name',	'f.field_name'
			);
		}
		
		parent::__construct( $config );
	}


	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 * @since	1.1.0
	 */
	protected function populateState( $ordering = null, $direction = null )
	{
		// Initialise variables.
		$app 	= JFactory::getApplication();
		
		// Load the filter state.
		$search = $app->getUserStateFromRequest( $this->context . '.filter.search', 'filter_search' );
		$this->setState( 'filter.search', $search );
  
		$published = $app->getUserStateFromRequest( $this->context . '.filter.state', 'filter_published', '', 'string' );
		$this->setState( 'filter.state', $published );
		
		if( $fieldID = $app->input->getInt( 'field_id', $app->getUserStateFromRequest( $this->context . '.filter.field_id', 'filter_field_id', 0, 'int' ) ) )
		{
			if( $fieldID != $app->getUserState( $this->context . '.filter.field_id' ) )
			{
				$app->setUserState( $this->context . '.filter.field_id', $fieldID );
				$app->input->set( 'limitstart', 0 );
			}
		}
		else
		{
			if( !( $fieldID = $app->getUserState( $this->context . '.filter.field_id' ) ) )
			{
				$filterMode 	= FieldsandfiltersModes::getMode( FieldsandfiltersModes::MODE_FILTER );
				
				// Load pluginExtensions Helper
				$extensionsID 	= FieldsandfiltersFactory::getExtensions()->getExtensionsByTypeID( 'content_type_id' );
				
				// Load Fields Helper
				$fieldsID 	= FieldsandfiltersFactory::getFields()->getFieldsByModeIDColumn( 'field_id', $extensionsID, $filterMode, array( 1, -1 ) );
				$fieldID 	= current( $fieldsID );
				
			}
		}
		
		$this->setState( 'filter.field_id', (int) $fieldID );
		
		// Load the parameters.
		$params = JComponentHelper::getParams( 'com_fieldsandfilters' );
		$this->setState( 'params', $params );
		
		// List state information.
		parent::populateState( 'fv.value', 'asc' );
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 * @return	string		A store id.
	 * @since	1.6
	 */
	protected function getStoreId( $id = '' )
	{
		// Compile the store id.
		$id .= ':' . $this->getState( 'filter.search' );
		$id .= ':' . $this->getState( 'filter.state' );
		$id .= ':' . $this->getState( 'filter.field_id' );
	
		return parent::getStoreId( $id );
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.0.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query		= $db->getQuery( true );
		
		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'fv.*'
			)
		);
		$query->from( $db->quoteName( '#__fieldsandfilters_field_values', 'fv' ) );
		
		// Join over the fields.
		$query->select( $db->quoteName( 'f.name', 'field_name' ) );
		$query->join( 'LEFT', $db->quoteName( '#__fieldsandfilters_fields', 'f' ) . ' ON ' . $db->quoteName( 'f.id' ) . ' = ' . $db->quoteName( 'fv.field_id' ) );
		
		// Filter by field id in title
		$fieldID = $this->getState( 'filter.field_id' );
		if( is_numeric( $fieldID ) )
		{
			$query->where( $db->quoteName( 'f.id' ) . ' = ' . (int) $fieldID );
		}
		
		// Filter by published state
		$state = $this->getState( 'filter.state' );
		if( is_numeric( $state ) )
		{
		    $query->where( $db->quoteName( 'fv.state' ) . ' = ' . (int) $state );
		}
		
		// Filter by search in title
		$search = $this->getState( 'filter.search' );
		if( !empty( $search ) )
		{
			if( stripos( $search, 'id:' ) === 0 )
			{
				$query->where( $db->quoteName( 'fv.id' ) . ' = ' . (int) substr( $search, 3 ) );
			}
			else
			{
				$search = $db->Quote( '%' . $db->escape( $search, true ) . '%' );
				
				$where = array(
						( $db->quoteName( 'fv.value' ) . ' LIKE ' . $search ),
						( $db->quoteName( 'fv.alias' ) . ' LIKE ' . $search )
					);
				
				$query->where( '( ' . implode( ' OR ', $where ) . ' )' );
			}
		}
		
		// Add the list ordering clause.
		$orderCol	= $this->state->get( 'list.ordering', 'fv.value');
		$orderDirn	= $this->state->get('list.direction', 'ASC');
		
		if( $orderCol && $orderDirn )
		{
		    $query->order( $db->escape( $orderCol . ' ' . $orderDirn ) );
		}
		
		return $query;
	}
	
	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since	1.0.0
	 */
	public function getItems()
	{
		if( !$this->getState( 'filter.field_id' ) )
		{
			return array();
		}
		
		return parent::getItems();
	}
}
