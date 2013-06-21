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

class FieldsandfiltersControllerFilters extends JControllerLegacy
{
        protected static $app_swap;
        protected static $doc_swap;
        
        
        public function filters()
        {
                if( empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest' )
                {
                        die;
                }
                
                // Check for a valid token. If invalid, send a 403 with the error message.
		JSession::checkToken( 'request' ) or $this->sendResponse( new Exception( JText::_( 'JINVALID_TOKEN' ), 403 ) );
                
                $app                    = JFactory::getApplication();
                $jinput                 = $app->input;
                $extensionTypeID        = $jinput->get( 'extensionID', 0, 'int' );
                $requestID              = $jinput->get( 'requestID', 0, 'double' );
                
                // Check if exist requestID and extention type id
                if( !$extensionTypeID || !$requestID )
                {
                        $this->sendResponse( new Exception( JText::_( 'COM_FILEDSANDFILTERS_FILTERS_ERROR_EXTENSION_REQUEST_ID' ), 403 ) );
                }
                
                $filtersInput = $jinput->get( 'fieldsandfilters', array(), 'array' );
                
                // Load PluginExtensions Helper
                JLoader::import( 'helpers.fieldsandfilters.pluginextensions', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
                $extensions = FieldsandfiltersPluginExtensionsHelper::getInstance()->getExtensionsByIDPivot( 'extension_type_id', $extensionTypeID );
                
                if( !( $extension =  $extensions->get( $extensionTypeID ) ) )
                {
                        $this->sendResponse( new Exception( JText::_( 'COM_FILEDSANDFILTERS_FILTERS_ERROR_EXTENSION_NOT_EXISTS' ), 403 ) );
                }
                
                /*
		 * We are going to swap out the raw document object with an HTML document
		 * in order to work around some plugins that don't do proper environment
		 * checks before trying to use HTML document functions.
		 */
		self::$doc_swap = clone( JFactory::getDocument() );
		$lang = JFactory::getLanguage();

		// Get the document properties.
		$attributes = array (
			'charset'	=> 'utf-8',
			'lineend'	=> 'unix',
			'tab'		=> '  ',
			'language'	=> $lang->getTag(),
			'direction'	=> $lang->isRTL() ? 'rtl' : 'ltr'
		);
                
		// Get the HTML document.
		$html = JDocument::getInstance( 'html', $attributes );
                
		// Swap the documents.
		JFactory::$document = $html;

		// Get the admin application.
		self::$app_swap = clone( JFactory::getApplication() );

		// Get the site app.
                include_once JPATH_SITE . '/includes/application.php';
		$site = JApplication::getInstance( 'site' );
                $site->input->set( 'format', 'html' );
                
                // clear extension type ID
                $site->getRouter()->setVar( 'extensionID', '' );
                
		// Swap the app.
		JFactory::$application = $site;
		
		
                
                try
		{
			JPluginHelper::importPlugin( 'fieldsandfiltersExtensions' );
                        // Trigger the onFieldsandfiltersPrepareFormField event.
                        $context = 'com_fieldsandfilters.filters.' . $extension->name;
                        
                        JDispatcher::getInstance()->trigger( 'onFieldsandfiltersFiltersDisplay', array( $context ) );
                        
			// Send the response.
			$this->sendResponse( null );
		}
		// Catch an exception and return the response.
		catch( Exception $e )
		{
			$this->sendResponse( $e );
		}
        }
        
        /**
	 * Method to handle a send a JSON response. The body parameter
	 * can be a Exception object for when an error has occurred or
	 * a JObject for a good response.
	 *
	 * @param   mixed  $data  JObject on success, Exception on error. [optional]
	 */
	public static function sendResponse( $data = null )
	{
                $app            = JFactory::getApplication();
                // Create the response object.
                $response       = new JObject;
                
                // Send the assigned error code if we are catching an exception.
		if( $data instanceof Exception )
		{
                        // Log the error
			JLog::add( $data->getMessage(), JLog::ERROR );
                        
                        JResponse::setHeader( 'status', $data->getCode() );
			JResponse::sendHeaders();
                        
			// Prepare the error response.
			$response->error = true;
			$response->header = JText::_( 'COM_FILEDSANDFILTERS_FILTERS_ERROR_HEADER' );
			$response->message = $data->getMessage();
		}
                else
                {
                        
                        $document = JFactory::getDocument();
                        
			// Prepare the response data.
			$response->head = $document->getHeadData();
                        $response->body = $document->getBuffer( 'component', 'fieldsandfilters' );
                        $response->hash = md5( serialize( $app->input->get( 'fieldsandfilters' , array(), 'array' ) ) );
                        
                        $jregistry = JRegistry::getInstance( 'fieldsandfilters' )->get( 'filters', array() );
                        $response->setProperties( $jregistry );
                        
                }
                
                // The old token is invalid so send a new one.
		$response->token            = JFactory::getSession()->getFormToken();
                $response->requestID        = $app->input->get( 'requestID', 0, 'double' );

		// Add the buffer.
		// $response->buffer = JDEBUG ? ob_get_contents() : ob_end_clean();
                
                if( self::$app_swap && self::$app_swap instanceof JApplication )
                {
                        JFactory::$application = self::$app_swap;
                        
                        self::$app_swap = null;
                }
                
                if( self::$doc_swap && self::$doc_swap instanceof JDocument )
                {
                        JFactory::$document = self::$doc_swap;
                        
                        self::$doc_swap = null;
                }
                
		// Send the JSON response.
		echo json_encode( $response );
                
		// Close the application.
		$app->close();
	}
}

// Register the error handler.
JError::setErrorHandling( E_ALL, 'callback', array( 'FieldsandfiltersControllerFilters', 'sendResponse' ) );
