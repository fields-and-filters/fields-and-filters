<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::import('com_content.models.archive', JPATH_SITE . '/components');

/**
 * @since       1.0.0
 */
class plgFieldsandfiltersExtensionsContentModelArchive extends ContentModelArchive
{
	/**
	 * Context string for the model type.  This is used to handle uniqueness
	 * when dealing with the getStoreId() method and caching data structures.
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $context = 'com_content.archive';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);

		$params = JFactory::getApplication()->getParams('com_content');
		$this->setState('params', $params);

		// Process show_noauth parameter
		$this->setState('filter.access', !$params->get('show_noauth'));

		// Get list limit
		$app    = JFactory::getApplication();
		$itemid = $app->input->get('Itemid', 0, 'int');
		$limit  = $app->getUserStateFromRequest('com_content.archive.list' . $itemid . '.limit', 'limit', $params->get('display_num'), 'uint');
		$this->setState('list.limit', $limit);
	}

	/**
	 * @return  JDatabaseQuery
     *
     * Change Access level from `protected` to `public` for Joomla! 2.5.x. In Joomla! 3.x must be `protected`
	 */
	public function getListQuery()
	{
		// Create a new query object.
		$query = parent::getListQuery();

		// Filter Fieldsandfilters itemsID
		$itemsID      = (array) $this->getState('fieldsandfilters.itemsID');
		$emptyItemsID = $this->setState('fieldsandfilters.emptyItemsID', false);

		if (!empty($itemsID) && !$emptyItemsID)
		{
			JArrayHelper::toInteger($itemsID);
			$query->where($this->getDbo()->quoteName('a.id') . ' IN( ' . implode(',', $itemsID) . ')');
		}

        if ($this->getState('fieldsandfilters.random.selected', false))
        {
            $query->clear('order');
            $query->order('RAND() ASC');

            if ($this->getState('fieldsandfilters.random.limit'))
            {
                $this->setState('list.limit', $this->getState('fieldsandfilters.random.limit', 0));
            }
        }

        return $query;
    }

    /**
     * @since       1.2.5
     */
    public function getTotal()
    {
        if ($this->getState('fieldsandfilters.random.selected', false))
        {
            return $this->getState('list.limit');
        }

        return parent::getTotal();
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
			$limit      = $app->input->get('limit', $params->get('display_num', 20), 'uint');
			$limitstart = $app->input->get('limitstart', 0, 'uint');

			$query = $this->_buildQuery();

			$this->_data = $this->_getList($query, $limitstart, $limit);
		}

		return $this->_data;
	}

	/**
	 * @since       1.0.0
	 */
	public function getItemsID()
	{
		// Get a storage key.
		$store = $this->getStoreId('getItemsID');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Load the list items ID.
		$query = clone $this->_getListQuery();
		$query->clear('select');
		$query->clear('order');
		$query->clear('group');

		$query->select('DISTINCT ' . $this->_db->quoteName('a.id'));
		$this->_db->setQuery($query);

		if (!($itemsID = $this->_db->loadColumn()))
		{
			$itemsID = array();
		}

		$this->setState('fieldsandfilters.itemsID', $itemsID);

		// Add the items to the internal cache.
		$this->cache[$store] = $itemsID;

		return $this->cache[$store];

	}

	/**
	 * Returns a record count for the query
	 *
	 * @param   string $query The query.
	 *
	 * @return  integer  Number of rows for query
	 *
	 * @since       1.0.0
	 */
	protected function _getListCount($query)
	{
		$rows = count($this->getItemsID());

		return $rows;
	}

	/**
	 * @since       1.0.0
	 */
	public function getContentItemsID()
	{
		$limit   = $this->getState('list.limit');
		$itemsID = array();

		if ($limit >= 0)
		{
			$itemsID = $this->getItemsID();
		}

		return $itemsID;
	}
}
