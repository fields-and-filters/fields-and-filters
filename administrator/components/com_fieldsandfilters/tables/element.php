<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kulka.tomek@gmail.com> -
 */

// No direct access
defined('_JEXEC') or die;

/**
 * element Table class
 *
 * @since       1.0.0
 */
class FieldsandfiltersTableElement extends JTable
{
	protected $_tbl_data = '#__fieldsandfilters_data';
	protected $_tbl_connections = '#__fieldsandfilters_connections';
	protected $_tbl_for_key = 'element_id';

	/**
	 * Constructor
	 *
	 * @param JDatabase A database connector object
	 *
	 * @since       1.0.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__fieldsandfilters_elements', 'id', $db);
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param    array        Named array
	 *
	 * @return    null|string    null is operation was satisfactory, otherwise returns an error
	 * @see        JTable:bind
	 *
	 * @since      1.0.0
	 */
	public function bind($array, $ignore = '')
	{
		$this->fields = JArrayHelper::getValue($array, 'fields', array());
		unset($array['fields']);

		return parent::bind($array, $ignore);
	}

	/**
	 * Method to perform sanity checks on the JTable instance properties to ensure
	 * they are safe to store in the database.  Child classes should override this
	 * method to make sure the data they are storing in the database is safe and
	 * as expected before storage.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @link     http://docs.joomla.org/JTable/check
	 * @since    1.0.0
	 */
	public function check()
	{
		$key = $this->_tbl_key;

		//If there is an ordering column and this is a new row then get the next ordering value
		if ($this->$key == 0)
		{
			$this->ordering = self::getNextOrder();
		}

		return true;
	}

	/**
	 * Overrides JTable::store to set modified data and user id.
	 *
	 * @param   boolean $updateNulls True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since    1.0.0
	 */
	public function store($updateNulls = false)
	{
		unset($this->fields);

		return parent::store($updateNulls);
	}

	/**
	 * @since    1.0.0
	 **/
	public function getElementID($extensionID, $itemID)
	{
		if (!is_numeric($extensionID) || !$extensionID || !is_numeric($itemID) || !$itemID)
		{
			return false;
		}

		$query = $this->_db->getQuery(true);
		$query->select($this->_db->quoteName($this->_tbl_key));
		$query->from($this->_db->quoteName($this->_tbl));
		$query->where($this->_db->quoteName('content_type_id') . '=' . (int) $extensionID);
		$query->where($this->_db->quoteName('item_id') . '=' . (int) $itemID);

		// Check for a database error.
		try
		{
			$elementID = $this->_db->setQuery($query)->loadResult();
		} catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return $elementID;
	}

	/**
	 * @since    1.0.0
	 **/
	protected function _testPrimaryKey($object)
	{
		$keyName = $this->_tbl_key;
		$pk      = $this->$keyName;

		if (!is_object($object) && !is_null($object))
		{
			$pk = $object;
		}
		elseif (isset($object->$keyName))
		{
			$pk = $object->$keyName;
		}

		return $pk;
	}

	/**
	 * @since    1.0.0
	 **/
	public function getData($object)
	{
		$pk = $this->_testPrimaryKey($object);

		if (!isset($object->field_id))
		{
			$this->setError('');

			return false;
		}

		$query = $this->_db->getQuery(true);

		$query->select($this->_db->quoteName('data'));
		$query->from($this->_db->quoteName($this->_tbl_data));
		$query->where($this->_db->quoteName($this->_tbl_for_key) . ' = ' . (int) $pk);
		$query->where($this->_db->quoteName('content_type_id') . ' = ' . (int) $this->content_type_id);
		$query->where($this->_db->quoteName('field_id') . ' = ' . (int) $object->field_id);

		$this->_db->setQuery($query);

		try
		{
			return $this->_db->loadResult();
		} catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;

	}

