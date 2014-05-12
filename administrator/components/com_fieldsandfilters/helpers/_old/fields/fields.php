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
 * FieldsandfiltersFieldsHelper
 *
 * @package     com_fieldsandfilters
 * @since       1.1.0
 */
class FieldsandfiltersFields extends KextensionsBufferValues
{
	/**
	 * @since       1.2.0
	 **/
	const VALUES_VALUES = 1;

	/**
	 * @since       1.2.0
	 **/
	const VALUES_DATA = 2;

	/**
	 * @since       1.2.0
	 **/
	const VALUES_BOTH = 3;

	/**
	 * An array of names that don't exists
	 *
	 * @var    array
	 * @since  1.10
	 */
	protected $_not = array('__modes', 'fields' => '__notFields');

	/**
	 *
	 * @since       1.1.0
	 */
	protected $_extension_locatnion_default = 'onBeforeRender';

	/**
	 *
	 * @since       1.1.0
	 */
	protected $_valuesModes = array();

	/**
	 *
	 * @since       1.1.0
	 */
	protected $_staticModes = array();

	/**
	 * Temporarily fields ID var
	 *
	 * @since       1.0.0
	 */
	protected $_fieldsID = array();

	/**
	 * Temporarily modes var
	 *
	 * @since       1.0.0
	 */
	protected $_modes = array();

	/**
	 *
	 * @since       1.1.0
	 */
	public function __construct($debug = false)
	{
		parent::__construct($debug);

		$this->_valuesModes = (array) FieldsandfiltersModes::getMode(FieldsandfiltersModes::MODE_FILTER);
		$this->_staticModes = (array) FieldsandfiltersModes::getMode(FieldsandfiltersModes::MODE_STATIC);
	}

	/**
	 *
	 * @since       1.1.0
	 */
	public function getFields($types, $states = null, $values = null, $without = true)
	{
		$this->method = __FUNCTION__;

		$this->_setConfigFields($values, $without);

		return $this->_getBuffer($types, null, $states);
	}

	/**
	 *
	 * @since       1.1.0
	 */
	public function getFieldsByID($types, $ids, $states = null, $values = null, $without = true)
	{
		$this->method = __FUNCTION__;

		$this->_setConfigFields($values, $without);

		$this->config->def('notName', 'fields');

		return $this->_getBuffer($types, $ids, $states);
	}

	/**
	 *
	 * @since       1.1.0
	 */
	public function getFieldsByModeID($types, $ids, $states = null, $values = null, $without = true)
	{
		$this->method = __FUNCTION__;

		$this->_setConfigFields($values, $without);

		return $this->_getBuffer($types, $ids, $states);
	}

	/**
	 *
	 * @since       1.2.0
	 */
	public function getTypeName()
	{
		return 'content_type_id';
	}

	/**
	 *
	 * @since       1.2.0
	 */
	public function getForeignName()
	{
		return 'field_id';
	}

	/**
	 *
	 * @since       1.2.0
	 */
	public function getValuesName()
	{
		if ($this->methodValues == self::VALUES_VALUES)
		{
			return 'values';
		}
		else
		{
			if ($this->methodValues == self::VALUES_DATA)
			{
				return 'data';
			}
		}

		return null;
	}

	/**
	 *
	 * @since       1.1.0
	 */
	protected function _setConfigFields($values = false, $without = true)
	{
		if (!$without)
		{
			$this->config->def('elemntsWithoutValues', $without);
		}

		if ($values)
		{
			switch ($values)
			{
				case self::VALUES_VALUES:
					$this->config->def('getValues', self::VALUES_VALUES);
					break;
				case self::VALUES_DATA:
					$this->config->def('getValues', self::VALUES_DATA);
					break;
				case self::VALUES_BOTH;
					$this->config->def('getValues', array(self::VALUES_VALUES, self::VALUES_DATA));
					break;
			}
		}
	}

