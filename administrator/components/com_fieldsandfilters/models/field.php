<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
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
class FieldsandfiltersModelField extends JModelAdmin
{
	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since       1.0.0
	 */
	protected $text_prefix = 'COM_FIELDSANDFILTERS';

	/**
	 * Item cache.
	 *
	 * @since       1.1.0
	 */
	protected $_cache = array();

	/**
	 * The event to trigger after changing the required state of the data.
	 *
	 * @var    string
	 * @since       1.0.0
	 */
	protected $event_change_required = null;

	/**
	 * Constructor.
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 *
	 * @see         JController
	 * @since       1.1.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		if (isset($config['event_change_required']))
		{
			$this->event_change_required = $config['event_change_required'];
		}
		elseif (empty($this->event_change_required))
		{
			$this->event_change_required = 'onContentChangeRequired';
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
	public function getTable($type = 'Field', $prefix = 'FieldsandfiltersTable', $config = array())
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
	 * @since       1.1.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		if (empty($data))
		{
			$item            = $this->getItem();
			$mode            = (int) $item->get('mode');
			$type            = $item->get('type');
			$content_type_id = (int) $item->get('content_type_id');

		}
		else
		{
			$data = ($data instanceof JRegistry) ? $data : new JRegistry($data);

			$mode            = (int) $data->get('mode');
			$type            = $data->get('type');
			$content_type_id = (int) $data->get('content_type_id');

			if ($typeMode = FieldsandfiltersModes::getModeName($mode, FieldsandfiltersModes::MODE_NAME_TYPE))
			{
				$layoutType = $data->get('params.type.' . $typeMode . '_layout');
			}

		}

		$this->setState($this->getName() . '.mode', $mode);
		$this->setState($this->getName() . '.type', $type);
		$this->setState($this->getName() . '.content_type_id', $content_type_id);
		$this->setState($this->getName() . '.layoutType', isset($layoutType) ? $layoutType : '');

		// Get the form.
		$form = $this->loadForm('com_fieldsandfilters.field', 'field', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		$form->setValue('mode', null, FieldsandfiltersModes::getMode($form->getFieldAttribute('mode', 'value', 'field.text', 'properties')));

		if (!in_array($form->getValue('mode', 0), (array) FieldsandfiltersModes::getMode(FieldsandfiltersModes::MODE_FILTER)))
		{
			// Disable field for display.
			$form->setFieldAttribute('alias', 'type', 'hidden');

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('alias', 'filter', 'unset');

			if (trim($form->getValue('alias')) != '')
			{
				$form->setValue('alias', null, '');
			}
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
		$data      = $data instanceof JRegistry ? $data : new JRegistry($data);
		$fieldType = $data->get('type', $this->getState($this->getName() . '.type'));
		$typeMode  = $data->get('type_mode', FieldsandfiltersModes::getModeName($data->get('mode', $this->getState($this->getName() . '.mode')), FieldsandfiltersModes::MODE_NAME_TYPE));

		try
		{
			if ($fieldType && $typeMode && ($type = FieldsandfiltersFactory::getTypes()->getTypes(true)->get($fieldType)))
			{
				$path = $type->forms->get($typeMode, new JObject)->get('path');
				$form::addFormPath($path);

				if (!$form->loadFile($typeMode, true, '/metadata/form/*'))
				{
					throw new Exception(JText::_('JERROR_LOADFILE_FAILED'));
				}

				if ($layoutType = $data->get('params.type.' . $typeMode . '_layout', $this->getState($this->getName() . '.layoutType')))
				{
					$paths = array(
						JPath::clean(JPATH_PLUGINS . '/' . $type->type . '/' . $type->name . '/tmpl/' . $typeMode)
					);

					if (strpos($layoutType, ':') > 0 && strpos($layoutType, '_:') !== 0)
					{
						list($template, $layoutType) = explode(':', $layoutType);
						$paths[] = JPATH::clean(JPATH_SITE . '/templates/' . $template . '/html/plg_' . $type->type . '_' . $type->name . '/' . $typeMode);
					}

					$path = JPath::find($paths, $layoutType . '.xml');

					if (is_file($path))
					{
						if (!$form->loadFile($path, true, '/form/*'))
						{
							throw new Exception(JText::_('JERROR_LOADFILE_FAILED'));
						}
					}
				}

				// load plugin language
				KextensionsLanguage::load('plg_' . $type->type . '_' . $type->name, JPATH_ADMINISTRATOR);
			}

			$contentTypeId = $data->get('content_type_id', $this->getState($this->getName() . '.content_type_id'));
			$extensionForm = $data->get('extension_form', 'extension');

			// get extension type objet by type id or plugin type
			if ($contentTypeId && ($extension = FieldsandfiltersFactory::getExtensions()->getExtensionsByTypeID($contentTypeId, true, true)->get($contentTypeId)))
			{
				$path = $extension->forms->get($extensionForm, new JObject)->get('path');
				$form::addFormPath($path);

				if (!$form->loadFile($extensionForm, true, '/metadata/form/*'))
				{
					throw new Exception(JText::_('JERROR_LOADFILE_FAILED'));
				}

				// load plugin language
				KextensionsLanguage::load('plg_' . $extension->type . '_' . $extension->name, JPATH_ADMINISTRATOR);
			}
		} catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return;
		}

		// overwrite the mode default of the plugin type mode 
		$form->setFieldAttribute('mode', 'default', FieldsandfiltersModes::getMode($form->getFieldAttribute('mode', 'value', 'field.text', 'properties')));

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
		$data = JFactory::getApplication()->getUserState('com_fieldsandfilters.edit.field.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		if (!$data instanceof JRegistry)
		{
			$data = new JRegistry($data);
		}

		if (FieldsandfiltersFactory::isVersion())
		{
			$this->preprocessData('com_fieldsandfilters.field', $data);
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
		$pk = (!empty($pk)) ? (int) $pk : (int) $this->getState($this->getName() . '.id');

		$store = md5(__METHOD__ . $pk);
		if (!isset($this->_cache[$store]))
		{
			$isNew = true;

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			$table->load($pk);

			// Check for a table object error.
			if ($error = $table->getError())
			{
				$this->setError($error);

				return false;
			}

			// Prime required properties.			
			if (empty($table->id))
			{
				$table->content_type_id = 0;
				$table->params          = '{}';
			}
			else
			{
				$isNew = false;

				if (in_array($table->mode, FieldsandfiltersModes::getMode(FieldsandfiltersModes::MODE_STATIC)))
				{
					$elementTable = $this->getTable('Element', 'FieldsandfiltersTable');

					$objectTable             = new stdClass;
					$objectTable->element_id = 0;
					$objectTable->field_id   = $table->id;

					$elementTable->content_type_id = $table->content_type_id;

					if ($data = $elementTable->getData($objectTable))
					{
						$table->values = array('data' => $data);
					}
				}
			}

			// Convert to the JObject before adding the params.
			$properties = $table->getProperties(true);
			$item       = JArrayHelper::toObject($properties, 'JObject');

			// Convert the params field to an array.
			$item->params = new JRegistry;
			$item->params->loadString($table->params);

			if (!$isNew)
			{
				// Include the fieldsandfiltersExtensions plugins for the on prepare item events.
				JPluginHelper::importPlugin('fieldsandfilterstypes');

				// Trigger the onPrepareItem event.
				$result = JFactory::getApplication()->triggerEvent('onFieldsandfiltersPrepareElementFields', array(($this->option . '.' . $this->name), &$item, $isNew, $this->state));

				if (in_array(false, $result, true))
				{
					$this->setError(JText::_('COM_FIELDSANDFILTERS_DATABASE_ERROR_PREPARE_ITEM'));

					return false;
				}
			}

			$item->params = $item->params->toObject();

			$this->_cache[$store] = $item;
		}

		return $this->_cache[$store];
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array   &$pks  A list of the primary keys to change.
	 * @param   integer $value The value of the published state.
	 *
	 * @return  boolean  True on success.
	 * @since       1.0.0
	 */
	public function required(&$pks, $value = 1)
	{
		$user  = JFactory::getUser();
		$table = $this->getTable();
		$pks   = (array) $pks;

		// Include the content plugins for the change of state event.
		JPluginHelper::importPlugin('content');

		try
		{
			// Access checks.
			foreach ($pks as $i => $pk)
			{
				$table->reset();

				if ($table->load($pk) && !$this->canEditState($table))
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					throw new RuntimeException(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
				}
			}

			// Attempt to change the state of the records.
			if (!$table->required($pks, $value, $user->get('id')))
			{
				throw new Exception($table->getError());
			}

			// Trigger the onContentChangeState event.
			$context = $this->option . '.' . $this->name;
			$result  = JFactory::getApplication()->triggerEvent($this->event_change_required, array($context, $pks, $value));

			if (in_array(false, $result, true))
			{
				throw new Exception($table->getError());
			}

			// Clear the component's cache
			$this->cleanCache();
		} catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array $data The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 * @since       1.0.0
	 */
	public function save($data)
	{
		$app          = JFactory::getApplication();
		$table        = $this->getTable();
		$key          = $table->getKeyName();
		$pk           = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew        = true;
		$elementTable = $this->getTable('Element', 'FieldsandfiltersTable');

		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('content');

		// Include the fieldsandfilters filters plugins for the on save events.
		JPluginHelper::importPlugin(FieldsandfiltersTypes::PLUGIN_FOLDER);

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

			$context = $this->option . '.' . $this->name;

			// Trigger the onContentBeforeSave event.
			$result = $app->triggerEvent($this->event_before_save, array($context, $table, $isNew));
			if (in_array(false, $result, true))
			{
				throw new Exception($table->getError());
			}

			if ($isValues = isset($table->values))
			{
				$tableFields = (array) $table->get('values');
			}

			// Get old item
			$item = $this->getItem($table->$key);

			// Store the data.
			if (!$table->store())
			{
				throw new Exception($table->getError());
			}

			if ($isValues)
			{
				$table->set('values', JArrayHelper::toObject($tableFields, 'JObject'));
			}

			// Trigger the onFieldsandfiltersBeforeSaveData event.
			$result = $app->triggerEvent('onFieldsandfiltersBeforeSaveData', array($context, $table, $item, $isNew)); // array($newItem, $oldItem)

			if (in_array(false, $result, true))
			{
				throw new Exception($table->getError());
			}

			$staticModes = FieldsandfiltersModes::getMode(FieldsandfiltersModes::MODE_STATIC);

			if ($isValues)
			{
				if (in_array($table->mode, $staticModes))
				{
					// get Data
					$data = $table->get('values', new JObject)->get('data');

					$objectTable             = new stdClass;
					$objectTable->element_id = 0;
					$objectTable->field_id   = $table->id;

					$elementTable->content_type_id = $item->content_type_id;

					$oldData = $elementTable->getData($objectTable);

					if (!empty($data))
					{
						$objectTable->data = $data;

						if ($oldData && $table->content_type_id != $item->content_type_id)
						{
							$elementTable->deleteData($objectTable);

							$elementTable->content_type_id = $table->content_type_id;

							$elementTable->insertData($objectTable);
						}
						else
						{
							if ($oldData)
							{
								$elementTable->content_type_id = $table->content_type_id;

								$elementTable->updateData($objectTable);
							}
							else
							{
								$elementTable->content_type_id = $table->content_type_id;

								$elementTable->insertData($objectTable);
							}
						}
					}
					else
					{
						if ($oldData)
						{
							$elementTable->deleteData($objectTable);
						}
					}
				}
			}
			elseif ($table->mode != $item->mode && in_array($item->mode, $staticModes))
			{
				$objectTable             = new stdClass;
				$objectTable->element_id = 0;
				$objectTable->field_id   = $table->id;

				$elementTable->deleteData($objectTable);
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
	 * @since       1.1.0
	 */
	public function delete(&$pks)
	{
		$app          = JFactory::getApplication();
		$pks          = (array) $pks;
		$table        = $this->getTable();
		$valueTable   = $this->getTable('Fieldvalue', 'FieldsandfiltersTable');
		$elementTable = $this->getTable('Element', 'FieldsandfiltersTable');

		$filterMode = (array) FieldsandfiltersModes::getMode(FieldsandfiltersModes::MODE_FILTER);
		$otherMode  = (array) FieldsandfiltersModes::getModes(null, array(), true, $filterMode);

		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin('content');

		// Include the fieldsandfilters Types plugins for the on save events.
		JPluginHelper::importPlugin(FieldsandfiltersTypes::PLUGIN_FOLDER);

		try
		{
			// Iterate the items to delete each one.
			foreach ($pks as $i => $pk)
			{
				if (!$table->load($pk) || !$this->canDelete($table))
				{
					throw new Exception($table->getError());
				}

				$context = $this->option . '.' . $this->name;

				// Trigger the onContentBeforeDelete event.
				$result = $app->triggerEvent($this->event_before_delete, array($context, $table));
				if (in_array(false, $result, true))
				{
					throw new Exception($table->getError());
				}

				// Get old item
				$item = $this->getItem($pk);

				// Trigger the onFieldsandfiltersBeforeSaveData event.
				$result = $app->triggerEvent('onFieldsandfiltersBeforeDeleteData', array($context, $item));

				if (in_array(false, $result, true))
				{
					throw new Exception($table->getError());
				}

				$objectTable             = new stdClass();
				$objectTable->field_id   = $table->id;
				$objectTable->element_id = null;

				//delete fields values and connections
				if (in_array($table->mode, $filterMode))
				{
					if (!$valueTable->deleteByFieldID($table->id))
					{
						throw new Exception($valueTable->getError());
					}

					if (!$elementTable->deleteConnections($objectTable))
					{
						throw new Exception($elementTable->getError());
					}
				}
				// delete data
				else
				{
					if (in_array($table->mode, $otherMode) && !$elementTable->deleteData($objectTable))
					{
						throw new Exception($elementTable->getError());
					}
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