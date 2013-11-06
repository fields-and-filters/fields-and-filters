<?php
/**
 * @version     1.1.1
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kulka.tomek@gmail.com> - 
 */

// No direct access.
defined( '_JEXEC' ) or die;

if( !FieldsandfiltersFactory::isVersion() )
{
	jimport( 'joomla.application.component.modeladmin' );
}

/**
 * Fieldsandfilters model.
 * @since	1.0.0
 */
class FieldsandfiltersModelFieldvalue extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.0.0
	 */
	protected $text_prefix = 'COM_FIELDSANDFILTERS';
	
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.0.0
	 */
	public function getTable( $type = 'Fieldvalue', $prefix = 'FieldsandfiltersTable', $config = array() )
	{
		return JTable::getInstance( $type, $prefix, $config );
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.0.0
	 */
	public function getForm( $data = array(), $loadData = true )
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm( 'com_fieldsandfilters.fieldvalue', 'fieldvalue', array( 'control' => 'jform', 'load_data' => $loadData ) );
		
		if( empty( $form ) )
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.0.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState( 'com_fieldsandfilters.edit.fieldvalue.data', array() );
		
		if( empty( $data ) )
		{
			$data = $this->getItem();
            
		}
		
		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.0.0
	 */
	public function getItem( $pk = null )
	{
		$pk = ( !empty( $pk ) ) ? (int) $pk : (int) $this->getState( 'fieldvalue.id' );
		
		$item = parent::getItem( $pk );
		
		return $item;
	}
	
	/**
	 * Auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 * @since	1.0.0
	 */
	protected function populateState()
	{
		// Get the pk of the record from the request.
		$app 	= JFactory::getApplication( 'administrator' );
		$jinput = $app->input;
		
		$fieldvalueID = $jinput->getInt( 'id' );
		$this->setState( 'fieldvalue.id', $fieldvalueID );
		
		// Load the parameters.
		$value = JComponentHelper::getParams( $this->option );
		$this->setState( 'params', $value );
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	0.1.0
	
	protected function prepareTable(&$table)
	{
		jimport( 'joomla.filter.output' );

		if( empty( $table->id)) {

			// Set ordering to the last item if not set
			if( @$table->ordering === '' ) {
				$db = JFactory::getDbo( );
				$db->setQuery( 'SELECT MAX(ordering) FROM #__fieldsandfilters_field_values' );
				$max = $db->loadResult( );
				$table->ordering = $max+1;
			}

		}
	}
	 */
	
	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   JTable  $table  A JTable object.
	 *
	 * @return  array  An array of conditions to add to ordering queries.
	 *
	 * @since	1.0.0
	 */
	protected function getReorderConditions( $table )
	{
		$condition = array();
		$condition[] = 'field_id = ' . (int) $table->field_id;
		return $condition;
	}
	
	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since	1.0.0
	 */
	public function delete( &$pks )
	{
		$pks 		= (array) $pks;
		$table 		= $this->getTable();
		$elementTable 	= $this->getTable( 'Element', 'FieldsandfiltersTable' );
		$dispatcher	= FieldsandfiltersFactory::getDispatcher();
		
		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin( 'content' );
		
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
					
					$objectTable 			= new stdClass();
					$objectTable->field_value_id 	= $table->field_value_id;
					
					//delete connections
					if( !$elementTable->deleteConnections( $objectTable ) )
					{
						$this->setError( $elementTable->getError() );
						return false;
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