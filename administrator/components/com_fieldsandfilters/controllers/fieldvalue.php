<?php
/**
 * @version     1.1.0
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kulka.tomek@gmail.com> - 
 */

// No direct access
defined('_JEXEC') or die;

if( !FieldsandfiltersFactory::isVersion() )
{
	jimport( 'joomla.application.component.controllerform' );
}

/**
 * Fieldvalue controller class.
 * @since	1.0.0
 */
class FieldsandfiltersControllerFieldvalue extends JControllerForm
{

        function __construct()
        {
                $this->view_list = 'fieldvalues';
                parent::__construct();
        }
        
        /**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since	1.0.0
	 */
	public function save( $key = 'field_value_id', $urlVar = 'id' )
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
	 *
	 * @since	1.0.0
	 */
	public function edit( $key = 'field_value_id', $urlVar = 'id' )
	{                   
                parent::edit( $key, $urlVar ); 
        }

}