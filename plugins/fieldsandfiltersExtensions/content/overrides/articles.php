<?php
/**
 * @version     1.0.0
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_extension.content
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined( '_JEXEC' ) or die;

JLoader::import( 'com_content.models.articles', JPATH_SITE . '/components' );

/**
* @since       1.0.0
*/
class plgFieldsandfiltersExtensionsContentModelArticles extends ContentModelArticles
{
	/**
	 * Get the master query for retrieving a list of articles subject to the model state.
	 *
	 * @return	JDatabaseQuery
	 * @since       1.0.0
	 */
	public function getListQuery()
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
		else if( $emptyItemsID )
		{
			$query->where( $db->quoteName( 'a.id' ) . ' = ' . 1 );
		}
		
		return $query;
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
}
