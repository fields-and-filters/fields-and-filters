<?php
/**
 * @package     lib_kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access
defined('JPATH_PLATFORM') or die;

/**
 * KextensionsBuffer.
 *
 * @since       1.0.0
 */
abstract class KextensionsBuffer extends KextensionsBufferCore implements KextensionsBufferInterfaceBuffer
{
	/**
	 * Temporarily states var
	 *
	 * @since       1.0.0
	 */
	protected $_states = array();

	/**
	 * Temporarily types var
	 *
	 * @since       1.0.0
	 */
	protected $_types = array();

	/**
	 * Temporarily not elements var
	 *
	 * @since       1.0.0
	 */
	protected $_notElements = array();

	/**
	 *
	 * @since       1.0.0
	 */
	public function getPrimaryName()
	{
		return 'id';
	}

	/**
	 *
	 * @since       1.0.0
	 */
	public function getStateName()
	{
		return 'state';
	}

	/**
	 *
	 * @since       1.0.0
	 */
	protected function _searchData()
	{
		while (($type = current($this->types)) !== false)
		{
			$this->_beforeQuery($type);

			next($this->types);
		}
	}

	/**
	 *
	 * @since       1.0.0
	 */
	protected function _beforeQuery($type)
	{
		// Get extension type id from cache
		$data = $this->_getData($type);

		// The difference states between argument states and cache states
		$dataStates = $data->get('__states', array());
		$_states    = array_diff($this->states, $dataStates);

		if (!empty($_states))
		{
			// Add difference states to query varible
			$this->_states += $_states;

			// When the get states of the need, then add states to the cache extenion type, because we don't need them next time
			$data->set('__states', array_merge($dataStates, $_states));

			// Get elements id from cache, because we don't need get that id's second time from database
			$this->_notElements = array_merge($this->_notElements, array_keys(get_object_vars($data->get('elements', new stdClass))));

			// Add extension type id to query varible
			array_push($this->_types, $type);

			$this->_unsetNot($type, $_states);

		}
	}

	/**
	 *
	 * @since       1.0.0
	 */
	protected function _beforeQueryElements($type)
	{
		if (($emptyEl = empty($this->elements)) && ($emptyNotEl = empty($this->notElements)))
		{
			return;
		}
		elseif ($emptyEl)
		{
			$this->elements = $this->notElements;
		}
		elseif (!(isset($emptyNotEl) ? $emptyNotEl : empty($this->notElements)))
		{
			$this->elements = array_unique(array_merge($this->elements, $this->notElements));
		}

		// Get extension type id from cache
		$data = $this->_getData($type);

		// The difference states between argument states and cache states
		$_states = array_diff($this->states, $data->get('__states', array()));

		// Get elements id form cache that don't exist in database
		$notName = $this->config->get('notName');
		if (!($notName || isset($this->_not[$notName])))
		{
			reset($this->_not);
			$notName = key($this->_not);
		}

		$_notElements = $data->get($this->_not[$notName], array());
		$_notElements = KextensionsArray::fromArray($_notElements, $_states);

		$_elementsID = array();

		// [TODO] change this code
        $elements = array_diff($this->elements, $this->_beforeSearchElements($data));

        if (empty($elements)) {
            return;
        }

		foreach ($elements AS $elementID)
		{
			$this->_searchElements($data, $elementID, $_elementsID, $_notElements);
		}

		// We need only isn't exists elements id
		$elements = array_values(array_diff($this->elements, $_elementsID));

		if (!empty($_states) && !empty($elements))
		{
			// Add difference states and extension type id to query varibles.
			$this->_states += $_states;
			array_push($this->_types, $type);
		}

		unset($_notElements, $_elementsID);
	}

	/**
	 *
	 * @since       1.0.0
	 */
	protected function _beforeSearchElements($data, $key = 'id')
	{
        $elements = array();

        foreach ($this->_data AS $_data) {
            if ($_data == $data) {
                continue;
            }

            $elements = array_merge($elements, KextensionsArray::getColumn($_data->get('elements', new stdClass), 'item_id', 'id'));
        }

        return $elements;
	}

	/**
	 *
	 * @since       1.0.0
	 */
	protected function _searchElements(&$data, $elementID, &$_elementsID, &$_notElements)
	{
		// We take element from cache and this id add to array
		if ($_element = $data->elements->get($elementID))
		{
			if (in_array($_element->{$this->getStateName()}, $this->states))
			{
				$this->buffer->set($_element->{$this->getPrimaryName()}, $_element);
			}

			if (($key = array_search($elementID, $this->elements)) !== false)
			{
				unset($this->elements[$key]);
			}

		}
		// If argument element id in array ids not exist, add that id to array exist id, because we know that id isn't exist
		elseif (in_array($elementID, $_notElements))
		{
			if (!in_array($elementID, $this->notElements))
			{
				array_push($this->notElements, $elementID);
			}

			array_push($_elementsID, $elementID);
		}
	}

	/**
	 *
	 * @since       1.0.0
	 */
	protected function _testQueryVars()
	{
		return (!empty($this->_types));
	}

	/**
	 *
	 * @since       1.0.0
	 */
	protected function _getQuery()
	{
		$query = $this->_db->getQuery(true);

		return $query;
	}

	/**
	 *
	 * @since       1.0.0
	 */
	protected function _setData(&$_element)
	{
		$this->_getData($_element->{$this->getTypeName()})->elements->set($_element->{$this->getPrimaryName()}, $_element);
	}

	/**
	 *
	 * @since       1.0.0
	 */
	protected function _afterQuery()
	{
		// Get elements from cahce
		while (!empty($this->types))
		{
			$_elements = get_object_vars($this->_getData(array_shift($this->types))->get('elements', new JObject));
			// Add only those elements are suitable states
			while (($_element = current($_elements)) !== false)
			{
				if (in_array($_element->{$this->getStateName()}, $this->states))
				{
					$this->buffer->set($_element->{$this->getPrimaryName()}, $_element);
				}

				next($_elements);
			}
		}
	}

	/**
	 *
	 * @since       1.0.0
	 */
	protected function _prepareBuffer()
	{
		$this->_searchData();

		// If array extensions type ids isn't empty, we get elements from database
		if ($this->_testQueryVars())
		{
			try
			{
				$query = $this->_getQuery();
				if ($_elements = $this->_db->setQuery($query)->loadObjectList())
				{
					// Add elements to extension type cache
					while ($_element = array_shift($_elements))
					{
						$this->_setData($_element);
					}
				}
			} catch (RuntimeException $e)
			{
				JLog::add($e->getMessage(), JLog::ERROR, 'Kextensions');
			}
		}

		if (!$this->config->get('afterQueryOff', false))
		{
			$this->_afterQuery();
		}
	}

	/**
	 * Method to get the Elements that reflect extensions type id and states
	 *
	 * @return    object        empty or array object elements
	 * @since       1.0.0
	 */
	protected function _getBuffer()
	{
		// Check arguments
		if (!call_user_func_array(array($this, '_checkArgs'), func_get_args()) || is_null($this->method))
		{
			return $this->_returnBuffer();
		}

		$this->_prepareBuffer();

		return $this->_returnBuffer();
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
			$this->_states      = array();
			$this->_types       = array();
			$this->_notElements = array();
		}

		return $reset;
	}
}