	/**
	 *
	 * @since       1.1.0
	 */
	protected function _beforeQuery($type)
	{
		switch ($this->method)
		{
			case 'getFieldsByID':
				parent::_beforeQueryElements($type);
				break;
			case 'getFieldsByModeID':
				// Get content type id from cache
				$data = $this->_getData($type);

				// The difference states between argument states and cache states
				$dataStates = $data->get('__states', array());
				$_states    = array_diff($this->states, $dataStates);

				// The diffrence modes between argument modes and cache modes
				$dataModes = $data->get('__modes', array());
				$_modes    = array_diff($this->elements, $dataModes);

				if (!empty($_modes) || !empty($_states))
				{
					// Add difference states to query varible
					$this->_states += $_states;

					// Add difference modes to query varible
					$this->_modes += $_modes;

					// When the get modes of the need, then add modes to the cache content type, because we don't need them next time
					$data->set('__modes', array_merge($dataModes, $_modes));

					// When the get states of the need, then add states to the cache extenion type, because we don't need them next time
					// $data->set('__states', array_merge($dataStates, $_states));

					// Get elements id from cache, because we don't need get that id's second time from database 
					$this->_notElements = array_merge($this->_notElements, array_keys(get_object_vars($data->get('elements', new stdClass))));

					// Add content type id to query varible
					array_push($this->_types, $type);
				}
				break;
			default:
				parent::_beforeQuery($type);
				break;
		}
	}

	/**
	 *
	 * @since       1.1.0
	 */
	protected function _testQueryVars()
	{
		if ($this->method == 'getFieldsByID')
		{
			return (!empty($this->_types) && !empty($this->elements));
		}

		return parent::_testQueryVars();
	}

	/**
	 *
	 * @since       1.1.0
	 */
	protected function _getQuery()
	{
		// Get db and query
		$query = $this->_db->getQuery(true);

		$query->select('*')
			->from($this->_db->quoteName('#__fieldsandfilters_fields'))
			->where($this->_db->quoteName('state') . ' IN (' . implode(',', $this->_states) . ')') // Fiels where states
			->where($this->_db->quoteName('content_type_id') . ' IN(' . implode(',', $this->_types) . ')'); // Fields where contents type id

		if ($this->method == 'getFieldsByID')
		{
			$query->where($this->_db->quoteName('id') . ' IN (' . implode(',', $this->elements) . ')');
		}
		else
		{
			if ($this->method == 'getFieldsByModeID')
			{
				$query->where($this->_db->quoteName('mode') . ' IN (' . implode(',', $this->_modes) . ')');
			}
		}

		// We no need same elements id
		if (!empty($this->_notElements))
		{
			JArrayHelper::toInteger($this->_notElements);
			$query->where($this->_db->quoteName('id') . ' NOT IN (' . implode(',', $this->_notElements) . ')');
		}

		$query->order($this->_db->quoteName('ordering') . ' ASC');

		/* @deprecated 1.2.0 */
		if (FieldsandfiltersFactory::useOldStructure())
		{
			$query->select(array(
				$this->_db->quoteName('id', 'field_id'),
				$this->_db->quoteName('name', 'field_name'),
				$this->_db->quoteName('alias', 'field_alias'),
				$this->_db->quoteName('type', 'field_type')
			));
		}

		/* @end deprecated 1.2.0 */

		return $query;
	}

	/**
	 *
	 * @since       1.1.0
	 */
	protected function _setData(&$_field)
	{
		$this->_getData($_field->content_type_id)->elements->set($_field->id, $_field);
		$_field->params   = new JRegistry($_field->params);
		$_field->location = (array) $_field->params->get('extension.location', $this->_extension_locatnion_default);

		if (($byID = $this->method == 'getFieldsByID') || $this->method == 'getFieldsByModeID')
		{
			$this->buffer->set($_field->id, $_field);
		}

		if ($byID)
		{
			array_push($this->_fieldsID, $_field->id);
		}
	}

	/**
	 *
	 * @since       1.1.0
	 */
	protected function _afterQuery()
	{
		switch ($this->method)
		{
			case 'getFieldsByID':
				if ($this->_testQueryVars())
				{
					$this->_setNot(array_diff($this->elements, $this->_fieldsID), 'fields');
				}
				break;
			case 'getFieldsByModeID':
				// Get elements from cahce
				while (!empty($this->types))
				{
					$_elements = get_object_vars($this->_getData(array_shift($this->types))->get('elements', new JObject));

					// Add only those elements are suitable states
					while ($_element = current($_elements))
					{
						if (in_array($_element->mode, $this->elements) && in_array($_element->state, $this->states))
						{
							$this->buffer->set($_element->{$this->getPrimaryName()}, $_element);
						}

						next($_elements);
					}
				}
				break;
			default:
				parent::_afterQuery();
				break;
		}
	}

