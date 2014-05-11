<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kulka.tomek@gmail.com> -
 */

// No direct access
defined('_JEXEC') or die;

if (!FieldsandfiltersFactory::isVersion())
{
	jimport('joomla.application.component.controllerform');
}

/**
 * Fieldvalue controller class.
 *
 * @since    1.0.0
 */
class FieldsandfiltersControllerFieldvalue extends JControllerForm
{
	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   12.2
	 */
	protected function getRedirectToListAppend()
	{
		$append = parent::getRedirectToListAppend();

		$filter   = (array) JFactory::getApplication()->getUserState(sprintf('%s.%s.filter', $this->option, $this->view_list), array());
		$field_id = (int) JArrayHelper::getValue($filter, 'field_id');

		if ($field_id)
		{
			$append .= '&field_id=' . $field_id;
		}

		return $append;
	}
}