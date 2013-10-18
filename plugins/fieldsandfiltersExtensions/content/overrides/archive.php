<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::import( 'com_content.models.archive', JPATH_SITE . '/components' );

/* [TEST] */
/**
 * @since       1.0.0
 */
class plgFieldsandfiltersExtensionsContentModelArchive extends ContentModelArchive
{
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState();
		
		$app = JFactory::getApplication();
		
		/* [TEST] */
		$params = $app->getParams('com_content');
		$this->setState('params', $params);

		// process show_noauth parameter
		if (!$params->get('show_noauth'))
		{
			$this->setState('filter.access', true);
		}
		else
		{
			$this->setState('filter.access', false);
		}
		/* @end [TEST] */
		
		// Add archive properties
		$params = $this->state->params;

		// Filter on archived articles
		$this->setState('filter.published', 2);

		// Filter on month, year
		$this->setState('filter.month', $app->input->getInt('month'));
		$this->setState('filter.year', $app->input->getInt('year'));

		// Optional filter text
		$this->setState('list.filter', $app->input->getString('filter-search'));

		// Get list limit
		$itemid = $app->input->get('Itemid', 0, 'int');
		$limit = $app->getUserStateFromRequest('com_content.archive.list' . $itemid . '.limit', 'limit', $params->get('display_num'), 'uint');
		$this->setState('list.limit', $limit);
	}
	
	/**
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		
		$query = parent::getListQuery();
		
		// Filter Fieldsandfilters itemsID
		$itemsID 	= (array) $this->getState( 'fieldsandfilters.itemsID' );
		$emptyItemsID 	= $this->setState( 'fieldsandfilters.emptyItemsID', false );
		
		if( !empty( $itemsID ) && !$emptyItemsID  )
		{
			JArrayHelper::toInteger( $itemsID );
			$query->where( $db->quoteName( 'a.id' ) . ' IN( ' . implode( ',', $itemsID ) . ')' );
		}
		
		return $query;
	}

	/**
	 * Method to get the archived article list
	 *
	 * @access public
	 * @return array
	 */
	public function getData()
	{
		$app = JFactory::getApplication();

		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			// Get the page/component configuration
			$params = $app->getParams('com_content');

			// Get the pagination request variables
			$limit		= $app->input->get('limit', $params->get('display_num', 20), 'uint');
			$limitstart	= $app->input->get('limitstart', 0, 'uint');

			$query = $this->_buildQuery();

			$this->_data = $this->_getList($query, $limitstart, $limit);			
		}

		return $this->_data;
	}

	// JModelLegacy override to add alternating value for $odd
	protected function _getList($query, $limitstart=0, $limit=0)
	{
		$result = parent::_getList($query, $limitstart, $limit);

		$odd = 1;
		foreach ($result as $k => $row)
		{
			$result[$k]->odd = $odd;
			$odd = 1 - $odd;
		}

		return $result;
	}
	
	/**
	 * @since       1.0.0
	 */
	public function getItemsID()
	{
		// Get a storage key.
		$store = $this->getStoreId( 'getItemsID' );

		// Try to load the data from internal storage.
		if( isset( $this->cache[$store] ) )
		{
			return $this->cache[$store];
		}
		
		// Load the list items ID.
		$query = clone $this->_getListQuery();
		$query->clear( 'select' );
		$query->clear( 'order' );
		$query->clear( 'group' );
		
		$query->select( 'DISTINCT ' . $this->_db->quoteName( 'a.id' ) );
		$this->_db->setQuery($query);
		
		if( !( $itemsID = $this->_db->loadColumn() ) )
		{
			$itemsID = array();
		}
		
		$this->setState( 'fieldsandfilters.itemsID', $itemsID );
		
		// Add the items to the internal cache.
		$this->cache[$store] = $itemsID;
		
		return $this->cache[$store];
		
	}

	/**
	 * Returns a record count for the query
	 *
	 * @param   string  $query  The query.
	 *
	 * @return  integer  Number of rows for query
	 *
	 * @since       1.0.0
	 */
	protected function _getListCount( $query )
	{
		$rows = count( $this->getItemsID() );
		
		return $rows;
	}
	
	/**
	 * @since       1.0.0
	 */
	public function getContentItemsID()
	{
		$limit 		= $this->getState( 'list.limit' );
		$itemsID 	= array();
		
		if( $limit >= 0 )
		{
		 	$itemsID = $this->getItemsID();
		}
		
		return $itemsID;
	}
}
