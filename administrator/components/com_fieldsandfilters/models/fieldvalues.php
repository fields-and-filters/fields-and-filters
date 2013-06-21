<?php
/**
 * @version     1.0.0
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kulka.tomek@gmail.com> - 
 */

defined('_JEXEC') or die;

if( version_compare( JVERSION, 3.0, '<' ) )
{
	jimport( 'joomla.application.component.modellist' );
}

/**
 * Methods supporting a list of Fieldsandfilters records.
 */
class FieldsandfiltersModelfieldvalues extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param    array    An optional associative array of configuration settings.
	 * @see        JController
	 * @since    1.6
	 */
	public function __construct( $config = array() )
	{
		if( empty( $config['filter_fields'] ) )
		{
			$config['filter_fields'] = array(
						'field_value_id', 	'fv.field_value_id',
						'field_id', 		'fv.field_id',
						'field_value',		'fv.field_value',
						'field_value_alias', 	'fv.field_value_alias',
						'ordering', 		'fv.ordering',
						'state',		'fv.state',
						'field_name',		'f.field_name',
						'id'
			);
		}
		
		parent::__construct( $config );
	}


	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 */
	protected function populateState( $ordering = null, $direction = null )
	{
		// Initialise variables.
		$app 	= JFactory::getApplication( 'administrator' );
		$jinput = $app->input;   
		// Load the filter state.
		$search = $app->getUserStateFromRequest( $this->context . '.filter.search', 'filter_search' );
		$this->setState( 'filter.search', $search );
  
		$published = $app->getUserStateFromRequest( $this->context . '.filter.state', 'filter_published', '', 'string' );
		$this->setState( 'filter.state', $published );
		
		if( $fieldID = $jinput->getInt( 'id', $app->getUserStateFromRequest( $this->context . '.filter.field_id', 'filter_field_id', 0, 'int' ) ) )
		{
			if( $fieldID != $app->getUserState( $this->context . '.filter.field_id' ) )
			{
				$app->setUserState( $this->context . '.filter.field_id', $fieldID );
				$jinput->set( 'limitstart', 0 );
			}
		}
		else
		{
			if( !( $fieldID = $app->getUserState( $this->context . '.filter.field_id' ) ) )
			{
				// Load pluginTypes Helper
				JLoader::import( 'helpers.fieldsandfilters.pluginTypes', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
				
				$valuesTypes = FieldsandfilterspluginTypesHelper::getInstance()->getMode( 'values' );
				
				// Load pluginExtensions Helper
				JLoader::import( 'helpers.fieldsandfilters.pluginextensions', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
				
				$extensionsID = FieldsandfilterspluginExtensionsHelper::getInstance()->getExtensionsColumn( 'extension_type_id' );
				
				// Load Fields Helper
				JLoader::import( 'helpers.fieldsandfilters.fields', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
				
				$fieldsID = FieldsandfiltersFieldsHelper::getInstance()->getFieldsByModeIDColumn( 'field_id', $extensionsID, $valuesTypes, array( 1, -1 ) );
				$fieldID = current( $fieldsID );
				
			}
		}
		$this->setState( 'filter.field_id', (int) $fieldID );
		
		// Load the parameters.
		$params = JComponentHelper::getParams( 'com_fieldsandfilters' );
		$this->setState( 'params', $params );
		
		// List state information.
		parent::populateState( 'fv.field_value', 'asc' );
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
		$id .= ':' . $this->getState( 'filter.id' );
	
		return parent::getStoreId( $id );
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
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
		$query->select( $db->quoteName( 'f.field_name' ) );
		$query->join( 'LEFT', $db->quoteName( '#__fieldsandfilters_fields', 'f' ) . ' ON ' . $db->quoteName( 'f.field_id' ) . ' = ' . $db->quoteName( 'fv.field_id' ) );
		
		// Filter by field id in title
		$fieldID = $this->getState( 'filter.field_id' );
		if( is_numeric( $fieldID ) )
		{
			$query->where( $db->quoteName( 'f.field_id' ) . ' = ' . (int) $fieldID );
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
				$query->where( $db->quoteName( 'fv.field_value_id' ) . ' = ' . (int) substr( $search, 3 ) );
			}
			else
			{
				$search = $db->Quote( '%' . $db->escape( $search, true ) . '%' );
				
				$where = array(
						( $db->quoteName( 'fv.field_value' ) . ' LIKE ' . $search ),
						( $db->quoteName( 'fv.field_value_alias' ) . ' LIKE ' . $search )
					);
				
				$query->where( '( ' . implode( ' OR ', $where ) . ' )' );
			}
		}
		
		// Add the list ordering clause.
		$orderCol	= $this->state->get( 'list.ordering' );
		$orderDirn	= $this->state->get( 'list.direction' );
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
	 * @since   11.1
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
