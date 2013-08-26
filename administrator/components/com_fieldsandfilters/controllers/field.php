<?php
/**
 * @version     1.1.1
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access
defined('_JEXEC') or die;

if( !FieldsandfiltersFactory::isVersion() )
{
	jimport( 'joomla.application.component.controllerform' );
}

/**
 * Field controller class.
 * @since       1.1.0
 */
class FieldsandfiltersControllerField extends JControllerForm
{

        function __construct()
        {
                $this->view_list = 'fields';
                parent::__construct();
        }
        
        /**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 * @since       1.0.0
	 */
	public function save( $key = 'field_id', $urlVar = 'id' )
	{
                parent::save( $key, $urlVar );       
        }
        
        /**
	 * Method to edit an existing record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key
	 * (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if access level check and checkout passes, false otherwise.
	 * @since       1.0.0
	 */
	public function edit( $key = 'field_id', $urlVar = 'id' )
	{
                parent::edit( $key, $urlVar ); 
        }
	
	/**
	 * Sets the type of the field item currently being edited.
	 *
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key
	 *
	 * @return  void
	 * @since       1.1.0
	 */
	function setType( $urlVar = 'id' )
	{
		// Initialise variables.
		$app 	        = JFactory::getApplication();
		$jinput         = $app->input;
                
		// Get the posted values from the request.
		$data           = $jinput->post->get( 'jform', array(), 'array' );
		$recordId       = $jinput->get( $urlVar, 0, 'int' );
                
		// Get the type.
		$type = new JRegistry( base64_decode( $data['temp_type'] ) );
                
		if( ( $name = $type->get( 'name' ) ) && ( $typeMode = $type->get( 'mode' ) ) && $type->get( 'type' ) == 'fieldsandfiltersTypes' && $recordId  == $type->get( 'id', 0 ) )
		{
			$data['field_type'] 	= $name;
                        $data['type_mode'] 	= $typeMode;
			
			//Save the data in the session.
			$app->setUserState( 'com_fieldsandfilters.edit.field.data', $data );
			
			$this->setRedirect( JRoute::_( 'index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend( $recordId, $urlVar ), false ) );
			return true;
		}
		
		$this->setRedirect( JRoute::_( 'index.php?option=' . $this->option . '&view=' . $this->view_list, false ) );
		return false;
	}
        
        /**
	 * Sets the extension type of the field item currently being edited.
	 *
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key
	 *
	 * @return  void
	 * @since       1.1.0
	 */        
        function setExtension( $urlVar = 'id' )
	{
		// Initialise variables.
		$app 	        = JFactory::getApplication();
		$jinput         = $app->input;
                $types          = array( 'com_fieldsandfilters', 'fieldsandfiltersExtensions' );
                
		// Get the posted values from the request.
		$data           = $jinput->post->get( 'jform', array(), 'array' );
		$recordId       = $jinput->get( $urlVar, 0, 'int' );
		
		// Get the type.
                $extension = new JRegistry( base64_decode( $data['temp_extension'] ) );
		
                if( ( $extensionTypeId = $extension->get( 'extension_type_id' ) ) && ( $typeExtension = $extension->get( 'extension' ) ) && in_array( $extension->get( 'type' ), $types ) && $recordId  == $extension->get( 'id', 0 ) )
		{
			$data['extension_type_id'] 	= $extensionTypeId;
			$data['type_extension'] 	= $typeExtension;
			
			//Save the data in the session.
			$app->setUserState( 'com_fieldsandfilters.edit.field.data', $data );
			
			$this->setRedirect( JRoute::_( 'index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend( $recordId, $urlVar ), false ) );
			return true;
		}
		
		$this->setRedirect( JRoute::_( 'index.php?option=' . $this->option . '&view=' . $this->view_list, false ) );
		return false;
	}

}