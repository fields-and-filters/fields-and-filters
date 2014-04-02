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
 * fieldvalue Table class
 *
 * @since    1.0.0
 */
class FieldsandfiltersTableFieldvalue extends JTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabase A database connector object
	 *
	 * @since    1.0.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__fieldsandfilters_field_values', 'id', $db);
	}

	/**
	 * Overloaded check function
	 *
	 * @since    1.0.0
	 */
	public function check()
	{
		//If there is an ordering column and this is a new row then get the next ordering value
		if ($this->id == 0)
		{
			$this->ordering = self::getNextOrder('field_id=' . (int) $this->field_id);
		}

		// Check for a field name.
		if (trim($this->value) == '')
		{
			$this->setError(JText::_('COM_FIELDSANDFILTERS_DATABASE_ERROR_VALID_FIELD_VALUE'));

			return false;
		}

		// Check for a field alias
		if (trim($this->alias) == '')
		{
			$this->alias = $this->value;
		}

		$this->alias = JApplication::stringURLSafe($this->alias);

		if (trim(str_replace('-', '', $this->alias)) == '')
		{
			$this->alias = JFactory::getDate()->format('Y-m-d-H-i-s');
		}

		return parent::check();
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
		// Verify that the alias is unique
		$table = JTable::getInstance('Fieldvalue', 'FieldsandfiltersTable');

		if ($table->load(array('alias' => $this->alias, 'field_id' => $this->field_id)) && ($table->id != $this->id || $this->id == 0))
		{
			$this->setError(JText::sprintf('COM_FIELDSANDFILTERS_DATABASE_ERROR_FIELD_VALUE_ALIAS_EXISTS', $this->alias));

			return false;
		}

		return parent::store($updateNulls);
	}

	/**
	 * @since    1.0.0
	 **/
	public function deleteByFieldID($fieldID)
	{
		$query = $this->_db->getQuery(true);

		$query->delete();
		$query->from($this->_db->quoteName($this->_tbl));

		// Delete the field value
		$query->where($this->_db->quoteName('field_id') . ' = ' . (int) $fieldID);

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
	 */
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
