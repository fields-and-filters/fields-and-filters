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
 * @since       1.1.0
 */
class FieldsandfiltersModelElement extends JModelAdmin
{
	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since       1.0.0
	 */
	protected $text_prefix = 'COM_FIELDSANDFILTERS';

	/**
	 * Feilds Form cache.
	 *
	 * @since       1.0.0
	 */
	protected $fieldsFrom = array();

	/**
	 * Item cache.
	 *
	 * @since       1.0.0
	 */
	protected $_cache = array();

	/**
	 * @since       1.0.0
	 **/
	protected $_item_states = array(-3, -2, -1, 0, 1, 2, 3);

	/**
	 * Constructor.
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 *
	 * @see         JController
	 * @since       1.0.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		if (isset($config['item_states']))
		{
			$this->_item_states = $config['item_states'];
		}
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param    type      The table type to instantiate
	 * @param    string    A prefix for the table class name. Optional.
	 * @param    array     Configuration array for model. Optional.
	 *
	 * @return    JTable    A database object
	 * @since       1.0.0
	 */
	public function getTable($type = 'Element', $prefix = 'FieldsandfiltersTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param    array   $data     An optional array of data for the form to interogate.
	 * @param    boolean $loadData True if the form is to load its own data (default case), false if not.
	 *
	 * @return    JForm    A JForm object on success, false on failure
	 * @since       1.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// The folder and element vars are passed when saving the form.
		if (empty($data))
		{
			$item          = $this->getItem();
			$elementID     = (int) $item->get('id');
			$contentTypeID = (int) $item->get('content_type_id');
			$itemID        = (int) $item->get('item_id');
		}
		else
		{
			$elementID     = JArrayHelper::getValue($data, 'id', 0, 'int');
			$contentTypeID = JArrayHelper::getValue($data, 'content_type_id', 0, 'int');
			$itemID        = JArrayHelper::getValue($data, 'item_id', 0, 'int');
		}

		// These variables are used to add data from the plugin XML files.
		$this->setState($this->getName() . '.id', $elementID);
		$this->setState($this->getName() . '.content_type_id', $contentTypeID);
		$this->setState($this->getName() . '.item_id', $itemID);

		// Get the form.
		$form = $this->loadForm('com_fieldsandfilters.element', 'element', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * @param    object $form A form object.
	 * @param    mixed  $data The data expected for the form.
	 *
	 * @return    void
	 * @since       1.1.0
	 * @throws    Exception if there is an error in the form event.
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		$contentTypeID = $this->getState($this->getName() . '.content_type_id', 0);

		if ($contentTypeID)
		{
			$isNew = !(boolean) $this->getState($this->getName() . '.id', 0);

			$fieldsForm = new KextensionsForm($form->getName());
			$fieldsData = FieldsandfiltersFieldsHelper::getFieldsByTypeIDColumnFieldType($contentTypeID);

			$fieldsForm->setPath('filters');

			$fieldsetXML = new SimpleXMLElement('<fieldset />');
			$fieldsetXML->addAttribute('name', 'fieldsandfilters');

			JPluginHelper::importPlugin('fieldsandfilterstypes');

			// Trigger the onFieldsandfiltersPrepareFormField event.
			JFactory::getApplication()->triggerEvent('onFieldsandfiltersPrepareFormField', array($fieldsForm, $fieldsData, $isNew));

			if ($fieldsFormXML = $fieldsForm->getFormFields())
			{
				// Load the XML Helper
				KextensionsXML::setFields($fieldsetXML, $fieldsFormXML);

				$form->setField($fieldsetXML, 'fields');

				if ($default = $fieldsForm->getData())
				{
					$form->bind($default);
				}
			}
		}

		// Trigger the default form events.
		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 * @since       1.0.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_fieldsandfilters.edit.element.data', array());

		if (empty($data))
		{
			// is error SimpleXMLElement::xpath(), searching name '*_error' when $data is instance of JObject.
			$data = new JRegistry($this->getItem());
		}

		if (FieldsandfiltersFactory::isVersion())
		{
			$this->preprocessData('com_fieldsandfilters.element', $data);
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param    integer    The id of the primary key.
	 *
	 * @return    mixed    Object on success, false on failure.
	 * @since       1.1.0
	 */
	public function getItem($pk = null)
	{
		$row = (!empty($pk)) ? (int) $pk : (int) $this->getState($this->getName() . '.id');

		if (empty($row))
		{
			$row = array(
				'item_id'         => (int) $this->getState($this->getName() . '.item_id', 0),
				'content_type_id' => (int) $this->getState($this->getName() . '.content_type_id', 0)
			);
		}

		$store = md5(serialize($row));
		if (!isset($this->_cache[$store]))
		{
			$app           = JFactory::getApplication();
			$isNew         = true;
			$extensionName = false;

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			$table->load($row);

			if (!empty($table->id))
			{
				$isNew = false;

				// Load Elements Helper
				if ($element = FieldsandfiltersFactory::getElements()->getElementsByID($table->content_type_id, $table->id, $this->_item_states, FieldsandfiltersElements::VALUES_BOTH)->get($table->id))
				{
					$table->fields = array(
						'connections' => $element->connections->getProperties(true),
						'data'        => $element->data->getProperties(true)
					);
				}
			}
			else
			{
				$table->content_type_id = (int) $this->getState($this->getName() . '.content_type_id', 0);
				$table->item_id         = (int) $this->getState($this->getName() . '.item_id', 0);
			}

			if ($table->content_type_id && ($extension = FieldsandfiltersFactory::getExtensions()->getExtensionsByTypeID($table->content_type_id, true, true)->get($table->content_type_id)))
			{
				$extensionName = $extension->name;
			}

			// Convert to the JObject before adding other data.
			$properties = $table->getProperties(true);

			$item = JArrayHelper::toObject($properties, 'JObject');

			if ($extensionName)
			{
				$item->extension_name = $extension->forms->extension->title;

				// Include the fieldsandfiltersExtensions plugins for the on prepare item events.
				JPluginHelper::importPlugin('fieldsandfiltersextensions');
				JPluginHelper::importPlugin('fieldsandfilterstypes');

				// Trigger the onFieldsandfiltersPrepareElement event.
				$result = $app->triggerEvent('onFieldsandfiltersPrepareElement', array(($this->option . '.' . $this->name . '.' . $extensionName), &$item, $isNew, $this->state));

				if (in_array(false, $result, true))
				{
					$this->setError(JText::sprintf('COM_FIELDSANDFILTERS_DATABASE_ERROR_PREPARE_ITEM', $extensionName));

					return false;
				}

				// Trigger the onFieldsandfiltersPrepareElementFields event.
				$result = $app->triggerEvent('onFieldsandfiltersPrepareElementFields', array(($this->option . '.' . $this->name), &$item, $isNew, $this->state));

				if (in_array(false, $result, true))
				{
					$this->setError(JText::sprintf('COM_FIELDSANDFILTERS_DATABASE_ERROR_PREPARE_ITEM', $extensionName));

					return false;
				}
			}

			$this->_cache[$store] = $item;
		}

		return $this->_cache[$store];
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since       1.0.0
	 */
	protected function populateState()
	{
		// Get the pk of the record from the request.
		$jinput = JFactory::getApplication()->input;

		$itemID = (int) $jinput->get('itid');
		$this->setState($this->getName() . '.item_id', $itemID);

		$contentTypeID = (int) $jinput->get('ctid');
		$this->setState($this->getName() . '.content_type_id', $contentTypeID);

		parent::populateState();
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   JTable $table A record object.
	 *
	 * @return  array  An array of conditions to add to add to ordering queries.
	 *
	 * @since       1.0.0
	 */
	protected function getReorderConditions($table)
	{
		$condition   = array();
		$condition[] = 'content_type_id = ' . (int) $table->content_type_id;

		return $condition;
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   JTable &$table A reference to a JTable object.
	 *
	 * @return  void
	 *
	 * @since       1.0.0
	 */
	protected function prepareTable($table)
	{
		$key = $table->getKeyName();
		if (empty($table->$key))
		{
			if ($elementID = $table->getElementID($table->content_type_id, $table->item_id))
			{
				$table->$key = $elementID;
			}
		}
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array $data The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since       1.1.0
	 */
	public function save($data)
	{
		$app   = JFactory::getApplication();
		$table = $this->getTable();
		$key   = $table->getKeyName();
		$pk    = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('content');
		JPluginHelper::importPlugin('fieldsandfilterstypes');

		// Allow an exception to be thrown.
		try
		{
			// Load the row if saving an existing record.
			if ($pk > 0)
			{
				$table->load($pk);
				$isNew = false;
			}

			// Bind the data.
			if (!$table->bind($data))
			{
				throw new Exception($table->getError());
			}

			// Prepare the row for saving
			$this->prepareTable($table);

			// Check the data.
			if (!$table->check())
			{
				throw new Exception($table->getError());
			}

			// Load PluginExtensions Helper
			$extensionName = ($extension = FieldsandfiltersFactory::getExtensions()->getExtensionsByTypeIDPivot('content_type_id', $table->content_type_id)->get($table->content_type_id)) ? $extension->name : '';

			$context = $this->option . '.' . $this->name . '.' . $extensionName;

			// Trigger the onContentBeforeSave event.
			$result = $app->triggerEvent($this->event_before_save, array($context, $table, $isNew));
			if (in_array(false, $result, true))
			{
				throw new Exception($table->getError());
			}

			$tableFields = (array) $table->get('fields');

			// Get old item
			$item = $this->getItem($table->$key);

			// Store the data.
			if (!$table->store())
			{
				throw new Exception($table->getError());
			}

			$table->set('fields', JArrayHelper::toObject($tableFields, 'JObject'));

			// Store fields data and connections
			// Trigger the onFieldsandfiltersBeforeSaveData event.
			$result = $app->triggerEvent('onFieldsandfiltersBeforeSaveData', array(($this->option . '.' . $this->name), $table, $item, $isNew)); // array($newItem, $oldItem)

			if (in_array(false, $result, true))
			{
				throw new Exception($table->getError());
			}

			$item            = $item->get('fields', new JObject);
			$dataItem        = $item->get('data', new JObject);
			$connectionsItem = $item->get('connections', new JObject);

			$tableFields = $table->get('fields', new JObject);
			$data        = $tableFields->get('data', new JObject);
			$connections = $tableFields->get('connections', new JObject);

			$filterMode = (array) FieldsandfiltersModes::getMode(FieldsandfiltersModes::MODE_FILTER, array());
			$otherMode  = (array) FieldsandfiltersModes::getModes(null, array(), true, $filterMode);

			$fields = FieldsandfiltersFieldsHelper::getFieldsByTypeIDColumnFieldType($table->content_type_id);
			$fields = KextensionsArray::flatten(get_object_vars($fields));

			while ($field = array_shift($fields))
			{
				$_data            = (string) $data->get($field->id, '');
				$_dataItem        = (string) $dataItem->get($field->id, '');
				$_connections     = (array) $connections->get($field->id, new JObject)->getProperties(true);
				$_connectionsItem = (array) $connectionsItem->get($field->id, new JObject)->getProperties(true);

				// other (field/static)
				if (in_array($field->mode, $otherMode) && (!empty($_data) || !empty($_dataItem)))
				{
					$tableObject           = new stdClass();
					$tableObject->field_id = (int) $field->id;

					// delete text
					if (!empty($_dataItem) && empty($_data))
					{
						$table->deleteData($tableObject);
					}
					// insert text
					elseif (empty($_dataItem) && !empty($_data))
					{
						$tableObject->data = $_data;

						$table->insertData($tableObject);
					}
					// update text
					elseif ($_dataItem != $_data)
					{
						$tableObject->data = $_data;

						$table->updateData($tableObject);
					}
				}
				// filter
				elseif (in_array($field->mode, $filterMode) && (!empty($_connections) || !empty($_connectionsItem)))
				{
					$tableObject           = new stdClass();
					$tableObject->field_id = (int) $field->id;

					$field_valuesID = array_keys($field->values->getProperties(true));
					$_connections   = array_intersect($field_valuesID, $_connections);
					$__connections  = array_unique(array_diff($_connections, $_connectionsItem));

					JArrayHelper::toInteger($__connections);

					if (!empty($__connections))
					{
						$tableObject->field_value_id = $__connections;
						$table->insertConnections($tableObject);
					}

					$__connections = array_unique(array_diff($_connectionsItem, $_connections));

					JArrayHelper::toInteger($__connections);

					if (!empty($__connections))
					{
						$tableObject                 = new stdClass();
						$tableObject->field_value_id = $__connections;

						$table->deleteConnections($tableObject);
					}
				}
			}

			// Trigger the onContentAfterSave event.
			$app->triggerEvent($this->event_after_save, array($context, $table, $isNew));

			// Clean the cache.
			$this->cleanCache();
		} catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		$pkName = $table->getKeyName();

		if (isset($table->$pkName))
		{
			$this->setState($this->getName() . '.id', $table->$pkName);
		}
		$this->setState($this->getName() . '.new', $isNew);

		return true;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array &$pks An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since       1.1.0
	 */
	public function delete(&$pks)
	{
		$app        = JFactory::getApplication();
		$pks        = (array) $pks;
		$table      = $this->getTable();
		$dispatcher = FieldsandfiltersFactory::getDispatcher();

		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin('content');
		JPluginHelper::importPlugin('fieldsandfilterstypes');

		// Load PluginExtensions Helper
		$extensionsHelper = FieldsandfiltersFactory::getExtensions();

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if (!$table->load($pk) || !$this->canDelete($table))
			{
				throw new Exception($table->getError());
			}

			$extensionName = ($extension = $extensionsHelper->getExtensionsByTypeID($table->content_type_id)->get($table->content_type_id)) ? $extension->name : '';

			$context = $this->option . '.' . $this->name . '.' . $extensionName;

			// Trigger the onContentBeforeDelete event.
			$result = $app->triggerEvent($this->event_before_delete, array($context, $table));
			if (in_array(false, $result, true))
			{
				throw new Exception($table->getError());
			}

			// Trigger the onFieldsandfiltersBeforeDeleteData event.
			$result = $app->triggerEvent('onFieldsandfiltersBeforeDeleteData', array(($this->option . '.' . $this->name), $this->getItem($pk)));

			if (in_array(false, $result, true))
			{
				throw new Exception($table->getError());
			}

			// delete fields data element
			if (!$table->deleteData($pk))
			{
				throw new Exception($table->getError());
			}

			// delete fields connections element
			if (!$table->deleteConnections($pk))
			{
				throw new Exception($table->getError());
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

		return true;
	}
}