	/**
	 *
	 * @since       1.1.0
	 */
	protected function _getQueryValues()
	{
		$query = $this->_db->getQuery(true);

		$query->select('*')
			->where($this->_db->quoteName('field_id') . ' IN (' . implode(',', $this->_valuesElements) . ')');

		if ($this->methodValues == self::VALUES_VALUES)
		{
			$query->from($this->_db->quoteName('#__fieldsandfilters_field_values'))
				->where($this->_db->quoteName('state') . ' = 1')
				->order($this->_db->quoteName('ordering') . ' ASC');
		}
		elseif ($this->methodValues == self::VALUES_DATA)
		{
			$query->from($this->_db->quoteName('#__fieldsandfilters_data'))
				->where($this->_db->quoteName('element_id') . ' = ' . 0)
				->where($this->_db->quoteName('content_type_id') . ' IN(' . implode(',', $this->_types) . ')');
		}

		return $query;
	}

	/**
	 *
	 * @since       1.1.0
	 */
	protected function _searchValuesElement(&$element)
	{
		$elementName = $this->getPrimaryName();

		if ($this->methodValues == self::VALUES_VALUES)
		{
			$modes = $this->_valuesModes;
		}
		elseif ($this->methodValues == self::VALUES_DATA)
		{
			$modes = $this->_staticModes;
		}
		else
		{
			return false;
		}

		// if element don't have filter_connection, add to arrry
		if (in_array($element->mode, $modes) && !isset($element->{$this->getValuesName()}))
		{
			array_push($this->_valuesElements, $element->$elementName);
		}
		// if we need only elements with filter_connections isn't empty
		elseif (!$this->config->get('elemntsWithoutValues', true) && isset($element->{$this->getValuesName()}))
		{
			if ($this->methodValues == self::VALUES_VALUES)
			{
				$_values = get_object_vars($element->{$this->getValuesName()});
			}
			elseif ($this->methodValues == self::VALUES_DATA)
			{
				$_values = $element->{$this->getValuesName()};
			}

			if (empty($_values))
			{
				unset($this->buffer->{$element->$elementName});
			}

			unset($_values);
		}
	}

	/**
	 *
	 * @since       1.2.0
	 */
	protected function _prepareValuesElement($element)
	{
		if ($this->methodValues == self::VALUES_VALUES)
		{
			parent::_prepareValuesElement($element);
		}
	}

	/**
	 *
	 * @since       1.1.0
	 */
	protected function _addValue(&$_value)
	{
		$element    = $this->buffer->get($_value->{$this->getForeignName()});
		$valuesName = $this->getValuesName();

		if ($this->methodValues == self::VALUES_VALUES && isset($_value->id))
		{
			unset($_value->field_id);

			$element->$valuesName->set($_value->id, $_value);
		}
		elseif ($this->methodValues == self::VALUES_DATA)
		{
			if (isset($_value->field_id) && isset($_value->data) && !isset($element->$valuesName))
			{
				$element->$valuesName = $_value->data;
			}
			elseif (!isset($element->$valuesName))
			{
				$element->$valuesName = '';
			}
		}
	}

	/**
	 * Reset arguments
	 *
	 * @param    boolean $reset reset arguments if you need
	 *
	 * @return      boolean         if is reset
	 * @since       1.0.0
	 **/
	protected function _resetArgs($reset = null)
	{
		$reset = parent::_resetArgs($reset);

		if ($reset)
		{
			if ($this->method == 'getFieldsByID')
			{
				$this->_fieldsID = array();
			}
			elseif ($this->method == 'getFieldsByModeID')
			{
				$this->_modes = array();
			}
		}

		return $reset;
	}

	/** __call method generator methods:
	 * getFieldsPivot( $pivot, $types, $states = null, $values = null, $without = true )
	 * getFieldsColumn( $column, $types, $states = null )
	 *
	 * getFieldsByIDPivot( $pivot, $types, $ids, $states = null, $values = null, $without = true )
	 * getFieldsByIDColumn( $column, $types, $ids, $states = null )
	 *
	 * getFieldsByModeIDPivot( $pivot, $types, $ids, $states = null, $values = null, $without = true )
	 * getFieldsByModeIDColumn( $column, $types, $ids, $states = null )
	 *
	 * @since       1.1.0
	 **/
	protected function _beforeCall($type, $method, $name, &$arguments)
	{
		if ($type == 'Pivot')
		{
			if ($method == 'getFields')
			{
				$values  = isset($arguments[3]) ? $arguments[3] : null;
				$without = isset($arguments[4]) ? $arguments[4] : true;
			}
			else
			{
				$values  = isset($arguments[4]) ? $arguments[4] : null;
				$without = isset($arguments[5]) ? $arguments[5] : true;
			}

			$this->_setConfigFields($values, $without);
		}
	}
}