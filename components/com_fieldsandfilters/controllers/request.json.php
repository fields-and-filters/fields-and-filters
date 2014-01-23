<?php
/**
 * @version     1.1.1
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
	protected static $app_swap;

	/**
	 * @since       1.1.0
	 */
	protected static $doc_swap;

	/**
	 * @since       1.1.0
	 */
	public function filters()
	{
		// Is ajax requerst
		KextensionsRequest::isAjax() OR die();

		// Check for a valid token. If invalid, send a 403 with the error message.
		JSession::checkToken('request') OR $this->sendResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

		$app             = JFactory::getApplication();
		$extensionTypeID = $app->input->get('extensionID', 0, 'int');
		$requestID       = $app->input->get('requestID', 0, 'alnum');

		// Check if exist requestID and extention type id
		if (!$requestID)
		{
			$this->sendResponse(new Exception(JText::_('COM_FILEDSANDFILTERS_FILTERS_ERROR_REQUEST_ID'), 403));
		}
		else
		{
			if (!$extensionTypeID)
			{
				$this->sendResponse(new Exception(JText::_('COM_FILEDSANDFILTERS_FILTERS_ERROR_REQUEST_ID'), 403));
			}
		}

		// Load PluginExtensions Helper
		$extensions = FieldsandfiltersFactory::getExtensions()->getExtensionsByTypeID($extensionTypeID);

		if (!($extension = $extensions->get($extensionTypeID)))
		{
			$this->sendResponse(new Exception(JText::_('COM_FILEDSANDFILTERS_FILTERS_ERROR_EXTENSION_NOT_EXISTS'), 403));
		}

		self::_prepareDocument();

		try
		{
			JPluginHelper::importPlugin('fieldsandfiltersextensions');

			// Trigger the onFieldsandfiltersPrepareFormField event.
			$context = 'com_fieldsandfilters.filters.' . $extension->name;

			$app->triggerEvent('onFieldsandfiltersRequestJSON', array($context));

			// Send the response.
			$this->sendResponse(null, 'filters');
		} catch (Exception $e)
		{
			$this->sendResponse($e);
		}
	}

	/**
	 * @since       1.1.0
	 */
	public function fields()
	{
		// Is ajax requerst
		KextensionsRequest::isAjax() OR die();

		// Check for a valid token. If invalid, send a 403 with the error message.
		JSession::checkToken('request') OR $this->sendResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

		$app             = JFactory::getApplication();
		$requestID       = $app->input->get('requestID', 0, 'alnum');
		$fieldID         = $app->input->get('fieldID', 0, 'int');
		$extensionTypeID = $app->input->get('extensionID', 0, 'int');

		// Check if exist requestID and extention type id
		if (!$requestID)
		{
			$this->sendResponse(new Exception(JText::_('COM_FILEDSANDFILTERS_FILTERS_ERROR_REQUEST_ID'), 403));
		}
		else
		{
			if (!$extensionTypeID)
			{
				$this->sendResponse(new Exception(JText::_('COM_FILEDSANDFILTERS_FILTERS_ERROR_REQUEST_ID'), 403));
			}
			else
			{
				if (!$fieldID)
				{
					$this->sendResponse(new Exception(JText::_('COM_FILEDSANDFILTERS_FILTERS_ERROR_FIELD_ID'), 403));
				}
			}
		}

		// Load PluginExtensions Helper
		if (!($extension = FieldsandfiltersFactory::getExtensions()->getExtensionsByTypeID($extensionTypeID)->get($extensionTypeID)))
		{
			$this->sendResponse(new Exception(JText::_('COM_FILEDSANDFILTERS_FILTERS_ERROR_EXTENSION_NOT_EXISTS'), 403));
		}
		else
		{
			if (!($field = FieldsandfiltersFactory::getFields()->getFieldsByID($extension->extension_type_id, $fieldID)->get($fieldID)))
			{
				$this->sendResponse(new Exception(JText::_('COM_FILEDSANDFILTERS_FILTERS_ERROR_FIELD_NOT_EXISTS'), 403));
			}
		}

		self::_prepareDocument();

		try
		{
			JPluginHelper::importPlugin('fieldsandfilterstypes');

			// Trigger the onFieldsandfiltersPrepareFormField event.
			$context = 'com_fieldsandfilters.fields.' . $field->field_type;

			$app->triggerEvent('onFieldsandfiltersRequestJSON', array($context, $field));

			// Send the response.
			$this->sendResponse(null, 'fields');
		} // Catch an exception and return the response.
		catch (Exception $e)
		{
			$this->sendResponse($e);
		}
	}

	/**
	 * @since       1.1.0
	 */
	protected static function _prepareDocument()
	{
		/*
		 * We are going to swap out the raw document object with an HTML document
		 * in order to work around some plugins that don't do proper environment
		 * checks before trying to use HTML document functions.
		 */
		self::$doc_swap = clone(JFactory::getDocument());
		$lang           = JFactory::getLanguage();

		// Get the document properties.
		$attributes = array(
			'charset'   => 'utf-8',
			'lineend'   => 'unix',
			'tab'       => '  ',
			'language'  => $lang->getTag(),
			'direction' => $lang->isRTL() ? 'rtl' : 'ltr'
		);

		// Get the HTML document.
		$html = JDocument::getInstance('html', $attributes);

		// Swap the documents.
		JFactory::$document = $html;

		// Get the admin application.
		self::$app_swap = clone(JFactory::getApplication());

		// Get the site app.
		include_once JPATH_SITE . '/includes/application.php';
		$site = JApplication::getInstance('site');
		$site->input->set('format', 'html');

		// clear extension type ID
		$site->getRouter()->setVar('extensionID', '');

		// Swap the app.
		JFactory::$application = $site;
	}

	/**
	 * Method to handle a send a JSON response. The body parameter
	 * can be a Exception object for when an error has occurred or
	 * a JObject for a good response.
	 *
	 * @param   mixed $data JObject on success, Exception on error. [optional]
	 *
	 * @since       1.1.0
	 */
	public static function sendResponse($data = null, $type = null)
	{
		$app = JFactory::getApplication();
		// Create the response object.
		$response = new JObject;

		// Send the assigned error code if we are catching an exception.
		if ($data instanceof Exception)
		{
			// Log the error
			JLog::add($data->getMessage(), JLog::ERROR);

			JResponse::setHeader('status', $data->getCode());
			JResponse::sendHeaders();

			// Prepare the error response.
			$response->error   = true;
			$response->header  = JText::_('COM_FILEDSANDFILTERS_FILTERS_ERROR_HEADER');
			$response->message = $data->getMessage();
		}
		else
		{
			if ($type)
			{

				$document = JFactory::getDocument();

				// Prepare the response data.
				$response->head = $document->getHeadData();
				$response->body = $document->getBuffer('component', 'fieldsandfilters');
				$response->hash = md5(serialize($app->input->get('fieldsandfilters', array(), 'array')));

				$jregistry = JRegistry::getInstance('fieldsandfilters')->get($type, array());

				$response->setProperties($jregistry);

			}
		}

		// The old token is invalid so send a new one.
		$response->token     = JFactory::getSession()->getFormToken();
		$response->requestID = $app->input->get('requestID', 0, 'alnum');

		// Add the buffer.
		// $response->buffer = JDEBUG ? ob_get_contents() : ob_end_clean();

		if (self::$app_swap && self::$app_swap instanceof JApplication)
		{
			JFactory::$application = self::$app_swap;

			self::$app_swap = null;
		}

		if (self::$doc_swap && self::$doc_swap instanceof JDocument)
		{
			JFactory::$document = self::$doc_swap;

			self::$doc_swap = null;
		}

		// Send the JSON response.
		echo json_encode($response);

		// Close the application.
		$app->close();
	}
}

// Register the error handler.
// set_error_handler( array( 'FieldsandfiltersControllerRequest', 'sendResponse' ) );