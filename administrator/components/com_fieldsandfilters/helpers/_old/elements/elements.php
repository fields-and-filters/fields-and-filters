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
 * FieldsandfiltersElements
 *
 * @package     com_fieldsandfilters
 * @since       1.1.0
 */
class FieldsandfiltersElements extends KextensionsBufferValues
{
	/**
	 * @since       1.2.0
	 **/
	const VALUES_CONNECTIONS = 1;

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
	 * @since       1.1.0
	 */
	protected $_not = array('items' => '__notItems', 'elements' => '__notElements');

	/**
	 * Temporarily elements ID var
	 *
	 * @since       1.0.0
	 */
	protected $_elementsID = array();

	/**
	 * Temporarily items ID var
	 *
	 * @since       1.0.0
	 */
	protected $_itemsID = array();

	/**
	 * Temporarily items ID var
	 *
	 * @since       1.0.0
	 */
	protected $_elementsItemsID = array();

	/**
	 *
	 * @since       1.1.0
	 */
	public function getElements($types, $states = null, $values = null, $without = false)
	{
		$this->method = __FUNCTION__;

		$this->_setConfigElements($values, $without);

		return $this->_getBuffer($types, null, $states);
	}

	/**
	 *
	 * @since       1.1.0
	 */
	public function getElementsByID($types, $ids, $states = null, $values = null, $without = false)
	{
		$this->method = __FUNCTION__;

		$this->_setConfigElements($values, $without);

		$this->config->def('notName', 'elements');

		return $this->_getBuffer($types, $ids, $states);
	}

	/**
	 *
	 * @since       1.1.0
	 */
	public function getElementsByItemID($types, $ids, $states = null, $values = null, $without = false)
	{
		$this->method = __FUNCTION__;

		$this->_setConfigElements($values, $without);

		$this->config->def('notName', 'items');

		return $this->_getBuffer($types, $ids, $states);
	}

	/**
	 * Method get element id, return only first element id if exists
	 *
	 * @param    int /array        $extensionTypeID    intiger or array of extensions type id
	 * @param    int /array        $itemID            intiger or array of items id
	 * @param    int /array/null        $states                intiger or array of states
	 *
	 * @return    if exists return int element id, empty return null.
	 * @since    1.0.0
	 */
	public function getElementID($types, $id, $states = null)
	{
		// Get elements
		$elements = (array) $this->getElementsByItemIDColumn('id', $types, $id, $states);
		if (!empty($elements))
		{
			reset($elements);

			return current($elements);
		}

		return null;
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
		return 'element_id';
	}

