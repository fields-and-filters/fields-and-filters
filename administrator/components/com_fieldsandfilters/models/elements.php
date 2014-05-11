<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kulka.tomek@gmail.com> -
 */

defined('_JEXEC') or die;

if (!FieldsandfiltersFactory::isVersion())
{
	jimport('joomla.application.component.modellist');
}

/**
 * Methods supporting a list of Fieldsandfilters records.
 *
 * @since       1.1.0
 */
class FieldsandfiltersModelElements extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param    array    An optional associative array of configuration settings.
	 *
	 * @see         JController
	 * @since       1.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'e.id',
				'content_type_id', 'e.content_type_id',
				'item_id', 'e.item_id',
				'ordering', 'e.ordering',
				'state', 'e.state'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since       1.1.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app     = JFactory::getApplication();
		$filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array');

		// If the context is set, assume that stateful lists are used.
		// @deprecated v.1.2 && J3.x
		if (!FieldsandfiltersFactory::isVersion() && $this->context)
		{
			// Receive & set filters
			if ($filters)
			{
				foreach ($filters as $name => $value)
				{
					$this->setState('filter.' . $name, $value);
				}
			}
		}
		else
		{
			$this->state->set('filter.content_type_id', JArrayHelper::getValue($filters, 'content_type_id', 0, 'int'));
		}

		// Load the parameters.
		$params = JComponentHelper::getParams('com_fieldsandfilters');
		$this->setState('params', $params);

		$contentTypeID = (int) $this->state->get('filter.content_type_id');
		if ($contentTypeID && ($extension = FieldsandfiltersFactory::getExtensions()->getExtensionsByTypeIDPivot('content_type_id', $contentTypeID)->get($contentTypeID)))
		{
			// Include the content plugins for the on delete events.
			JPluginHelper::importPlugin('fieldsandfiltersextensions');

			// Trigger the onContentBeforeDelete event.
			$app->triggerEvent('onFieldsandfiltersPopulateState', array(($this->context . '.' . $extension->name), $this->state, &$this->filter_fields));

			$this->setState('filter.extension_name', $extension->name);
		}

		// List state information.
		parent::populateState($this->state->get('list.query.ordering', 'e.item_id'), 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param    string $id A prefix for the store id.
	 *
	 * @return    string        A store id.
	 * @since       1.0.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.content_type_id');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return    JDatabaseQuery
	 * @since       1.0.0
	 */
	protected function getListQuery()
	{
		// get extension, we need him for query
		$extensionName = $this->state->get('filter.extension_name');
		$contentTypeID = (int) $this->state->get('filter.content_type_id');

		if (!$extensionName || !$contentTypeID)
		{
			return false;
		}

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select(
				$this->getState(
					'list.select',
					'e.*'
				)
			)
			->from($db->quoteName('#__fieldsandfilters_elements', 'e'));

		$where = array(
			$db->quoteName('e.content_type_id') . ' = ' . (int) $contentTypeID
		);

		if ($this->state->get('filter.empty', 0))
		{
			$where[] = $db->quoteName('e.content_type_id') . ' IS NULL';
		}

		$query->where('(' . implode(' OR ', $where) . ')');

		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published))
		{
			$query->where('e.state = ' . (int) $published);
		}

		// Include the fieldsandfiltersExtensions plugins for the on PrepareList Query events.
		JPluginHelper::importPlugin('fieldsandfiltersextensions');

		// Trigger the onPrepareListQuery event.
		$result = JFactory::getApplication()->triggerEvent('onFieldsandfiltersPrepareListQuery', array(($this->context . '.' . $extensionName), $query, $this->state));

		if (in_array(false, $result, true))
		{
			$this->setError(JText::sprintf('COM_FIELDSANDFILTERS_DATABASE_ERROR_PREPARE_LIST_QUERY', $extensionName));

			$this->setState('filter.extension_name', null);

			return false;
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'e.item_id');
		$orderDirn = $this->state->get('list.direction', 'ASC');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
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
		return ($this->getState('filter.extension_name') ? parent::getItems() : array());
	}

	/**
	 * Method to get a JPagination object for the data set.
	 *
	 * @return  JPagination  A JPagination object for the data set.
	 * @since       1.0.0
	 */
	public function getPagination()
	{
		if ($this->getState('filter.extension_name'))
		{
			$page = parent::getPagination();
		}
		else
		{
			// Create the pagination object.
			jimport('joomla.html.pagination');
			$limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');
			$page  = new JPagination(0, 0, $limit);
		}

		return $page;
	}

	/**
	 * @since       1.2.0
	 */
	public function getExtensionDir()
	{
		$dir = $this->state->get('list.view.extension.dir', false);

		return ($dir && is_dir($dir) ? $dir : false);
	}
}
