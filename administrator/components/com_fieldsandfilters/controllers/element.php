<?php
/**
 * @version     1.0.0
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
 * Element controller class.
 * @since       1.0.0
 */
class FieldsandfiltersControllerElement extends JControllerForm
{
	/**
	 * @since       1.0.0
	 **/
	public function __construct()
	{
		$this->view_list = 'elements';
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
	 * @since       1.0.0
	 */
	public function save( $key = 'element_id', $urlVar = 'id' )
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
	 * @since       1.0.0
	 */
	public function edit( $key = 'element_id', $urlVar = 'id' )
	{
                parent::edit( $key, $urlVar ); 
        }
        
        /**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since       1.0.0
	 */
	protected function getRedirectToItemAppend( $recordId = null, $urlVar = 'id' )
	{
		$append                 = parent::getRedirectToItemAppend( $recordId, $urlVar );
                $jinput                 = JFactory::getApplication()->input;
                $itemID                 = $jinput->get( 'itid', 0, 'int' );
                $extensionTypeID	= $jinput->get( 'etid', 0, 'int' );
		
		if( !$recordId && !$itemID && !$extensionTypeID )
		{
			$jform = $jinput->get( 'jform', array(), 'array' );
			
			$itemID			= JArrayHelper::getValue( $jform, 'item_id', 0, 'int' );
			$extensionTypeID	= JArrayHelper::getValue( $jform, 'extension_type_id', 0, 'int' );
		}
                
		// Setup redirect info.
		if( $extensionTypeID )
		{
			$append .= '&etid=' . $extensionTypeID;
		}
                if( $itemID )
		{
			$append .= '&itid=' . $itemID;
		}
		
		return $append;
	}
}