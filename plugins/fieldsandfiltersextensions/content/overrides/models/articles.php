<?php
/**
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_extension.content
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

JLoader::import('com_content.models.articles', JPATH_SITE . '/components');

/**
 * @since       1.0.0
 */
class plgFieldsandfiltersExtensionsContentModelArticles extends ContentModelArticles
{
	/**
	 * Context string for the model type.  This is used to handle uniqueness
	 * when dealing with the getStoreId() method and caching data structures.
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $context = 'com_content.articles';

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string $ordering  An optional ordering field.
	 * @param   string $direction An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState($ordering = 'ordering', $direction = 'ASC')
	{
		parent::populateState($ordering, $direction);

		$params = JFactory::getApplication()->getParams('com_content');
		$this->setState('params', $params);

		// Process show_noauth parameter
		$this->setState('filter.access', !$params->get('show_noauth'));
	}

	/**
	 * Get the master query for retrieving a list of articles subject to the model state.
	 *
	 * @return    JDatabaseQuery
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

        // Only for Joomla 3.x
        if (FieldsandfiltersFactory::isVersion())
        {
            $query->clear('limit');
        }

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
}
