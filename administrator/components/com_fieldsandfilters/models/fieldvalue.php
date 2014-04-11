<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kulka.tomek@gmail.com> -
 */

// No direct access.
defined('_JEXEC') or die;

if (!FieldsandfiltersFactory::isVersion())
{
	jimport('joomla.application.component.modeladmin');
}

/**
 * Fieldsandfilters model.
 *
 * @since    1.0.0
 */
class FieldsandfiltersModelFieldvalue extends JModelAdmin
{
	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.0.0
	 */
	protected $text_prefix = 'COM_FIELDSANDFILTERS';

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param    type      The table type to instantiate
	 * @param    string    A prefix for the table class name. Optional.
	 * @param    array     Configuration array for model. Optional.
	 *
	 * @return    JTable    A database object
	 * @since    1.0.0
	 */
	public function getTable($type = 'Fieldvalue', $prefix = 'FieldsandfiltersTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Stock method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState()
	{
		FieldsandfiltersHelper::setUserStateFieldID(sprintf('%s.%ss.filter', $this->option, $this->name));

		parent::populateState();
	}

	/**
	 * Method to get the record form.
	 *
	 * @param    array   $data     An optional array of data for the form to interogate.
	 * @param    boolean $loadData True if the form is to load its own data (default case), false if not.
	 *
	 * @return    JForm    A JForm object on success, false on failure
	 * @since    1.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_fieldsandfilters.fieldvalue', 'fieldvalue', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 * @since    1.0.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_fieldsandfilters.edit.fieldvalue.data', array());

		if (empty($data))
		{
			$data = $this->getItem();

		}

		if (FieldsandfiltersFactory::isVersion())
		{
			$this->preprocessData('com_fieldsandfilters.fieldvalue', $data);
		}

		return $data;
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   JTable $table A JTable object.
	 *
	 * @return  array  An array of conditions to add to ordering queries.
	 *
	 * @since    1.0.0
	 */
	protected function getReorderConditions($table)
	{
		$condition   = array();
		$condition[] = 'field_id = ' . (int) $table->field_id;

		return $condition;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array &$pks An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since    1.0.0
	 */
	public function delete(&$pks)
	{
		$pks          = (array) $pks;
		$app          = JFactory::getApplication();
		$table        = $this->getTable();
		$elementTable = $this->getTable('Element', 'FieldsandfiltersTable');
		$context      = $this->option . '.' . $this->name;

		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin('content');

		try
		{
			// Iterate the items to delete each one.
			foreach ($pks as $i => $pk)
			{
				if (!$table->load($pk) || !$this->canDelete($table))
				{
					throw new Exception($table->getError());
				}
				// Trigger the onContentBeforeDelete event.
				$result = $app->triggerEvent($this->event_before_delete, array($context, $table));
				if (in_array(false, $result, true))
				{
					$this->setError($table->getError());

					return false;
				}

				$objectTable                 = new stdClass();
				$objectTable->field_value_id = $table->id;

				//delete connections
				if (!$elementTable->deleteConnections($objectTable))
				{
					throw new Exception($elementTable->getError());
				}

				if (!$table->delete($pk))
				{
					throw new Exception($table->getError());
				}

				// Trigger the onContentAfterDelete event.
				$app->triggerEvent($this->event_after_delete, array($context, $table));
			}

			// Clear the component's cache
			$this->cleanCache();
		} catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}
}