	/**
	 *
	 * @since       1.2.0
	 */
	public function getValuesName()
	{
		if ($this->methodValues == self::VALUES_CONNECTIONS)
		{
			return 'connections';
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
	protected function _setConfigElements($values = null, $without = true)
	{
		if (!$without)
		{
			$this->config->def('elemntsWithoutValues', $without);
		}

		if ($values)
		{
			switch ($values)
			{
				case self::VALUES_CONNECTIONS:
					$this->config->def('getValues', self::VALUES_CONNECTIONS);
					break;
				case self::VALUES_DATA:
					$this->config->def('getValues', self::VALUES_DATA);
					break;
				case self::VALUES_BOTH:
					$this->config->def('getValues', array(self::VALUES_CONNECTIONS, self::VALUES_DATA));
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
		if ($this->method == 'getElementsByID' || $this->method == 'getElementsByItemID')
		{
			parent::_beforeQueryElements($type);
		}
		else
		{
			parent::_beforeQuery($type);
		}
	}

	/**
	 *
	 * @since       1.1.0
	 */
	protected function _beforeSearchElements($data, $key = 'id')
	{
		if ($this->method == 'getElementsByItemID')
		{
			// Get elements id from elements where is items id
			$this->_elementsItemsID = KextensionsArray::getColumn($data->get('elements', new stdClass), 'item_id', 'id');

            $key = 'item_id';
		}

        return parent::_beforeSearchElements($data, $key);
	}

	/**
	 *
	 * @since       1.1.0
	 */
	protected function _searchElements(&$data, $itemID, &$_itemsID, &$_notItems)
	{
		if ($this->method == 'getElementsByItemID')
		{
			// We take element from cache and this id add to array
			if (($elementID = array_search($itemID, $this->_elementsItemsID)) !== false)
			{
				$primaryName = $this->getPrimaryName();
				$_element    = $data->elements->get($elementID);

				if (in_array($_element->state, $this->states))
				{
					$this->buffer->{$_element->$primaryName} = $_element;
				}

				array_push($_itemsID, $itemID);
			}
			// If argument element id in array ids not exist, add that id to array exist id, because we know that id isn't exist
			elseif (in_array($itemID, $_notItems))
			{
				array_push($_itemsID, $itemID);
			}
		}
		else
		{
			parent::_searchElements($data, $itemID, $_itemsID, $_notItems);
		}
	}

	/**
	 *
	 * @since       1.1.0
	 */
	protected function _testQueryVars()
	{
		if ($this->method == 'getElementsByID' || $this->method == 'getElementsByItemID')
		{
			return (!empty($this->_types) && !empty($this->elements));

		}
		else
		{
			return parent::_testQueryVars();
		}
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
			->from($this->_db->quoteName('#__fieldsandfilters_elements'))
			->where($this->_db->quoteName('state') . ' IN (' . implode(',', $this->_states) . ')') // Elements where states
			->where($this->_db->quoteName('content_type_id') . ' IN(' . implode(',', $this->_types) . ')'); // Elements where extensions type id

		if ($this->method == 'getElementsByID')
		{
			$query->where($this->_db->quoteName('id') . ' IN (' . implode(',', $this->elements) . ')');
		}
		elseif ($this->method == 'getElementsByItemID')
		{
			$query->where($this->_db->quoteName('item_id') . ' IN (' . implode(',', $this->elements) . ')');
		}

		// We no need same elements id
		if (!empty($this->_notElements))
		{
			JArrayHelper::toInteger($this->_notElements);
			$query->where($this->_db->quoteName('id') . ' NOT IN (' . implode(',', $this->_notElements) . ')');
		}

		$query->order($this->_db->quoteName('ordering') . ' ASC');

		return $query;
	}

	/**
	 *
	 * @since       1.1.0
	 */
	protected function _setData(&$_element)
	{
		if (($byID = $this->method == 'getElementsByID') || ($byItem = $this->method == 'getElementsByItemID'))
		{
			$key = $_element->{$this->getPrimaryName()};

			$this->_getData($_element->content_type_id)->elements->set($key, $_element);
			$this->buffer->$key = $_element;

			if ($byID)
			{
				array_push($this->_elementsID, $key);
			}
			elseif ($byItem)
			{
				array_push($this->_itemsID, $_element->item_id);
			}
		}
		else
		{
			parent::_setData($_element);
		}
	}

	/**
	 *
	 * @since       1.1.0
	 */
	protected function _afterQuery()
	{
		if ((($byID = $this->method == 'getElementsByID') || ($byItem = $this->method == 'getElementsByItemID')) && $this->_testQueryVars())
		{
			if ($byID)
			{
				$this->_setNot(array_diff($this->elements, $this->_elementsID), 'elements');
			}
			elseif ($byItem)
			{
				$this->_setNot(array_diff($this->elements, $this->_itemsID), 'items');
			}
		}
		else
		{
			parent::_afterQuery();
		}
	}

	/**
	 *
	 * @since       1.1.0
	 */
	protected function _getQueryValues()
	{
		// Get db and query
		$query = $this->_db->getQuery(true);

		$query->select('*');

		if ($this->methodValues == self::VALUES_DATA)
		{
			$query->from($this->_db->quoteName('#__fieldsandfilters_data'));
		}
		elseif ($this->methodValues == self::VALUES_CONNECTIONS)
		{
			$query->from($this->_db->quoteName('#__fieldsandfilters_connections'));
		}

		$query->where($this->_db->quoteName('element_id') . ' IN (' . implode(',', $this->_valuesElements) . ')');

		return $query;
	}

	/**
	 *
	 * @since       1.1.0
	 */
	protected function _addValue(&$_value)
	{
		$element    = $this->buffer->get($_value->{$this->getForeignName()});
		$valuesName = $this->getValuesName();

		if (!(isset($element->$valuesName) && $element->$valuesName instanceof JObject))
		{
			$element->$valuesName = new JObject();
		}

		if ($this->methodValues == self::VALUES_DATA && isset($_value->field_id) && isset($_value->data) && !isset($element->$valuesName->{$_value->field_id}))
		{
			$element->$valuesName->set($_value->field_id, $_value->data);
		}
		elseif ($this->methodValues == self::VALUES_CONNECTIONS && isset($_value->field_id) && isset($_value->field_value_id))
		{
			if (!isset($element->$valuesName->{$_value->field_id}))
			{
				$element->$valuesName->set($_value->field_id, array());
			}

			if (is_array($_value->field_value_id))
			{
				$element->$valuesName->set($_value->field_id, array_merge($element->$valuesName->{$_value->field_id}, $_value->field_value_id));
			}
			else
			{
				array_push($element->$valuesName->{$_value->field_id}, $_value->field_value_id);
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
			if ($this->method == 'getElementsByID')
			{
				$this->_elementsID = array();
			}
			elseif ($this->method == 'getElementsByItemID')
			{
				$this->_itemsID         = array();
				$this->_elementsItemsID = array();
			}
		}

		return $reset;
	}

	/** __call method generator methods:
	 * getElementsPivot( $pivot, $types, $states = null, $values = null, $without = true )
	 * getElementsColumn( $column, $types, $states = null )
	 * getElementsByIDPivot( $pivot, $types, $ids, $states = null, $values = null, $without = true )
	 * getElementsByIDColumn( $column, $types, $states = null )
	 * getElementsByItemIDPivot( $pivot, $types, $ids, $states = null, $values = null, $without = true )
	 * getElementsByItemIDColumn( $column, $types, $states = null )
	 *
	 * @since       1.1.0
	 **/
	protected function _beforeCall($type, $method, $name, &$arguments)
	{
		if ($type == 'Pivot')
		{
			if ($method == 'getFilters')
			{
				$values  = isset($arguments[3]) ? $arguments[3] : null;
				$without = isset($arguments[4]) ? $arguments[4] : true;
			}
			else
			{
				$values  = isset($arguments[4]) ? $arguments[4] : null;
				$without = isset($arguments[5]) ? $arguments[5] : true;
			}

			$this->_setConfigElements($values, $without);
		}
	}
}