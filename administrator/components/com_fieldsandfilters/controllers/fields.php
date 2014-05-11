<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access.
defined('_JEXEC') or die;

/* @deprecated J3.x */
if (!FieldsandfiltersFactory::isVersion())
{
	jimport('joomla.application.component.controlleradmin');
}
/* @end deprecated J3.x */

/**
 * Fields list controller class.
 *
 * @since       1.0.0
 */
class FieldsandfiltersControllerFields extends JControllerAdmin
{
	/**
	 * Constructor.
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 *
	 * @see         JController
	 * @since       1.0.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Define standard task mappings.

		// State Value = -1
		$this->registerTask('onlyadmin', 'publish');

		// Required Value = 0
		$this->registerTask('unrequired', 'required');

		/* @deprecated J3.x */
		if (!FieldsandfiltersFactory::isVersion())
		{
			$this->input = JFactory::getApplication()->input;
		}
		/* @end deprecated J3.x */
	}

	/**
	 * Proxy for getModel.
	 *
	 * @since       1.0.0
	 */
	public function getModel($name = 'field', $prefix = 'FieldsandfiltersModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Method to publish a list of items
	 *
	 * @return  void
	 * @since       1.0.0
	 */
	public function publish()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to publish from the request.
		$user  = JFactory::getUser();
		$ids   = $this->input->get('cid', array(), 'array');
		$data  = array('publish' => 1, 'unpublish' => 0, 'onlyadmin' => -1);
		$task  = $this->getTask();
		$value = JArrayHelper::getValue($data, $task, 0, 'int');

		try
		{
			// Access checks.
			foreach ($ids as $i => $id)
			{
				if (!$user->authorise('core.edit.state', 'com_fieldsandfilters.field.' . (int) $id))
				{
					// Prune items that you can't change.
					unset($ids[$i]);
					throw new RuntimeException(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
				}
			}

			if (empty($ids))
			{
				throw new InvalidArgumentException(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'));
			}

			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			JArrayHelper::toInteger($ids);

			// Publish the items.
			if (!$model->publish($ids, $value))
			{
				throw new RuntimeException($model->getError());
			}

			switch ($value)
			{
				case 1:
					$ntext = '_N_ITEMS_PUBLISHED';
					break;
				case -1:
					$ntext = '_N_ITEMS_ONLYADMIN';
					break;
				default:
					$ntext = '_N_ITEMS_UNPUBLISHED';
					break;

			}

			$this->setMessage(JText::plural($this->text_prefix . $ntext, count($ids)));
		} catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}

		$extension    = $this->input->get('extension');
		$extensionURL = ($extension) ? '&extension=' . $extension : '';
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $extensionURL, false));
	}

	/**
	 * Method to required a list of items
	 *
	 * @return  void
	 * @since       1.0.0
	 */
	public function required()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to publish from the request.
		$user  = JFactory::getUser();
		$ids   = $this->input->get('cid', array(), 'array');
		$data  = array('required' => 1, 'unrequired' => 0);
		$task  = $this->getTask();
		$value = JArrayHelper::getValue($data, $task, 0, 'int');

		try
		{
			// Access checks.
			foreach ($ids as $i => $id)
			{
				if (!$user->authorise('core.edit.state', 'com_fieldsandfilters.field.' . (int) $id))
				{
					// Prune items that you can't change.
					unset($ids[$i]);
					throw new RuntimeException(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
				}
			}

			if (empty($ids))
			{
				throw new InvalidArgumentException(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'));
			}

			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			JArrayHelper::toInteger($ids);

			// Publish the items.
			if (!$model->required($ids, $value))
			{
				throw new RuntimeException($model->getError());
			}

			$ntext = ($value == 1) ? '_N_ITEMS_REQUIRED' : '_N_ITEMS_UNREQUIRED';

			$this->setMessage(JText::plural($this->text_prefix . $ntext, count($ids)));
		} catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}

		$extension    = $this->input->get('extension');
		$extensionURL = ($extension) ? '&extension=' . $extension : '';
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $extensionURL, false));
	}

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 * @since       1.0.0
	 */
	public function saveOrderAjax()
	{
		$pks   = $this->input->post->get('cid', array(), 'array');
		$order = $this->input->post->get('order', array(), 'array');

		// Sanitize the input
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		JFactory::getApplication()->close();
	}
}