<?php
/**
 * @version     1.0.0
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access.
defined('_JEXEC') or die;

/**
* @since       1.1.0
*/
class FieldsandfiltersControllerRequest extends JControllerLegacy
{
	/**
	 * @since       1.1.0
	 */
	protected $_dispatcher;
	
        /**
	 * @since       1.1.0
	 */
	public function __construct( $config = array() )
	{
		parent::__construct( $config );
		
		if( FieldsandfiltersFactory::isVersion() )
		{
			$this->_dispatcher = JEventDispatcher::getInstance();
		}
		else
		{
			$this->_dispatcher = JDispatcher::getInstance();
		}
	}
	
        /**
	 * @since       1.1.0
	 */
        public function fields()
	{
                // Check for a valid token. If invalid, send a 403 with the error message.
		JSession::checkToken( 'request' ) OR die();
                
		$app = JFactory::getApplication();
                JPluginHelper::importPlugin( 'fieldsandfiltersTypes' );
                
                $app 		        = JFactory::getApplication();
		$fieldID 	        = $app->input->get( 'fid', 0, 'int' );
		$extensionTypeID 	= $app->input->get( 'etid', 0, 'int' );
		
		if( $extensionID && $fieldID )
		{
                        // Load PluginExtensions Helper
                        if( !( $extension = FieldsandfiltersFactory::getPluginExtensions()->getExtensionsByIDPivot( 'extension_type_id', $extensionTypeID )->get( $extensionTypeID ) ) )
                        {
                                // $app->enqueueMessage( JText::_( JText::_( 'COM_FILEDSANDFILTERS_FILTERS_ERROR_EXTENSION_NOT_EXISTS' ), 'error' ) );
                        }
                        else if( !( $field = FieldsandfiltersFactory::getFields()->getFieldsByID( $extension->extension_type_id, $fieldID )->get( $fieldID ) ) )
                        {
                                // $app->enqueueMessage( JText::_( JText::_( 'COM_FILEDSANDFILTERS_FILTERS_ERROR_FIELD_NOT_EXISTS' ), 'error' ) );
                        }
                        else
                        {
                                $context = 'com_fieldsandfilters.fields.' . $field->field_type;
                                $this->_dispatcher->trigger( 'onFieldsandfiltersFieldsControllerRaw', array( $context, $field ) );
                        }
		}
		
		$this->setRedirect( JURI::root( true ) );
	}
}

