<?php

/**
 * @version     1.0.0
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */
// No direct access
defined('_JEXEC') or die;

/**
 * field Table class
 */
class FieldsandfiltersTableField extends JTable {

	/**
	 * Constructor
	 *
	 * @param JDatabase A database connector object
	 */
	public function __construct( &$db )
	{
		parent::__construct( '#__fieldsandfilters_fields', 'field_id', $db );
	}

	/**
	 * Method to bind an associative array or object to the JTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $array     An associative array or object to bind to the JTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTable/bind
	 * @since   11.1
	 */
	public function bind( $array, $ignore = '' )
	{
		
		
		if( isset( $array['params'] ) && is_array( $array['params'] ) )
		{
			$registry = new JRegistry();
			$registry->loadArray( $array['params'] );
			$array['params'] = (string) $registry;
		}
	    /*
		    if(!JFactory::getUser()->authorise('core.edit.state','com_fieldsandfilters') && $array['state'] == 1){
			    $array['state'] = 0;
		    }
    
	    
    
	    if (isset($array['metadata']) && is_array($array['metadata'])) {
		$registry = new JRegistry();
		$registry->loadArray($array['metadata']);
		$array['metadata'] = (string) $registry;
	    }
	    if(!JFactory::getUser()->authorise('core.admin', 'com_fieldsandfilters.field.'.$array['id'])){
		$actions = JFactory::getACL()->getActions('com_fieldsandfilters','field');
		$default_actions = JFactory::getACL()->getAssetRules('com_fieldsandfilters.field.'.$array['id'])->getData();
		$array_jaccess = array();
		foreach($actions as $action){
		    $array_jaccess[$action->name] = $default_actions[$action->name];
		}
		$array['rules'] = $this->JAccessRulestoArray($array_jaccess);
	    }
	    //Bind the rules for ACL where supported.
		    if (isset($array['rules']) && is_array($array['rules'])) {
			    $this->setRules($array['rules']);
		    }
		*/
    
	    return parent::bind( $array, $ignore );
	}
    
	/**
	 * This function convert an array of JAccessRule objects into an rules array.
	 * @param type $jaccessrules an arrao of JAccessRule objects.
	 */
	private function JAccessRulestoArray($jaccessrules){
		$rules = array();
		foreach($jaccessrules as $action => $jaccess){
			$actions = array();
			foreach($jaccess->getData() as $group => $allow){
				$actions[$group] = ((bool)$allow);
			}
			$rules[$action] = $actions;
		}
		return $rules;
	}

