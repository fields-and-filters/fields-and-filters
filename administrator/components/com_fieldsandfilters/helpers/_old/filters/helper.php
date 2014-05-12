<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access
defined('_JEXEC') or die;

/**
 * FieldsandfiltersFiltersHelper
 *
 * @since       1.0.0
 */
class FieldsandfiltersFiltersHelper
{
	/**
	 * @since       1.0.0
	 */
	protected static $_items = array();

	/**
	 * @since       1.0.0
	 */
	protected static $_counts = array();

	/**
	 * @since       1.1.0
	 */
	protected static $filters;

	/**
	 * @since       1.1.0
	 */
	protected static $types;

	/**
	 * @since       1.0.0
	 */
	public static function getFiltersValuesCount($types, $filters = null, $items = null, $states = null)
	{
		$hash = md5(serialize(func_get_args()));

		if (!isset(self::$_counts[$hash]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			self::_checkArg($types);

			try
			{
				if (empty($types))
				{
					throw new RuntimeException('Empty types');
				}

				$query->select(array(
					$db->quoteName('c.field_value_id'),
					'COUNT(' . $db->quoteName('c.field_value_id') . ') AS ' . $db->quoteName('field_value_count'),
				))
					->from($db->quoteName('#__fieldsandfilters_connections', 'c'))
					->where($db->quoteName('c.content_type_id') . ' IN(' . implode(',', $types) . ')');

				if (!is_null($filters))
				{
					self::_checkArg($filters);

					if (!empty($filters))
					{
						$query->where($db->quoteName('c.field_id') . ' IN(' . implode(',', $filters) . ')');
					}
				}

				$query->join('INNER', $db->quoteName('#__fieldsandfilters_elements', 'e') . ' ON ' . $db->quoteName('e.id') . '=' . $db->quoteName('c.element_id'));

				if (!is_null($items))
				{
					self::_checkArg($items);

					if (!empty($items))
					{
						$query->where($db->quoteName('e.item_id') . ' IN(' . implode(',', $items) . ')');
					}
				}

				if (empty($items))
				{
					$states = is_null($states) ? array(1) : $states;
					self::_checkArg($states);

					$query->where($db->quoteName('e.state') . ' IN (' . implode(',', $states) . ')');
				}

				$query->group($db->quoteName('c.field_value_id'));

				if ($result = $db->setQuery($query)->loadObjectList())
				{
					$counts = array();

					while ($count = array_shift($result))
					{
						$counts[$count->field_value_id] = $count->field_value_count;
					}

					$result = $counts;

					unset($counts);
				}
			} catch (RuntimeException $e)
			{
				JLog::add(__METHOD__ . ': ' . $e->getMessage(), JLog::ERROR, 'Fieldsandfilters-FiltersHelper');
				$result = false;
			}

			self::$_counts[$hash] = $result;
		}

		return self::$_counts[$hash];
	}

	/**
	 * @since       1.1.0
	 */
	public static function getItemsIDByFilters($types, $filters = null, $states = null, $betweenFilters = 'AND', $betweenValues = 'OR')
	{
		$hash = md5(serialize(func_get_args()));

		if (!isset(self::$_items[$hash]))
		{
			// Get the database object.
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			self::_checkArg($types);

			$query->select('DISTINCT ' . $db->quoteName('e.item_id'))
				->from($db->quoteName('#__fieldsandfilters_elements', 'e'));
			try
			{
				if (empty($types))
				{
					throw new RuntimeException('Empty types');
				}

				self::$types = $types;

				if (!is_null($filters))
				{
					$filters = (array) $filters;
					if (!empty($filters))
					{
						self::$filters = $filters;

						switch (strtoupper($betweenFilters . '-' . $betweenValues))
						{

							case 'OR-OR':
								self::_prepareQueyrBetweenFiltersOrValuesOr($query);
								break;
							case 'AND-OR':
								self::_prepareQueyrBetweenFiltersAndValuesOr($query);
								break;
							case 'OR-AND':
								self::_prepareQueyrBetweenFiltersORValuesAND($query);
								break;
							case 'AND-AND':
								self::_prepareQueyrBetweenFiltersANDValuesAND($query);
								break;
							default:
								throw new RuntimeException('Not exists comparation method');
								break;
						}
					}

				}

				$states = is_null($states) ? array(1) : $states;
				self::_checkArg($states);

				$query->where($db->quoteName('e.state') . ' IN (' . implode(',', $states) . ')');

				$query->where($db->quoteName('e.content_type_id') . ' IN(' . implode(',', self::$types) . ')');

				$result = $db->setQuery($query)->loadColumn();

				// echo $query->dump();
				// exit;

				self::$types   = null;
				self::$filters = null;

			} catch (RuntimeException $e)
			{
				// echo $e->getMessage();
				// exit;
				JLog::add(__METHOD__ . ': ' . $e->getMessage(), JLog::ERROR, 'Fieldsandfilters-FiltersHelper');
				$result = false;
			}

			self::$_items[$hash] = !empty($result) ? self::getSimpleItemsID(false, $result) : self::getSimpleItemsID();
		}

		return self::$_items[$hash];
	}

	/**
	 * @since       1.1.0
	 */
	public static function getSimpleItemsID($empty = true, $itemsID = array())
	{
		return new JObject(array('empty' => $empty, 'itemsID' => (array) $itemsID));
	}

	/**
	 * @since       1.1.0
	 */
	protected static function _prepareQueyrBetweenFiltersOrValuesOr($query)
	{
		$db = JFactory::getDbo();

		if (JArrayHelper::isAssociative(self::$filters))
		{
			if (count(self::$filters) > 1)
			{
				$unions   = array();
				$subQuery = $db->getQuery(true);
				$subQuery->select($db->quoteName('c.element_id'))
					->from($db->quoteName('#__fieldsandfilters_connections', 'c'));

				foreach (self::$filters AS $filter => &$filter_values)
				{
					$filter = (int) $filter;

					self::_checkArg($filter_values);

					if (!$filter || empty($filter_values))
					{
						continue;
					}

					$subQuery->clear('where');
					$subQuery->where($db->quoteName('c.field_id') . ' = ' . $filter);
					$subQuery->where($db->quoteName('c.field_value_id') . ' IN(' . implode(',', $filter_values) . ')');
					$subQuery->where($db->quoteName('c.content_type_id') . ' IN(' . implode(',', self::$types) . ')');
					$unions[] = $subQuery->__toString();
				}

				if (count($unions))
				{
					$subQuery = '(' . implode(PHP_EOL . ') UNION DISTINCT (', $unions) . PHP_EOL . ')';
					$query->join('INNER', '(' . $subQuery . ') AS `c` ON ' . $db->quoteName('c.element_id') . ' = ' . $db->quoteName('e.id'));
				}

			}
			else
			{
				self::_prepareQueyrFilterAssociativeArray($query);
			}
		}
		else
		{
			self::_prepareQueyrFilterArray($query);
		}
	}

	/**
	 * @since       1.1.0
	 */
	protected static function _prepareQueyrBetweenFiltersAndValuesOr($query)
	{
		$db = JFactory::getDbo();

		if (JArrayHelper::isAssociative(self::$filters))
		{
			if (count(self::$filters) > 1)
			{
				foreach (self::$filters AS $filter => &$filter_values)
				{
					$filter = (int) $filter;

					self::_checkArg($filter_values);

					if (!$filter || empty($filter_values))
					{
						continue;
					}

					$alias = 'c' . $filter;
					$and   = array(
						$db->quoteName($alias . '.element_id') . ' = ' . $db->quoteName('e.id'),
						$db->quoteName($alias . '.field_id') . ' = ' . $filter,
						$db->quoteName($alias . '.field_value_id') . ' IN(' . implode(',', $filter_values) . ')'
					);

					$query->join('INNER', $db->quoteName('#__fieldsandfilters_connections', $alias) . ' ON ' . implode(' AND ', $and));

					unset($and);
				}
			}
			else
			{
				self::_prepareQueyrFilterAssociativeArray($query);
			}
		}
		else
		{
			self::_prepareQueyrFilterArray($query);
		}
	}

	/**
	 * @since       1.1.0
	 */
	protected static function _prepareQueyrFilterAssociativeArray($query)
	{
		$db = JFactory::getDbo();

		$filter        = (int) key(self::$filters);
		$filter_values = current(self::$filters);

		self::_checkArg($filter_values);

		if ($filter && !empty($filter_values))
		{
			$query->join('INNER', $db->quoteName('#__fieldsandfilters_connections', 'c') . ' ON ' . $db->quoteName('c.element_id') . ' = ' . $db->quoteName('e.id'))
				->where($db->quoteName('c.field_id') . ' = ' . $filter)
				->where($db->quoteName('c.field_value_id') . ' IN(' . implode(',', $filter_values) . ')');
		}
		else
		{
			self::$filters = null;
		}
	}

	/**
	 * @since       1.1.0
	 */
	protected static function _prepareQueyrFilterArray($query)
	{
		$db = JFactory::getDbo();

		self::_checkArg(self::$filters);
		if (!empty(self::$filters))
		{
			$query->join('INNER', $db->quoteName('#__fieldsandfilters_connections', 'c') . ' ON ' . $db->quoteName('c.element_id') . ' = ' . $db->quoteName('e.id'))
				->where($db->quoteName('c.field_id') . ' IN(' . implode(',', self::$filters) . ')');
		}
	}

	/**
	 * @since       1.1.0
	 */
	protected static function _prepareQueyrBetweenFiltersORValuesAND($query)
	{
		$db = JFactory::getDbo();

		if (JArrayHelper::isAssociative(self::$filters))
		{
			if (count(self::$filters) > 1)
			{
				$unions   = array();
				$subQuery = $db->getQuery(true);
				$subQuery->select($db->quoteName('c.element_id'))
					->from($db->quoteName('#__fieldsandfilters_connections', 'c'));

				foreach (self::$filters AS $filter => &$filter_values)
				{
					$filter = (int) $filter;

					self::_checkArg($filter_values);

					if (!$filter || empty($filter_values))
					{
						continue;
					}
					$subQuery->clear('where');
					$subQuery->clear('join');

					$where = false;
					while ($value = array_shift($filter_values))
					{
						if ($where)
						{
							$where = true;
							$subQuery->where($db->quoteName('c.field_id') . ' = ' . $filter);
							$subQuery->where($db->quoteName('c.field_value_id') . ' = ' . $value);
							$subQuery->where($db->quoteName('c.content_type_id') . ' IN(' . implode(',', self::$types) . ')');
						}
						else
						{
							$alias = 'c' . $value;
							$and   = array(
								$db->quoteName($alias . '.element_id') . ' = ' . $db->quoteName('c.element_id'),
								$db->quoteName($alias . '.field_id') . ' = ' . $filter,
								$db->quoteName($alias . '.field_value_id') . ' = ' . $value
							);

							$subQuery->join('INNER', $db->quoteName('#__fieldsandfilters_connections', $alias) . ' ON ' . implode(' AND ', $and));

							unset($and);
						}
					}

					$unions[] = $subQuery->__toString();
				}

				if (count($unions))
				{
					$subQuery = '(' . implode(PHP_EOL . ') UNION DISTINCT (', $unions) . PHP_EOL . ')';
					$query->join('INNER', '(' . $subQuery . ') AS `c` ON ' . $db->quoteName('c.element_id') . ' = ' . $db->quoteName('e.id'));
				}

			}

			if (count(self::$filters) > 1)
			{
				foreach (self::$filters AS $filter => &$filter_values)
				{
					$filter = (int) $filter;

					self::_checkArg($filter_values);

					if (!$filter || empty($filter_values))
					{
						continue;
					}

					while ($value = array_shift($filter_values))
					{
						$alias = 'c' . $value;
						$and   = array(
							$db->quoteName($alias . '.element_id') . ' = ' . $db->quoteName('e.id'),
							$db->quoteName($alias . '.field_id') . ' = ' . $filter,
							$db->quoteName($alias . '.field_value_id') . ' = ' . $value
						);

						$query->join('INNER', $db->quoteName('#__fieldsandfilters_connections', $alias) . ' ON ' . implode(' AND ', $and));

						unset($and);
					}
				}
			}
			else
			{
				self::_prepareQueyrValueAssociativeArray($query);
			}
		}
		else
		{
			self::_prepareQueyrFilterArray($query);
		}
	}

	/**
	 * @since       1.1.0
	 */
	protected static function _prepareQueyrBetweenFiltersANDValuesAND($query)
	{
		$db = JFactory::getDbo();

		if (JArrayHelper::isAssociative(self::$filters))
		{
			if (count(self::$filters) > 1)
			{
				foreach (self::$filters AS $filter => &$filter_values)
				{
					$filter = (int) $filter;

					self::_checkArg($filter_values);

					if (!$filter || empty($filter_values))
					{
						continue;
					}

					while ($value = array_shift($filter_values))
					{
						$alias = 'c' . $value;
						$and   = array(
							$db->quoteName($alias . '.element_id') . ' = ' . $db->quoteName('e.id'),
							$db->quoteName($alias . '.field_id') . ' = ' . $filter,
							$db->quoteName($alias . '.field_value_id') . ' = ' . $value
						);

						$query->join('INNER', $db->quoteName('#__fieldsandfilters_connections', $alias) . ' ON ' . implode(' AND ', $and));

						unset($and);
					}
				}
			}
			else
			{
				self::_prepareQueyrValueAssociativeArray($query);
			}
		}
		else
		{
			self::_prepareQueyrFilterArray($query);
		}
	}

	/**
	 * @since       1.1.0
	 */
	protected static function _prepareQueyrValueAssociativeArray($query)
	{
		$db = JFactory::getDbo();

		$filter        = (int) key(self::$filters);
		$filter_values = current(self::$filters);

		self::_checkArg($filter_values);

		if ($filter && !empty($filter_values))
		{
			while ($value = array_shift($filter_values))
			{
				$alias = 'c' . $value;
				$and   = array(
					$db->quoteName($alias . '.element_id') . ' = ' . $db->quoteName('e.id'),
					$db->quoteName($alias . '.field_id') . ' = ' . $filter,
					$db->quoteName($alias . '.field_value_id') . ' = ' . $value
				);

				$query->join('INNER', $db->quoteName('#__fieldsandfilters_connections', $alias) . ' ON ' . implode(' AND ', $and));

				unset($and);
			}
		}
		else
		{
			self::$filters = null;
		}
	}

	/**
	 * @since       1.1.0
	 */
	protected static function _checkArg(&$arg)
	{
		$arg = array_unique((array) $arg);
		JArrayHelper::toInteger($arg);
	}
}