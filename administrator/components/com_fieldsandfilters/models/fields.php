<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

if (!FieldsandfiltersFactory::isVersion())
{
	jimport('joomla.application.component.modellist');
}

/**
 * Methods supporting a list of Fieldsandfilters records.
 *
 * @since       1.0.0
 */
class FieldsandfiltersModelFields extends JModelList
{

	/**
	 * Constructor.
	 *
	 * @param    array    An optional associative array of configuration settings.
	 *
	 * @see         JController
	 *
	 * @since       1.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'f.id',
				'name', 'f.name',
				'alias', 'f.alias',
				'type', 'f.type',
				'content_type_id', 'f.content_type_id',
				'mode', 'f.mode',
				'description', 'f.description',
				'ordering', 'f.ordering',
				'state', 'f.state',
				'required', 'f.required',
				'access', 'f.access',
				'language', 'f.language',
				'params', 'f.params',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since       1.0.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// If the context is set, assume that stateful lists are used.
		// @deprecated v.1.2 && J3.x
		if (!FieldsandfiltersFactory::isVersion() && $this->context)
		{
			$app = JFactory::getApplication();

			// Receive & set filters
			if ($filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array'))
			{
				foreach ($filters as $name => $value)
				{
					$this->setState('filter.' . $name, $value);
				}
			}
		}

		// Load the parameters.
		$params = JComponentHelper::getParams('com_fieldsandfilters');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('f.name', 'asc');
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
	 *
	 * @since       1.0.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.content_type_id');
		$id .= ':' . $this->getState('filter.type');
		$id .= ':' . $this->getState('filter.access');

		// $id.= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return    JDatabaseQuery
	 *
	 * @since       1.0.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'f.*'
			)
		);
		$query->from($db->quoteName('#__fieldsandfilters_fields', 'f'));

		/*
		// Join over the language
		$query->select($db->quoteName('l.title', 'language_title'));
		$query->join('LEFT', $db->quoteName('#__languages', 'l').' ON ' . $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('f.language'));
		
		// Join over the user field 'access'
		$query->select($db->quoteName('u.name', 'access'));
		$query->join('LEFT', $db->quoteName('#__users' , 'u') . ' ON ' . $db->quoteName('u.id') . ' =  ' .$db->quoteName('f.access'));
		*/

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where($db->quoteName('f.id') . ' = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search, true) . '%');

				$where = array(
					($db->quoteName('f.name') . ' LIKE ' . $search),
					($db->quoteName('f.alias') . ' LIKE ' . $search)
				);

				$query->where('(' . implode(' OR ', $where) . ')');
			}
		}

		// Filter by published state
		$state = $this->getState('filter.state');
		if (is_numeric($state))
		{
			$query->where($db->quoteName('f.state') . ' = ' . (int) $state);
		}

		// Filter by content type id
		$contentTypeID = $this->getState('filter.content_type_id');
		if (is_numeric($contentTypeID))
		{
			$query->where($db->quoteName('f.content_type_id') . ' = ' . (int) $contentTypeID);
		}

		// Filter on the type.
		if ($type = $this->getState('filter.type'))
		{
			$query->where($db->quoteName('f.type') . ' = ' . $db->quote($type));
		}

		/*
		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$query->where($db->quoteName('f.access') . ' = ' . (int) $access);
		}
		
		// Filter on the language.
		if ($language = $this->getState('filter.language'))
		{
			$query->where($db->quoteName('f.language') . ' = ' . $db->quote($language));
		}
		*/

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'f.name');
		$orderDirn = $this->state->get('list.direction', 'ASC');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}
}