	/**
	 * Method to perform sanity checks on the JTable instance properties to ensure
	 * they are safe to store in the database.  Child classes should override this
	 * method to make sure the data they are storing in the database is safe and
	 * as expected before storage.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @link    http://docs.joomla.org/JTable/check
	 * @since   11.1
	 */
	public function check()
	{
		// Load PluginTypes Helper
		JLoader::import( 'helpers.fieldsandfilters.plugintypes', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
		
		// Load pluginExtensions Helper
		JLoader::import( 'helpers.fieldsandfilters.pluginextensions', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
		
		//If there is an ordering column and this is a new row then get the next ordering value
		if( $this->field_id == 0 )
		{
			$this->ordering = self::getNextOrder();
		}
		
		// Check for a field name.
		if( trim( $this->field_name ) == '' )
		{
			$this->setError( JText::_( 'COM_FIELDSANDFILTERS_DATABASE_ERROR_VALID_FIELD_NAME' ) );
			return false;
		}
		
		// Check for a field type.
		if( trim( $this->field_type ) == '' )
		{
			$this->setError( JText::_( 'COM_FIELDSANDFILTERS_DATABASE_ERROR_VALID_FIELD_TYPE' ) );
			return false;
		}
		elseif( !FieldsandfiltersPluginTypesHelper::getInstance()->getTypes()->get( $this->field_type ) )
		{
			$this->setError( JText::sprintf( 'COM_FIELDSANDFILTERS_DATABASE_ERROR_FIELD_TYPE_NOT_EXISTS', $this->field_type ) );
			return false;
		}
		
		// Check for a field extension type id.
		if( !$this->extension_type_id )
		{
			$this->setError( JText::_( 'COM_FIELDSANDFILTERS_DATABASE_ERROR_VALID_FIELD_EXTENSION_TYPE_ID' ) );
			return false;
		}
		elseif( !FieldsandfiltersPluginExtensionsHelper::getInstance()->getExtensionsPivot( 'extension_type_id' )->get( $this->extension_type_id ) )
		{
			$this->setError( JText::sprintf( 'COM_FIELDSANDFILTERS_DATABASE_ERROR_EXTENSION_TYPE_ID_NOT_EXISTS', $this->extension_type_id ) );
			return false;
		}
		
		// Check mode field
		if( in_array( $this->mode, (array) FieldsandfilterspluginTypesHelper::getInstance()->getMode( 'values' ) ) )
		{
			// Check for a field alias
			if( trim( $this->field_alias ) == '' )
			{
				$this->field_alias = $this->field_name;
			}
		       
			$this->field_alias = JApplication::stringURLSafe( $this->field_alias );
		       
			if( trim( str_replace( '-', '', $this->field_alias ) ) == '' )
			{
				$this->field_alias = JFactory::getDate()->format( 'Y-m-d-H-i-s' );
			}
		}
		elseif( trim( $this->field_alias ) != '' )
		{
			$this->field_alias = '';
		}
		
		return true;
	}
	
	/**
	 * Overrides JTable::store to set modified data and user id.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function store( $updateNulls = false )
	{
		// Load PluginTypes Helper
		JLoader::import( 'helpers.fieldsandfilters.plugintypes', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
		
		// Check mode field
		if( in_array( $this->mode, (array) FieldsandfilterspluginTypesHelper::getInstance()->getMode( 'values' ) ) )
		{
			// Verify that the alias is unique
			$table = JTable::getInstance( 'Field', 'FieldsandfiltersTable' );
			
			if( $table->load( array( 'field_alias' => $this->field_alias ) ) && ( $table->field_id != $this->field_id || $this->field_id == 0 ) )
			{
				$this->setError( JText::sprintf( 'COM_FIELDSANDFILTERS_DATABASE_ERROR_FIELD_ALIAS_EXISTS', $this->field_alias ) );
				return false;
			}
		}
		
		return parent::store( $updateNulls );
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table. The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.  If not set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published, -1 = onlyadmin state]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function publish( $pks = null, $state = 1, $userId = 0 )
	{
		$k = $this->_tbl_key;
		
		// Sanitize input.
		JArrayHelper::toInteger( $pks );
		$userId = (int) $userId;
		$state = (int) $state;
		
		// If there are no primary keys set check to see if the instance key is set.
		if( empty( $pks ) )
		{
			if( $this->$k )
			{
				$pks = array( $this->$k );
			}
			// Nothing to set publishing state on, return false.
			else
			{
				$this->setError( JText::_( 'JLIB_DATABASE_ERROR_NO_ROWS_SELECTED' ) );
				return false;
			}
		}
		
		// Get the JDatabaseQuery object
		$query = $this->_db->getQuery( true );
		
		// Update the publishing state for rows with the given primary keys.
		$query->update( $this->_db->quoteName( $this->_tbl ) );
		$query->set( $this->_db->quoteName( 'state' ) . ' = ' . (int) $state );
		
		// Build the WHERE clause for the primary keys.
		$query->where( $this->_db->quoteName( $k ) . ' IN (' . implode( ',', $pks ) . ')' );
		
		$this->_db->setQuery( $query );
		
		try
		{
			$this->_db->execute();
		}
		catch( RuntimeException $e )
		{
			$this->setError( $e->getMessage() );
			return false;
		}
		
		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if( in_array( $this->$k, $pks ) )
		{
			$this->state = $state;
		}
		
		$this->setError('');
		
		return true;
	}
	
	/**
	 * Method to set the requiring state for a row or list of rows in the database
	 * table. The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.  If not set the instance property value is used.
	 * @param   integer  $state   The requiring state. eg. [0 = unrequired, 1 = required]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function required( $pks = null, $state = 1, $userId = 0 )
	{
		$k = $this->_tbl_key;
		
		// Sanitize input.
		JArrayHelper::toInteger( $pks );
		$userId = (int) $userId;
		$state = (int) $state;
		
		// If there are no primary keys set check to see if the instance key is set.
		if( empty( $pks ) )
		{
			if( $this->$k )
			{
				$pks = array( $this->$k );
			}
			// Nothing to set publishing state on, return false.
			else
			{
				$this->setError( JText::_( 'JLIB_DATABASE_ERROR_NO_ROWS_SELECTED' ) );
				return false;
			}
		}
		
		// Get the JDatabaseQuery object
		$query = $this->_db->getQuery( true );
		
		// Update the publishing state for rows with the given primary keys.
		$query->update( $this->_db->quoteName( $this->_tbl ) );
		$query->set( $this->_db->quoteName( 'required' ) . ' = ' . (int) $state );
		
		// Build the WHERE clause for the primary keys.
		$query->where( $this->_db->quoteName( $k ) . ' IN (' . implode( ',', $pks ) . ')' );
		
		$this->_db->setQuery( $query );
		
		try
		{
			$this->_db->execute();
		}
		catch( RuntimeException $e )
		{
			$this->setError( $e->getMessage() );
			return false;
		}
		
		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if( in_array( $this->$k, $pks ) )
		{
			$this->state = $state;
		}
		
		$this->setError('');
		
		return true;
	}
}
