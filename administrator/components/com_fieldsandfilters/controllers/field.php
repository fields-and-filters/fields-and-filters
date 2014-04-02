<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access
defined('_JEXEC') or die;

if (!FieldsandfiltersFactory::isVersion())
{
	jimport('joomla.application.component.controllerform');
}

/**
 * Field controller class.
 *
 * @since       1.1.0
 */
class FieldsandfiltersControllerField extends JControllerForm
{

	function __construct()
	{
		parent::__construct();

		/* @deprecated J3.x */
		if (!FieldsandfiltersFactory::isVersion())
		{
			$this->input = JFactory::getApplication()->input;
		}
		/* @end deprecated J3.x */
	}

	/**
	 * Sets the type of the field item currently being edited.
	 *
	 * @param   string $urlVar The name of the URL variable if different from the primary key
	 *
	 * @return  void
	 * @since       1.1.0
	 */
	function setType($urlVar = 'id')
	{
		// Get the posted values from the request.
		$data     = $this->input->post->get('jform', array(), 'array');
		$recordId = $this->input->get($urlVar, 0, 'int');

		// Get the type.
		$options  = new JRegistry(base64_decode($data['temp_type']));
		$name     = $options->get('name');
		$typeMode = $options->get('type_mode');
		$modes    = FieldsandfiltersModes::getMode()->get($typeMode);
		$type     = FieldsandfiltersFactory::getTypes()->getTypesByName($name)->get($name);

		if ($name && $modes && $type && $recordId == $options->get('id', 0))
		{
			$data['type']      = $name;
			$data['type_mode'] = $typeMode;

			//Save the data in the session.
			JFactory::getApplication()->setUserState('com_fieldsandfilters.edit.field.data', $data);

			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId, $urlVar), false));

			return true;
		}

		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));

		return false;
	}

	/**
	 * Sets the extension type of the field item currently being edited.
	 *
	 * @param   string $urlVar The name of the URL variable if different from the primary key
	 *
	 * @return  void
	 * @since       1.1.0
	 */
	function setExtension($urlVar = 'id')
	{
		// Get the posted values from the request.
		$data     = $this->input->post->get('jform', array(), 'array');
		$recordId = $this->input->get($urlVar, 0, 'int');

		// Get the type.
		$options       = new JRegistry(base64_decode($data['temp_extension']));
		$contentTypeId = (int) $options->get('content_type_id');
		$form          = $options->get('form');
		$extension     = FieldsandfiltersFactory::getExtensions()->getExtensionsByTypeID($contentTypeId)->get($contentTypeId);

		if ($contentTypeId && $extension && $extension->content_type_alias == $options->get('content_type_alias') && $recordId == $options->get('id', 0))
		{
			$data['content_type_id'] = $contentTypeId;
			$data['extension_form']  = $form;

			//Save the data in the session.
			JFactory::getApplication()->setUserState('com_fieldsandfilters.edit.field.data', $data);

			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId, $urlVar), false));

			return true;
		}

		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));

		return false;
	}

}