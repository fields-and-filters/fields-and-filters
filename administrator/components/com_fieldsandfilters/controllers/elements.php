<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kulka.tomek@gmail.com> -
 */

// No direct access.
defined('_JEXEC') or die;

if (!FieldsandfiltersFactory::isVersion())
{
	jimport('joomla.application.component.controlleradmin');
}

/**
 * Elements list controller class.
 *
 * @since       1.0.0
 */
class FieldsandfiltersControllerElements extends JControllerAdmin
{
	/**
	 * Proxy for getModel.
	 *
	 * @since       1.0.0
	 */
	public function getModel($name = 'element', $prefix = 'FieldsandfiltersModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array_merge(array('ignore_request' => true), $config));

		return $model;
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