	/**
	 * @since    1.0.0
	 **/
	public function insertData($object)
	{
		$pk = $this->_testPrimaryKey($object);

		if (!isset($object->field_id) || !isset($object->data))
		{
			$this->setError('');

			return false;
		}

		$query = $this->_db->getQuery(true);

		$query->insert($this->_db->quoteName($this->_tbl_data));
		$query->columns(array(
			$this->_db->quoteName($this->_tbl_for_key),
			$this->_db->quoteName('content_type_id'),
			$this->_db->quoteName('field_id'),
			$this->_db->quoteName('data')
		));

		$query->values(
			(int) $pk . ',' .
			(int) $this->content_type_id . ',' .
			(int) $object->field_id . ',' .
			$this->_db->quote($object->data)
		);
		$this->_db->setQuery($query);

		// Check for a database error.
		try
		{
			$this->_db->execute();
		} catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * @since    1.0.0
	 **/
	public function updateData($object)
	{
		$pk = $this->_testPrimaryKey($object);

		if (!isset($object->field_id) || !isset($object->data))
		{
			$this->setError('');

			return false;
		}

		$query = $this->_db->getQuery(true);

		$query->update($this->_db->quoteName($this->_tbl_data));
		$query->set($this->_db->quoteName('data') . ' = ' . $this->_db->quote($object->data));

		$query->where($this->_db->quoteName($this->_tbl_for_key) . ' = ' . (int) $pk);
		$query->where($this->_db->quoteName('field_id') . ' = ' . (int) $object->field_id);

		$this->_db->setQuery($query);

		// Check for a database error.
		try
		{
			$this->_db->execute();
		} catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * @since       1.0.0
	 **/
	public function deleteData($object = null)
	{
		$pk = $this->_testPrimaryKey($object);

		$query = $this->_db->getQuery(true);

		$query->delete();
		$query->from($this->_db->quoteName($this->_tbl_data));

		if (!is_null($pk))
		{
			$query->where($this->_db->quoteName($this->_tbl_for_key) . ' = ' . (int) $pk);
		}

		if (isset($object->field_id))
		{
			if (is_array($object->field_id))
			{
				$object->field_id = array_unique($object->field_id);
				JArrayHelper::toInteger($object->field_id);

				$query->where($this->_db->quoteName('field_id') . ' IN (' . implode(',', $object->field_id) . ')');
			}
			elseif (is_numeric($object->field_id))
			{
				$query->where($this->_db->quoteName('field_id') . ' = ' . (int) $object->field_id);
			}
			else
			{
				$this->setError('');

				return false;
			}
		}

		$this->_db->setQuery($query);

		// Check for a database error.
		try
		{
			$this->_db->execute();
		} catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * @since    1.0.0
	 **/
	public function insertConnections($object)
	{
		$pk = $this->_testPrimaryKey($object);

		if (!isset($object->field_id) || !isset($object->field_value_id))
		{
			$this->setError('');

			return false;
		}

		$query = $this->_db->getQuery(true);

		$query->insert($this->_db->quoteName($this->_tbl_connections));
		$query->columns(array(
			$this->_db->quoteName($this->_tbl_for_key),
			$this->_db->quoteName('content_type_id'),
			$this->_db->quoteName('field_id'),
			$this->_db->quoteName('field_value_id')
		));

		$arrayValues = array(
			(int) $pk,
			(int) $this->content_type_id,
			(int) $object->field_id,
		);

		if (is_array($object->field_value_id))
		{
			$object->field_value_id = array_unique($object->field_value_id);
			JArrayHelper::toInteger($object->field_value_id);

			while ($field_value_id = array_shift($object->field_value_id))
			{
				$arrayValues['field_value_id'] = (int) $field_value_id;

				$query->values(implode(',', $arrayValues));
			}
		}
		elseif (is_numeric($object->field_value_id))
		{
			array_push($arrayValues, (int) $object->field_value_id);

			$query->values(implode(',', $arrayValues));
		}
		else
		{
			$this->setError('');

			return false;
		}

		$this->_db->setQuery($query);

		// Check for a database error.
		try
		{
			$this->_db->execute();
		} catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * @since    1.0.0
	 **/
	public function deleteConnections($object = null)
	{
		$pk = $this->_testPrimaryKey($object);

		$query = $this->_db->getQuery(true);

		$query->delete();
		$query->from($this->_db->quoteName($this->_tbl_connections));

		if (!is_null($pk))
		{
			$query->where($this->_db->quoteName($this->_tbl_for_key) . ' = ' . (int) $pk);
		}

		if (isset($object->field_id))
		{
			if (is_array($object->field_id))
			{
				$object->field_id = array_unique($object->field_id);
				JArrayHelper::toInteger($object->field_id);

				$query->where($this->_db->quoteName('field_id') . ' IN (' . implode(',', $object->field_id) . ')');
			}
			elseif (is_numeric($object->field_id))
			{
				$query->where($this->_db->quoteName('field_id') . ' = ' . (int) $object->field_id);
			}
			else
			{
				$this->setError('');

				return false;
			}
		}

		if (isset($object->field_value_id))
		{
			if (is_array($object->field_value_id))
			{
				$object->field_value_id = array_unique($object->field_value_id);
				JArrayHelper::toInteger($object->field_value_id);

				$query->where($this->_db->quoteName('field_value_id') . ' IN (' . implode(',', $object->field_value_id) . ')');
			}
			elseif (is_numeric($object->field_value_id))
			{
				$query->where($this->_db->quoteName('field_value_id') . ' = ' . (int) $object->field_value_id);
			}
			else
			{
				$this->setError('');

				return false;
			}
		}

		$this->_db->setQuery($query);

		// Check for a database error.
		try
		{
			$this->_db->execute();
		} catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table. The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed   $pks    An optional array of primary key values to update.  If not set the instance property value is used.
	 * @param   integer $state  The publishing state. eg. [0 = unpublished, 1 = published, -1 = onlyadmin state]
	 * @param   integer $userId The user id of the user performing the operation.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since    1.0.0
	 **/
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		$k = $this->_tbl_key;

		// Sanitize input.
		JArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state  = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else
			{
				$this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));

				return false;
			}
		}

		// Get the JDatabaseQuery object
		$query = $this->_db->getQuery(true);

		// Update the publishing state for rows with the given primary keys.
		$query->update($this->_db->quoteName($this->_tbl));
		$query->set($this->_db->quoteName('state') . ' = ' . (int) $state);

		// Build the WHERE clause for the primary keys.
		$query->where($this->_db->quoteName($k) . ' IN (' . implode(',', $pks) . ')');

		$this->_db->setQuery($query);

		try
		{
			$this->_db->execute();
		} catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks))
		{
			$this->state = $state;
		}

		$this->setError('');

		return true;
	}
}
