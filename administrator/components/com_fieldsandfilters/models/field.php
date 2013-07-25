<?php
/**
 * @version     1.1.0
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access.
defined('_JEXEC') or die;

if( !FieldsandfiltersFactory::isVersion() )
{
	jimport( 'joomla.application.component.modeladmin' );
}

/**
 * Fieldsandfilters model.
 *
 * @since       1.1.0
 */
class FieldsandfiltersModelfield extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since       1.0.0
	 */
	protected $text_prefix = 'COM_FIELDSANDFILTERS';
	
	/**
	 * @var		string	Folder plugin types name.
	 * @since       1.0.0
	 */
	protected $_folder_plugin_types = 'fieldsandfiltersTypes';
	
	/**
	 * Item cache.
	 * @since       1.1.0
	 */
	protected $_cache = array();
	
	/**
	 * @var		string	Folder plugin extensions types name.
	 * @since       1.0.0
	 */
	protected $_folder_plugin_extensions = 'fieldsandfiltersExtensions';
	
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
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since       1.1.0
	 */
	public function __construct( $config = array() )
	{
		parent::__construct( $config );

		if( isset( $config['event_change_required'] ) )
		{
			$this->event_change_required = $config['event_change_required'];
		}
		elseif( empty( $this->event_change_required ) )
		{
			$this->event_change_required = 'onContentChangeRequired';
		}
	}


	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since       1.0.0
	 */
	public function getTable( $type = 'Field', $prefix = 'FieldsandfiltersTable', $config = array() )
	{
		return JTable::getInstance( $type, $prefix, $config );
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since       1.1.0
	 */
	public function getForm( $data = array(), $loadData = true )
	{
		$pluginTypesHelper = FieldsandfiltersFactory::getPluginTypes();
		
		if( !empty( $data ) )
		{
			$this->setState( 'field.field_type', JArrayHelper::getValue( $data, 'field_type' ) );
			$this->setState( 'field.extension_type_id', JArrayHelper::getValue( $data, 'extension_type_id', 0 ) );
			
			if( ( $mode = JArrayHelper::getValue( $data, 'mode' ) ) && ( $typeMode = $pluginTypesHelper->getModeName( $mode, 'type' ) ) )
			{
				$this->setState( 'field.type_mode', $typeMode );
			}
		}
		
		// Get the form.
		$form = $this->loadForm( 'com_fieldsandfilters.field', 'field', array('control' => 'jform', 'load_data' => $loadData) );
		if( empty( $form ) )
		{
			return false;
		}
		
		$form->setValue( 'mode', null, $pluginTypesHelper->getMode( $form->getFieldAttribute( 'mode', 'value', 'field.text', 'properties' ) ) );
		
		if( !in_array( $form->getValue( 'mode', 0 ), (array) $pluginTypesHelper->getMode( 'filter' ) ) )
		{
			// Disable field for display.
			$form->setFieldAttribute( 'field_alias', 'disabled', 'true' );
			
			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute( 'field_alias', 'filter', 'unset' );
			
			// Set disable class for field.
			$form->setFieldAttribute( 'field_alias', 'class', 'disabled' );
			
			if( trim( $form->getValue( 'field_alias' ) ) != '' )
			{
				$form->setValue( 'field_alias', null, '' );
			}
		}
		
		return $form;
	}
	
	/**
	 * @param	object	$form	A form object.
	 * @param	mixed	$data	The data expected for the form.
	 *
	 * @return	void
	 * @since       1.1.0
	 * @throws	Exception if there is an error in the form event.
	 */
	protected function preprocessForm( JForm $form, $data, $group = 'fieldsandfilters' )
	{
		$pluginTypesHelper = FieldsandfiltersFactory::getPluginTypes();
		
		$fieldType	= $this->getState( 'field.field_type', false );
		$typeMode	= $this->getState( 'field.type_mode', false );
		
		if( is_array( $data ) )
		{
			$fieldType = JArrayHelper::getValue( $data, 'field_type', $fieldType );
			
			if( !$typeMode && ( $mode = JArrayHelper::getValue( $data, 'mode' ) ) && ( $tm = $pluginTypesHelper->getModeName( $mode, 'type' ) ) )
			{
				$typeMode = $tm;
			}
		}
		
		if( $fieldType && $typeMode )
		{
			// get plugin type object 
			if( $pluginType = $pluginTypesHelper->getTypes( true)->get( $fieldType ) )
			{
				// If an XML file was found in the component, load it first.
				// We need to qualify the full path to avoid collisions with component file names.
				$filePath = $pluginType->forms->get( $typeMode, new JObject )->get( 'path' );
				
				if( $filePath && is_file( $filePath ) )
				{
					// load plugin language
					FieldsandfiltersFactory::getExtensions()->loadLanguage( 'plg_' . $pluginType->type . '_' . $pluginType->name, JPATH_ADMINISTRATOR );
					
					if( $pluginForm = simplexml_load_file( $filePath ) )
					{
						// get plugin type properties field and set to $form fields properties
						if( $pluginFieldsProperties = $pluginForm->xpath('//fields[@name="properties"]') )
						{
							$pluginFieldsProperties = $pluginFieldsProperties[0]->xpath('descendant::field');
							
							$form->setFields( $pluginFieldsProperties, 'properties' );
						}
						
						// get Plugin type values fieldset and set to $form fields values
						if( $pluginFieldsValues = $pluginForm->xpath('//fields[@name="values"]') )
						{
							$pluginFieldsValues = $pluginFieldsValues[0]->xpath('descendant::fieldset');
							
							$form->setFields( $pluginFieldsValues, 'values' );
						}
						
						// get plugin type params fieldset and set to $form fields params.type
						if( $pluginFieldsParams = $pluginForm->xpath('//fields[@name="params"]') )
						{
							$pluginFieldsParams = $pluginFieldsParams[0]->xpath('descendant::fieldset');
							
							$form->setFields( $pluginFieldsParams, 'params.type' );
						}
					}
				}
			}
		}
		
		$extensionTypeId 	= $this->getState( 'field.extension_type_id', false );
		$extensionTypeId 	= is_array( $data ) ? JArrayHelper::getValue( $data, 'extension_type_id', $extensionTypeId ) : $extensionTypeId;
		
		$typeExtension		= $this->getState( 'field.type_extension', 'extension' );
		
		if( $extensionTypeId )
		{
			// get extension type objet by type id or plugin type
			if( $pluginExtension = FieldsandfiltersFactory::getPluginExtensions()->getExtensionsPivot( 'extension_type_id', true )->get( $extensionTypeId ) )
			{
				$filePath = $pluginExtension->forms->get( $typeExtension, new JObject )->get( 'path' );
				
				// get xml form plugin extenion 
				if( $filePath && is_file( $filePath ) )
				{
					// load plugin language
					FieldsandfiltersFactory::getExtensions()->loadLanguage( 'plg_' . $pluginExtension->type . '_' . $pluginExtension->name, JPATH_ADMINISTRATOR );
					
					if( $pluginForm = simplexml_load_file( $filePath ) )
					{
						// get plugin extenion params fieldset and set to $form fields params.extension
						if( $pluginFieldsParams = $pluginForm->xpath('//fields[@name="params"]') )
						{
							$pluginFieldsParams = $pluginFieldsParams[0]->xpath('descendant::fieldset');
							
							$form->setFields( $pluginFieldsParams, 'params.extension' );
						}
						
						// get plugin extenion properties field and set to $form fields properties
						if( ( $pluginFieldsParams = $pluginForm->xpath( '//fields[@name="properties"]' ) ) && ( $modelForm = simplexml_load_file( JPATH_COMPONENT_ADMINISTRATOR . '/models/forms/' . $this->name . '.xml' ) ) )
						{
							$fieldset = new SimpleXMLElement( '<fieldset/>' );
							$fieldset->addAttribute( 'name', 'extension' );
							
							if( ( $pluginFieldLocation = $pluginFieldsParams[0]->xpath( 'descendant::field[@name="location"]' ) ) && ( $modelFormLocation = $modelForm->xpath( 'fields[@name="params"]/fields[@name="extension"]//field[@name="location"]' ) ) )
							{
								$modelFormLocation = $modelFormLocation[0];
								
								// Add options to model Form Location node
								FieldsandfiltersXMLHelper::mergeOptionsNode( $modelFormLocation, $pluginFieldLocation[0] );
								
								// Add model Form Location node to fieldset
								FieldsandfiltersXMLHelper::setFields( $fieldset, $modelFormLocation );
							}
							
							$form->setField( $fieldset, 'params.extension' );
						}
					}
				}
			}
		}
		
		// overwrite the mode default of the plugin type mode 
		$form->setFieldAttribute( 'mode', 'default', $pluginTypesHelper->getMode( $form->getFieldAttribute( 'mode', 'value', 'field.text', 'properties' ) ) );
		
		// Trigger the default form events.
		parent::preprocessForm( $form, $data, $group );
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since       1.0.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState( 'com_fieldsandfilters.edit.field.data', array() );

		if( empty( $data ) )
		{
			$data = new JRegistry( $this->getItem() );
            
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since       1.1.0
	 */
	public function getItem( $pk = null )
	{
		$pk = ( !empty( $pk ) ) ? (int) $pk : (int) $this->getState( 'field.id' );
		
		$store = md5( __METHOD__ . $pk );
		if( !isset( $this->_cache[$store] ) )
		{
			$isNew			= true;
			$pluginTypesHelper	= FieldsandfiltersFactory::getPluginTypes();
			
			// Get a level row instance.
			$table = $this->getTable();
			
			// Attempt to load the row.
			$table->load( $pk );
			
			// Check for a table object error.
			if( $error = $table->getError() )
			{
				$this->setError( $error );
				return false;
			}
			
			// Prime required properties.
			if( $fieldType = $this->getState( 'field.field_type' ) )
			{
				$table->field_type = $fieldType;
			}
			else
			{
				// We have a valid type, inject it into the state for forms to use.
				$this->setState( 'field.field_type', $table->field_type );
			}
			
			if( !$this->getState( 'field.type_mode' ) && $table->mode && ( $typeMode = $pluginTypesHelper->getModeName( $table->mode, 'type' ) ) )
			{
				$this->setState( 'field.type_mode', $typeMode );
			}
			
			
			if( empty( $table->field_id ) )
			{
				$table->extension_type_id 	= 0;
				$table->params			= '{}';
			}
			else
			{
				$isNew		= false;
				$this->setState( 'field.extension_type_id', $table->extension_type_id );
				
				if( in_array( $table->mode, $pluginTypesHelper->getMode( 'static' ) ) )
				{
					$elementTable 	= $this->getTable( 'Element', 'FieldsandfiltersTable' );
					
					$objectTable			= new stdClass;
					$objectTable->element_id 	= 0;
					$objectTable->field_id 		= $table->field_id;
					
					$elementTable->extension_type_id = $table->extension_type_id;
					
					if( $data = $elementTable->getData( $objectTable ) )
					{
						$table->values = array(	'data' => $data );
					}
				}
			}
			
			// Convert to the JObject before adding the params.
			$properties 	= $table->getProperties( true );
			$item	 	= JArrayHelper::toObject( $properties, 'JObject' );
			
			// Convert the params field to an array.
			$item->params	= new JRegistry;
			$item->params->loadString( $table->params );
			
			if( !$isNew )
			{
				// Include the fieldsandfiltersExtensions plugins for the on prepare item events.
				JPluginHelper::importPlugin( 'fieldsandfiltersTypes' );
				
				// Trigger the onPrepareItem event.
				$result = FieldsandfiltersFactory::getDispatcher()->trigger( 'onFieldsandfiltersPrepareElementFields', array( ( $this->option . '.' . $this->name ), &$item, $isNew, $this->state ) );
				
				if( in_array( false, $result, true ) )
				{
					$this->setError( JText::sprintf( 'COM_FIELDSANDFILTERS_DATABASE_ERROR_PREPARE_ITEM', $this->getState( 'elements.extension_name' ) ) );
					
					return false;
				}
			}
			
			$item->params	= $item->params->toObject();
			
			$this->_cache[$store] = $item;
		}
		
		return $this->_cache[$store];
	}
	
	/**
	 * Auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 * @since       1.1.0
	 */
	protected function populateState()
	{
		// Get the pk of the record from the request.
		$app 	= JFactory::getApplication( 'administrator' );
		$jinput = $app->input;
		
		$fieldId = $jinput->getInt( 'id' );
		$this->setState( 'field.id', $fieldId );
		
		$fieldData = new JObject( $app->getUserState( 'com_fieldsandfilters.edit.field.data', array() ) );
		
		$fieldType = $fieldData->get( 'field_type', $jinput->get( 'field_type' ) );
		$this->setState( 'field.field_type', $fieldType );
		
		if( $typeMode = $fieldData->get( 'type_mode' ) )
		{
			$this->setState( 'field.type_mode', $typeMode );
		}
		elseif( ( $mode = $jinput->get( 'mode' ) ) && ( $typeMode = FieldsandfiltersFactory::getPluginTypes()->getModeName( $mode, 'type' ) ) )
		{
			$this->setState( 'field.type_mode', $typeMode );
		}
		
		$fieldExtension = (int) $fieldData->get( 'extension_type_id', $jinput->get( 'extension_type_id' ) );		
		$this->setState( 'field.extension_type_id', $fieldExtension );
		
		$typeExtension = $fieldData->get( 'type_extension', 'extension' );
		$this->setState( 'field.type_extension', $typeExtension );
		
		// name field plugin folder
		$this->setState( 'field.folder_plugin_types', $this->_folder_plugin_types );
		$this->setState( 'field.folder_plugin_extensions', $this->_folder_plugin_extensions );
		
		// Load the parameters.
		$value = JComponentHelper::getParams( $this->option );
		$this->setState( 'params', $value );
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since       0.1.0
	 */
	/*
	protected function prepareTable(&$table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id)) {

			// Set ordering to the last item if not set
			if (@$table->ordering === '') {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__fieldsandfilters_fields');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}

		}
	}
	*/
	
	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 * @since       1.0.0
	 */
	public function required( &$pks, $value = 1 )
	{
		$user		= JFactory::getUser();
		$table 		= $this->getTable();
		$pks 		= (array) $pks;

		// Include the content plugins for the change of state event.
		JPluginHelper::importPlugin( 'content' );

		// Access checks.
		foreach( $pks as $i => $pk )
		{
			$table->reset();

			if( $table->load( $pk ) )
			{
				if( !$this->canEditState( $table ) )
				{
					// Prune items that you can't change.
					unset( $pks[$i] );
					JLog::add( JText::_( 'JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED' ), JLog::WARNING, 'jerror' );
					return false;
				}
			}
		}

		// Attempt to change the state of the records.
		if( !$table->required( $pks, $value, $user->get( 'id' ) ) )
		{
			$this->setError( $table->getError() );
			return false;
		}

		$context = $this->option . '.' . $this->name;

		// Trigger the onContentChangeState event.
		$result = FieldsandfiltersFactory::getDispatcher()->trigger( $this->event_change_required, array( $context, $pks, $value ) );

		if( in_array( false, $result, true ) )
		{
			$this->setError( $table->getError() );
			return false;
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}
	
	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 * @since       1.0.0
	 */
	public function save( $data )
	{
		$table 			= $this->getTable();
		$key 			= $table->getKeyName();
		$pk 			= ( !empty( $data[$key] ) ) ? $data[$key] : (int) $this->getState( $this->getName() . '.id' );
		$isNew 			= true;
		$elementTable 		= $this->getTable( 'Element', 'FieldsandfiltersTable' );
		$dispatcher		= FieldsandfiltersFactory::getDispatcher();

		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin( 'content' );
		
		// Include the fieldsandfilters Types plugins for the on save events.
		JPluginHelper::importPlugin( 'fieldsandfiltersTypes' );

		// Allow an exception to be thrown.
		try
		{
			// Load the row if saving an existing record.
			if( $pk > 0 )
			{
				$table->load( $pk );
				$isNew = false;
			}
			
			// Bind the data.
			if( !$table->bind( $data ) )
			{
				$this->setError( $table->getError() );
				return false;
			}
			
			// Prepare the row for saving
			$this->prepareTable( $table );

			// Check the data.
			if( !$table->check() )
			{
				$this->setError( $table->getError() );
				return false;
			}
			
			// Trigger the onContentBeforeSave event.
			$result = $dispatcher->trigger( $this->event_before_save, array( $this->option . '.' . $this->name, $table, $isNew ) );
			if( in_array( false, $result, true ) )
			{
				$this->setError( $table->getError() );
				return false;
			}
			
			if( $isValues = isset( $table->values )  )
			{
				$tableFields = (array) $table->get( 'values' );
			}
			
			// Get old item
			$item = $this->getItem( $table->$key );
			
			// Store the data.
			if( !$table->store() )
			{
				$this->setError( $table->getError() );
				return false;
			}
			
			if( $isValues )
			{
				$table->set( 'values', JArrayHelper::toObject( $tableFields, 'JObject' ) );
			}
			
			// Trigger the onFieldsandfiltersBeforeSaveData event.
			$result = $dispatcher->trigger( 'onFieldsandfiltersBeforeSaveData', array( ( $this->option . '.' . $this->name ), $table, $item, $isNew ) ); // array( $newItem, $oldItem )
			
			if( in_array( false, $result, true ) )
			{
				$this->setError( $table->getError() );
				return false;
			}
			
			$staticModes = FieldsandfiltersFactory::getPluginTypes()->getMode( 'static' );
			
			if( $isValues )
			{
				if( in_array( $table->mode, $staticModes ) )
				{
					// get Data
					$data 		= $table->get( 'values', new JObject )->get( 'data' );
					
					$objectTable			= new stdClass;
					$objectTable->element_id 	= 0;
					$objectTable->field_id 		= $table->field_id;
					
					$elementTable->extension_type_id = $item->extension_type_id;
					
					$oldData = $elementTable->getData( $objectTable );
					
					if( !empty( $data ) )
					{
						$objectTable->field_data = $data;
						
						if( $oldData && $table->extension_type_id != $item->extension_type_id )
						{
							$elementTable->deleteData( $objectTable );
							
							$elementTable->extension_type_id = $table->extension_type_id;
							
							$elementTable->insertData( $objectTable );
						}
						else if( $oldData )
						{
							$elementTable->extension_type_id = $table->extension_type_id;
							
							$elementTable->updateData( $objectTable );
						}
						else
						{
							$elementTable->extension_type_id = $table->extension_type_id;
							
							$elementTable->insertData( $objectTable );
						}
					}
					else if( $oldData )
					{
						$elementTable->deleteData( $objectTable );
					}
				}
			}
			elseif( $table->mode != $item->mode && in_array( $item->mode, $staticModes ) )
			{
				$objectTable			= new stdClass;
				$objectTable->element_id 	= 0;
				$objectTable->field_id 		= $table->field_id;
				
				$elementTable->deleteData( $objectTable );
			}
			
			// Clean the cache.
			$this->cleanCache();

			// Trigger the onContentAfterSave event.
			$dispatcher->trigger( $this->event_after_save, array( $this->option . '.' . $this->name . '.' . $extensionName, $table, $isNew ) );
		}
		catch( Exception $e )
		{
			$this->setError( $e->getMessage() );

			return false;
		}

		$pkName = $table->getKeyName();

		if( isset( $table->$pkName ) )
		{
			$this->setState( $this->getName() . '.id', $table->$pkName );
		}
		$this->setState( $this->getName() . '.new', $isNew );

		return true;
	}
	
	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 * @since       1.1.0
	 */
	public function delete( &$pks )
	{
		$pks 		= (array) $pks;
		$table 		= $this->getTable();
		$valueTable 	= $this->getTable( 'Fieldvalue', 'FieldsandfiltersTable' );
		$elementTable 	= $this->getTable( 'Element', 'FieldsandfiltersTable' );
		$dispatcher	= FieldsandfiltersFactory::getDispatcher();
		
		// Load PluginTypes Helper
		$pluginTypesHelper	= FieldsandfiltersFactory::getPluginTypes();
		$filterMode		= (array) $pluginTypesHelper->getMode( 'filter' );
		$otherMode		= (array) $pluginTypesHelper->getModes( null, array(), true, $filterMode );
		
		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin( 'content' );
		
		// Include the fieldsandfilters Types plugins for the on save events.
		JPluginHelper::importPlugin( 'fieldsandfiltersTypes' );
		
		// Iterate the items to delete each one.
		foreach( $pks as $i => $pk )
		{
			if( $table->load( $pk ) )
			{
				if( $this->canDelete( $table ) )
				{
					$context = $this->option . '.' . $this->name;
					
					// Trigger the onContentBeforeDelete event.
					$result = $dispatcher->trigger( $this->event_before_delete, array( $context, $table ) );
					if( in_array( false, $result, true ) )
					{
						$this->setError( $table->getError() );
						return false;
					}
					
					// Get old item
					$item = $this->getItem( $pk );
					
					// Trigger the onFieldsandfiltersBeforeSaveData event.
					$result = $dispatcher->trigger( 'onFieldsandfiltersBeforeDeleteData', array( $context, $item ) );
					
					if( in_array( false, $result, true ) )
					{
						$this->setError( $table->getError() );
						return false;
					}
					
					$objectTable 			= new stdClass();
					$objectTable->field_id 		= $table->field_id;
					$objectTable->element_id 	= null;
					
					//delete fields values and connections
					if( in_array( $table->mode, $filterMode ) )
					{
						if( !$valueTable->deleteByFieldID( $table->field_id ) )
						{
							$this->setError( $valueTable->getError() );
							return false;
						}
						
						if( !$elementTable->deleteConnections( $objectTable ) )
						{
							$this->setError( $elementTable->getError() );
							return false;
						}
					}
					// delete data
					else if( in_array( $table->mode, $otherMode ) )
					{
						if( !$elementTable->deleteData( $objectTable ) )
						{
							$this->setError( $elementTable->getError() );
							return false;
						}
					}
					
					if( !$table->delete( $pk ) )
					{
						$this->setError( $table->getError() );
						return false;
					}
					
					// Trigger the onContentAfterDelete event.
					$dispatcher->trigger( $this->event_after_delete, array( $context, $table ) );
				}
				else
				{
					// Prune items that you can't change.
					unset( $pks[$i] );
					$error = $this->getError();
					if( $error )
					{
						JLog::add( $error, JLog::WARNING, 'jerror' );
						return false;
					}
					else
					{
						JLog::add( JText::_( 'JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED' ), JLog::WARNING, 'jerror' );
						return false;
					}
				}

			}
			else
			{
				$this->setError( $table->getError() );
				return false;
			}
		}
		
		// Clear the component's cache
		$this->cleanCache();
		
		return true;
	}
}