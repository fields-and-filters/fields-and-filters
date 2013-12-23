<?php
/**
 * @version     1.1.1
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kulka.tomek@gmail.com> - 
 */

defined( '_JEXEC' ) or die;

if( !FieldsandfiltersFactory::isVersion() )
{
	jimport( 'joomla.application.component.modellist' );
}

/**
 * Methods supporting a list of Fieldsandfilters records.
 * @since       1.1.0
 */
class FieldsandfiltersModelElements extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param    array    An optional associative array of configuration settings.
	 * @see        JController
	 * @since       1.0.0
	 */
	public function __construct( $config = array() )
	{
		if( empty( $config['filter_fields'] ) )
		{
			$config['filter_fields'] = array(
				'id', 			'e.id',
				'content_type_id', 	'e.content_type_id',
				'item_id', 		'e.item_id',
				'ordering', 		'e.ordering',
				'state', 		'e.state'
			);
		}
		
		parent::__construct( $config );
	}


	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 * @since       1.1.0
	 */
	protected function populateState( $ordering = null, $direction = null )
	{
		// Initialise variables.
		$app = JFactory::getApplication();
		
		// Load the filter state.
		$search = $app->getUserStateFromRequest( $this->context.'.filter.search', 'filter_search' );
		$this->setState( 'filter.search', $search );
		
		$published = $app->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string' );
		$this->setState( 'filter.state', $published );
		
		// Load the parameters.
		$params = JComponentHelper::getParams( 'com_fieldsandfilters' );
		$this->setState( 'params', $params );
		
		$contentTypeID = $app->getUserStateFromRequest( $this->context . '.filter.content_type_id', 'filter_extension_type_id', 0, 'int' );
		
		if( $contentTypeID && ( $extension = FieldsandfiltersFactory::getExtensions()->getExtensionsByTypeIDPivot( 'content_type_id', $contentTypeID )->get( $contentTypeID ) ) )
		{
			// Include the content plugins for the on delete events.
			JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
			
			// Trigger the onContentBeforeDelete event.
			FieldsandfiltersFactory::getDispatcher()->trigger( 'onFieldsandfiltersPopulateState', array( ( $this->context . '.' . $extension->name ), $this->state, &$this->filter_fields ) );
			
			$this->setState( $this->getName() . '.extension_name', $extension->name );
		}
		
		$this->setState( 'filter.extension_type_id', $extensionTypeId );
		
		// List state information.
		parent::populateState( $this->state->get( 'query.item_id', 'e.item_id' ), 'asc' );
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
	 * @since       1.0.0
	 */
	protected function getStoreId( $id = '' )
	{
		// Compile the store id.
		$id.= ':' . $this->getState( 'filter.search' );
		$id.= ':' . $this->getState( 'filter.state' );
		$id.= ':' . $this->getState( 'filter.content_type_id' );

		return parent::getStoreId( $id );
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since       1.0.0
	 */
	protected function getListQuery()
	{
		// get extension, we need him for query
		$extensionName = $this->getState( $contentTypeID . '.extension_name' );
		
		if( empty( $extensionName ) )
		{
			return false;
		}
		
		// Create a new query object.
		$db		= $this->getDbo();
		$query		= $db->getQuery( true );

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'e.*'
			)
		);
		$query->from( $db->quoteName( '#__fieldsandfilters_elements', 'e' ) );
		
		$query->wher( $db->quoteName( 'e.extension_type_id' ) . ' = ' . (int) $this->getState( 'filter.extension_type_id' ) );
		
		// Filter by published state
		$published = $this->getState( 'filter.state' );
		if( is_numeric( $published ) )
		{
			$query->where( 'e.state = ' . (int) $published );
		}
		
		// Include the fieldsandfiltersExtensions plugins for the on PrepareList Query events.
		JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
		
		// Trigger the onPrepareListQuery event.
		$result = JFactory::getApplication()->triggerEvent( 'onFieldsandfiltersPrepareListQuery', array( ( $this->context . '.' . $extensionName ), $query, $this->state ) );
		
		if( in_array( false, $result, true ) )
		{
			$this->setError( JText::sprintf( 'COM_FIELDSANDFILTERS_DATABASE_ERROR_PREPARE_LIST_QUERY', $this->getState( 'elements.extension_name' ) ) );
			
			$this->setState( 'elements.extension_name', null );
			
			return false;
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
	 * @since       1.0.0
	 */
	public function getItems()
	{
		$extensionName = $this->getState( $this->getName() . '.extension_name' );
		
		return ( !empty( $extensionName ) ? parent::getItems() : array() );
	}
	
	/**
	 * Method to get a JPagination object for the data set.
	 *
	 * @return  JPagination  A JPagination object for the data set.
	 * @since       1.0.0
	 */
	public function getPagination()
	{
		$extensionName = $this->getState( $this->getName() . '.extension_name' );
		
		if( $extensionName )
		{
			$page = parent::getPagination();
		}
		else
		{
			// Create the pagination object.
			jimport('joomla.html.pagination');
			$limit 	= (int) $this->getState( 'list.limit' ) - (int) $this->getState( 'list.links' );
			$page 	= new JPagination( 0, 0, $limit );
		}
		
		return $page;
	}
}
