<?php
/**
 * @version     1.0.0
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kulka.tomek@gmail.com> - 
 */

// No direct access.
defined( '_JEXEC' ) or die;

if( version_compare( JVERSION, 3.0, '<' ) )
{
	jimport( 'joomla.application.component.modeladmin' );
}

/**
 * Fieldsandfilters model.
 * @since       1.1.0
 */
class FieldsandfiltersModelelement extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since       1.0.0
	 */
	protected $text_prefix = 'COM_FIELDSANDFILTERS';
	
	/**
	 * Item cache.
	 * @since       1.0.0
	 */
	protected $_cache = array();
	
	/**
	 * @since       1.0.0
	 **/
	protected $_item_states = array( -3, -2, -1, 0, 1, 2, 3 );
	
	/**
	 * @since       1.0.0
	 **/
	protected $_dispatcher;
	
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since       1.0.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		if( isset( $config[ 'item_states' ] ) )
		{
			$this->_item_states = $config['item_states'];
		}
		
		if( version_compare( JVERSION, 3.0, '<' ) )
		{
			$this->_dispatcher = JDispatcher::getInstance();
		}
		else
		{
			$this->_dispatcher = JEventDispatcher::getInstance();
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
	public function getTable( $type = 'Element', $prefix = 'FieldsandfiltersTable', $config = array() )
	{
		return JTable::getInstance( $type, $prefix, $config );
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since       1.0.0
	 */
	public function getForm( $data = array(), $loadData = true )
	{
		// The folder and element vars are passed when saving the form.
		if( empty( $data ) ) {
			$item			= $this->getItem();
			$elementID		= $item->element_id;
			$extensionTypeID	= $item->extension_type_id;
			$itemID			= $item->item_id;
		}
		else
		{
			$elementID		= JArrayHelper::getValue( $data, 'element_id' );
			$extensionTypeID	= JArrayHelper::getValue( $data, 'extension_type_id' );
			$itemID			= JArrayHelper::getValue( $data, 'item_id' );
		}
		
		// These variables are used to add data from the plugin XML files.
		$this->setState( 'element.element_id',	$elementID );
		$this->setState( 'element.extension_type_id',	$extensionTypeID );
		$this->setState( 'element.item_id',	$itemID );

		// Get the form.
		$form = $this->loadForm( 'com_fieldsandfilters.element', 'element', array('control' => 'jform', 'load_data' => $loadData ) );
		if( empty( $form ) )
		{
			return false;
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
	protected function preprocessForm( JForm $form, $data, $group = 'content' )
	{
		$jregistry = JRegistry::getInstance( 'fieldsandfilters' );
		$extensionTypeID = $this->getState( 'element.extension_type_id', 0 );
		
		$jregistry->set( 'extension_type_id', $extensionTypeID );
		
		if( $this->prepareFields() && ( $fields = $jregistry->get( 'fields' ) ) )
		{			
			$elementsForm = new JXMLElement( '<fields />' );
			$elementsForm->addAttribute( 'name', 'fields' );
			
			$fieldsetForm = $elementsForm->addChild( 'fieldset'  );
			$fieldsetForm->addAttribute( 'name', 'fields' );
			$fieldsetForm->addAttribute( 'label', 'fields' );
			// $fieldsetForm->addAttribute( 'description', 'COM_MENUS_ITEM_ASSOCIATIONS_FIELDSET_DESC');
			
			JPluginHelper::importPlugin( 'fieldsandfiltersTypes' );
			
			// Trigger the onFieldsandfiltersPrepareFormField event.
			$this->_dispatcher->trigger( 'onFieldsandfiltersPrepareFormField', array( !(boolean) $this->getState( 'element.element_id', 0 ) ) );
			
			if( $fieldsForm = $jregistry->get( 'form.fields' ) )
			{
				$fieldsForm = get_object_vars( $fieldsForm );
				
				ksort( $fieldsForm );
				
				// Load the XML Helper:
				FieldsandfiltersFactory::getXML()->setFields( $fieldsetForm , $fieldsForm );
				
				unset( $fieldsForm );
				
				// add parameters to form
				$form->setFields( $elementsForm, 'fields' );
				
				if( $defaultForm = $jregistry->get( 'form.default' ) )
				{
					// add values to xml form
					foreach( $defaultForm AS $fieldID => $default )
					{
						if( $defaultName = $default->get( 'name' ) )
						{
							$form->setValue( $fieldID, 'fields.' . $defaultName, $default->get( 'default' ) );
						}
						else
						{
							$form->setValue( $fieldID, 'fields', $default->get( 'default' ) );
						}
					}
				}
			}
		}
		// Trigger the default form events.
		parent::preprocessForm( $form, $data, $group );
	}
	
	/**
	 * @since       1.1.0
	 **/
	public function prepareFields( $extensionTypeID = null )
	{
		$extensionTypeID 	= ( !empty( $extensionTypeID ) ) ? (int) $extensionTypeID : (int) $this->getState( 'element.extension_type_id' );
		
		$store = md5( __METHOD__ . $extensionTypeID );
		if( !isset( $this->_cache[$store] ) )
		{
			$return = false;
			
			if( $extensionTypeID )
			{
				// Load PluginExtensions Helper
				$pluginExtensionsHelper = FieldsandfiltersFactory::getPluginExtensions();
				
				if( $extensionName = $this->getState( 'element.extension_name', ( ( $pluginExtension = $pluginExtensionsHelper->getExtensionsByIDPivot( 'extension_type_id', $extensionTypeID )->get( $extensionTypeID ) ) ? $pluginExtension->name : null ) ) )
				{
					JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
					
					$extensionsTypeID = $pluginExtensionsHelper->getExtensionsByNameColumn( 'extension_type_id', array( 'allextensions', $extensionName ) );
					
					$result = $this->_dispatcher->trigger( 'onFieldsandfiltersPrepareFields', array( ( $this->option . '.' . $this->name . '.' . $extensionName ), $extensionsTypeID ) );
					
					if( !in_array( false, $result, true ) )
					{
						$return = true;
					}
				}
			}
			
			$this->_cache[$store] = $return;
		}
		
		return $this->_cache[$store];
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
		$data = JFactory::getApplication()->getUserState( 'com_fieldsandfilters.edit.element.data', array() );

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
	public function getItem( $elementID = null )
	{		
		$row = ( !empty( $elementID ) ) ? (int) $elementID : (int) $this->getState( 'element.element_id' );
		
		if( empty( $row ) )
		{
			$row = array(
					'item_id' 		=> (int) $this->getState( 'element.item_id', 0 ),
					'extension_type_id' 	=> (int) $this->getState( 'element.extension_type_id', 0 ),
				);
		}
		
		$store = md5( __METHOD__ . serialize( $row ) );
		if( !isset( $this->_cache[$store] ) )
		{
			$isNew = true;
			
			// Get a level row instance.
			$table = $this->getTable();
			
			// Attempt to load the row.
			$table->load( $row );
			
			if( empty( $table->element_id ) )
			{
				$table->item_id			= (int) $this->getState( 'element.item_id', 0 );
				$table->extension_type_id 	= (int) $this->getState( 'element.extension_type_id', 0 );
			}
			else
			{
				$isNew = false;
				
				// Load Elements Helper
				if( $element = FieldsandfiltersFactory::getElements()->getElementsByID( $table->extension_type_id, $table->element_id, $this->_item_states, 3 )->get( $table->element_id ) )
				{
					$table->fields = array(
							       'connections' 	=>$element->connections->getProperties( true ),
							       'data' 		=>$element->data->getProperties( true )
							);
				}
				
				$this->setState( 'element.item_id', $table->item_id );
				$this->setState( 'element.extension_type_id', $table->extension_type_id );
				$this->setExtensionState( $table->extension_type_id  );
			}
			
			// prepare fields
			$this->prepareFields( $table->extension_type_id );
			
			// Convert to the JObject before adding other data.
			$properties = $table->getProperties( true );
			
			$item = JArrayHelper::toObject( $properties, 'JObject' );
			
			if( $extensionName = $this->getState( 'element.extension_name' ) )
			{
				// Include the fieldsandfiltersExtensions plugins for the on prepare item events.
				JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
				JPluginHelper::importPlugin( 'fieldsandfiltersTypes' );
				
				// Trigger the onPrepareItem event.
				$result = $this->_dispatcher->trigger( 'onFieldsandfiltersPrepareItem', array( ( $this->option . '.' . $this->name . '.' . $extensionName ), &$item, $isNew, $this->state ) );
				
				if( in_array( false, $result, true ) )
				{
					$this->setError( JText::sprintf( 'COM_FIELDSANDFILTERS_DATABASE_ERROR_PREPARE_ITEM', $this->getState( 'elements.extension_name' ) ) );
					
					return false;
				}
			}
			
			$this->_cache[$store] = $item;
		}
		
		$return = $this->_cache[$store];
		
		$this->setState( 'element.element_id', (int) $return->get( 'element_id', 0 ) );
		
		return $this->_cache[$store];
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 * @since       1.0.0
	 */
	protected function populateState()
	{
		// Get the pk of the record from the request.
		$app 	= JFactory::getApplication( 'administrator' );
		$jinput = $app->input;
		
		$elementID = $jinput->getInt( 'id' );
		$this->setState( 'element.element_id', $elementID );
		
		$itemID = $jinput->getInt( 'itid' );
		$this->setState( 'element.item_id', $itemID );
		
		$extensionTypeID = $jinput->getInt( 'etid' );
		$this->setState( 'element.extension_type_id', $extensionTypeID );
		
		$this->setExtensionState( $extensionTypeID );
		
		// Load the parameters.
		$value = JComponentHelper::getParams( $this->option );
		$this->setState( 'params', $value );
	}
	/**
	 * @since       1.1.0
	 **/
	protected function setExtensionState( $extensionTypeID )
	{
		if( is_numeric( $extensionTypeID ) )
		{
			// Load PluginExtensions Helper
			if( $extensionTypeID && ( $pluginExtension = FieldsandfiltersFactory::getPluginExtensions()->getExtensionsByIDPivot( 'extension_type_id', $extensionTypeID, true )->get( $extensionTypeID ) ) )
			{
				$this->setState( 'element.extension_name', $pluginExtension->name );
				$this->setState( 'element.extension_title', $pluginExtension->title );
			}
		}
	}
	
	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   JTable  $table  A record object.
	 *
	 * @return  array  An array of conditions to add to add to ordering queries.
	 *
	 * @since       1.0.0
	 */
	protected function getReorderConditions( $table )
	{
		$condition = array();
		$condition[] = 'extension_type_id = ' . (int) $table->extension_type_id;
		return $condition;
	}
	
	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   JTable  &$table  A reference to a JTable object.
	 *
	 * @return  void
	 *
	 * @since       1.0.0
	 */
	protected function prepareTable( $table )
	{
		$key = $table->getKeyName();
		if( empty( $table->$key ) )
		{
			if( $elementID = $table->getElementID( $table->extension_type_id, $table->item_id ) )
			{
				$table->$key = $elementID;
			}
		}
	}
	
	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since       1.1.0
	 */
	public function save( $data )
	{
		$table 			= $this->getTable();
		$key 			= $table->getKeyName();
		$pk 			= ( !empty( $data[$key] ) ) ? $data[$key] : (int) $this->getState( $this->getName() . '.id' );
		$isNew 			= true;

		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin( 'content' );

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
			
			// Load PluginExtensions Helper
			$extensionName = ( $extension = FieldsandfiltersFactory::getPluginExtensions()->getExtensionsByIDPivot( 'extension_type_id', $table->extension_type_id )->get( $table->extension_type_id ) ) ? $extension->name : '';
			
			$context = $this->option . '.' . $this->name . '.' . $extensionName;
			
			// Trigger the onContentBeforeSave event.
			$result = $this->_dispatcher->trigger( $this->event_before_save, array( $context, $table, $isNew ) );
			if( in_array( false, $result, true ) )
			{
				$this->setError( $table->getError() );
				return false;
			}
			
			$tableFields = (array) $table->get( 'fields' );
			
			// Get old item
			$item = $this->getItem( $table->$key );
			
			// Store the data.
			if( !$table->store() )
			{
				$this->setError( $table->getError() );
				return false;
			}
			
			$table->set( 'fields', JArrayHelper::toObject( $tableFields, 'JObject' ) );
			
			// Store fields data and connections
			if( $this->prepareFields( $table->extension_type_id ) && ( $fields = JRegistry::getInstance( 'fieldsandfilters' )->get( 'fields' ) ) )
			{
				// Include the fieldsandfilters Types plugins for the on save events.
				JPluginHelper::importPlugin( 'fieldsandfiltersTypes' );
				
				// Trigger the onFieldsandfiltersBeforeSaveData event.
				$result = $this->_dispatcher->trigger( 'onFieldsandfiltersBeforeSaveData', array( ( $this->option . '.' . $this->name ), $table, $item, $isNew ) ); // array( $newItem, $oldItem )
				
				if( in_array( false, $result, true ) )
				{
					$this->setError( $table->getError() );
					return false;
				}
				
				$item 			= $item->get( 'fields', new JObject );
				$dataItem 		= $item->get( 'data', new JObject );
				$connectionsItem 	= $item->get( 'connections', new JObject );
				
				$tableFields 	= $table->get( 'fields', new JObject );
				$data 		= $tableFields->get( 'data', new JObject );
				$connections 	= $tableFields->get( 'connections', new JObject );
				
				// Load PluginTypes Helper
				$pluginTypesHelper 	= FieldsandfiltersFactory::getPluginTypes();
				$filterMode 		= (array) $pluginTypesHelper->getMode( 'filter', array() );
				$otherMode		= (array) $pluginTypesHelper->getModes( null, array(), true, $filterMode );
				
				// Load Array Helper
				$fields = FieldsandfiltersFactory::getArray()->flatten( get_object_vars( $fields ) );
				
				while( $field = array_shift( $fields ) )
				{
					$_data 			= (string) $data->get( $field->field_id, '' );
					$_dataItem		= (string) $dataItem->get( $field->field_id, '' );
					$_connections		= (array) $connections->get( $field->field_id, new JObject )->getProperties( true );
					$_connectionsItem	= (array) $connectionsItem->get( $field->field_id, new JObject )->getProperties( true );
					
					// other ( data/static )
					if( in_array( $field->mode, $otherMode ) && ( !empty( $_data ) || !empty( $_dataItem ) ) )
					{
						$tableObject 		= new stdClass();
						$tableObject->field_id 	= (int) $field->field_id;
						
						// delete text
						if( !empty( $_dataItem ) && empty( $_data ) )
						{
							$table->deleteData( $tableObject );
						}
						// insert text
						elseif( empty( $_dataItem ) && !empty( $_data ) )
						{
							$tableObject->field_data	= $_data;
							
							$table->insertData( $tableObject );	
						}
						// update text
						elseif( $_dataItem != $_data )
						{
							$tableObject->field_data	= $_data;
							
							$table->updateData( $tableObject );
						}
					}
					// filter
					elseif( in_array( $field->mode, $filterMode ) && ( !empty( $_connections ) || !empty( $_connectionsItem ) ) )
					{
						$tableObject 		= new stdClass();
						$tableObject->field_id 	= (int) $field->field_id;
						
						$field_valuesID 	= array_keys( $field->values->getProperties( true ) );
						$_connections		= array_intersect( $field_valuesID, $_connections );
						$__connections 		= array_unique( array_diff( $_connections, $_connectionsItem ) );
						
						JArrayHelper::toInteger( $__connections );
						
						if( !empty( $__connections ) )
						{
							$tableObject->field_value_id = $__connections;
							$table->insertConnections( $tableObject );
						}
						
						$__connections = array_unique( array_diff( $_connectionsItem, $_connections ) );
						
						JArrayHelper::toInteger( $__connections );
						
						if( !empty( $__connections ) )
						{
							$tableObject 			= new stdClass();
							$tableObject->field_value_id 	= $__connections;
							
							$table->deleteConnections( $tableObject );
						}
					}
				}
				
				// Check for errors.
				if( count( $errors = $this->getErrors() ) )
				{
					$this->setError(  implode( "\n", $errors ) );
					return false;
				}
			}
			
			// Clean the cache.
			$this->cleanCache();

			// Trigger the onContentAfterSave event.
			$this->_dispatcher->trigger( $this->event_after_save, array( $context, $table, $isNew ) );
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
	 *
	 * @since       1.1.0
	 */
	public function delete( &$pks )
	{
		$pks = (array) $pks;
		$table = $this->getTable();

		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin( 'content' );
		
		// Load PluginExtensions Helper
		$pluginExtensionsHelper = FieldsandfiltersFactory::getPluginExtensions();
		
		// Iterate the items to delete each one.
		foreach( $pks as $i => $pk )
		{
			if( $table->load( $pk ) )
			{
				if( $this->canDelete( $table ) )
				{
					$extensionName = ( $extension = $pluginExtensionsHelper->getExtensionsByIDPivot( 'extension_type_id', $table->extension_type_id )->get( $table->extension_type_id ) ) ? $extension->name : '';
					
					$context = $this->option . '.' . $this->name . '.' .  $extensionName;
					
					// Trigger the onContentBeforeDelete event.
					$result = $this->_dispatcher->trigger( $this->event_before_delete, array( $context, $table ) );
					if( in_array( false, $result, true ) )
					{
						$this->setError( $table->getError() );
						return false;
					}
					
					// Store fields data and connections
					if( $this->prepareFields( $table->extension_type_id ) && ( $fields = JRegistry::getInstance( 'fieldsandfilters' )->get( 'fields' ) ) )
					{
						// Include the fieldsandfilters Types plugins for the on save events.
						JPluginHelper::importPlugin( 'fieldsandfiltersTypes' );
						
						// Get old item
						$item = $this->getItem( $pk );
						
						// Trigger the onFieldsandfiltersBeforeSaveData event.
						$result = $this->_dispatcher->trigger( 'onFieldsandfiltersBeforeDeleteData', array( ( $this->option . '.' . $this->name ), $item ) );
						
						if( in_array( false, $result, true ) )
						{
							$this->setError( $table->getError() );
							return false;
						}
						
						// delete fields data element
						if( !$table->deleteData( $pk ) )
						{
							$this->setError( $table->getError() );
							return false;
						}
						
						// delete fields connections element
						if( !$table->deleteConnections( $pk ) )
						{
							$this->setError( $table->getError() );
							return false;
						}
						
					}
					
					if( !$table->delete( $pk ) )
					{
						$this->setError( $table->getError() );
						return false;
					}
					
					// Trigger the onContentAfterDelete event.
					$this->_dispatcher->trigger( $this->event_after_delete, array( $context, $table ) );
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