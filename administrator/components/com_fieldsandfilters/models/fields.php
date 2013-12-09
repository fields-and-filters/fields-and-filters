<?php
/**
 * @version     1.1.1
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
	 * @see        JController
	 * 
	 * @since       1.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
						'field_id', 		'f.field_id',
						'field_name', 		'f.field_name',
						'field_alias', 		'f.field_alias',
						'field_type', 		'f.field_type',
						'extension_type_id', 	'f.extension_type_id',
						'mode', 		'f.mode',
						'description', 		'f.description',
						'ordering', 		'f.ordering',
						'state', 		'f.state',
						'required', 		'f.required',
						'access', 		'f.access',
						'language', 		'f.language',
						'params', 		'f.params',
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
		// Initialise variables.
		$app = JFactory::getApplication('administrator');
		
		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		
		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $published);
		
		$extensionTypeId = $app->getUserStateFromRequest($this->context . '.filter.extension_type_id', 'filter_extension_type_id', '', 'string');
		$this->setState('filter.extension_type_id', $extensionTypeId);
		
		$type = $app->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', '', 'string');
		$this->setState('filter.type', $type);
		
		$access = $app->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '', 'string');
		$this->setState('filter.access', $access);
		
		$language = $app->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '', 'string');
		$this->setState('filter.language', $language);
		
		// Load the parameters.
		$params = JComponentHelper::getParams('com_fieldsandfilters');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('f.field_name', 'asc');
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
	 *
	 * @since       1.0.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id.= ':' . $this->getState('filter.search');
		$id.= ':' . $this->getState('filter.state');
		$id.= ':' . $this->getState('filter.extension_type_id');
		$id.= ':' . $this->getState('filter.type');
		$id.= ':' . $this->getState('filter.access');
		$id.= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 *
	 * @since       1.0.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

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
				$query->where($db->quoteName('f.field_id') . ' = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				
				$where = array(
						($db->quoteName('f.field_name') . ' LIKE ' . $search),
						($db->quoteName('f.field_alias') . ' LIKE ' . $search)
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
		
		// Filter by extension type id
		$extensionTypeID = $this->getState('filter.extension_type_id');
		if (is_numeric($extensionTypeID))
		{
			$query->where($db->quoteName('f.extension_type_id') . ' = ' . (int) $extensionTypeID);
		}
		
		// Filter on the type.
		if ($type = $this->getState('filter.type'))
		{
			$query->where($db->quoteName('f.field_type') . ' = ' . $db->quote($type));
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
		$orderCol	= $this->state->get('list.ordering', 'f.name');
		$orderDirn	= $this->state->get('list.direction', 'ASC');
		
		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}
}
