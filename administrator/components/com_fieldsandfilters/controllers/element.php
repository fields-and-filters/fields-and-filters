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
 * Element controller class.
 *
 * @since       1.0.0
 */
class FieldsandfiltersControllerElement extends JControllerForm
{
	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer $recordId The primary key id for the item.
	 * @param   string  $urlVar   The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since       1.0.0
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append        = parent::getRedirectToItemAppend($recordId, $urlVar);
		$jinput        = JFactory::getApplication()->input;
		$itemID        = $jinput->get('itid', 0, 'int');
		$contentTypeID = $jinput->get('ctid', 0, 'int');

		if (!$recordId && !$itemID && !$contentTypeID)
		{
			$jform = $jinput->get('jform', array(), 'array');

			$itemID        = JArrayHelper::getValue($jform, 'item_id', 0, 'int');
			$contentTypeID = JArrayHelper::getValue($jform, 'content_type_id', 0, 'int');
		}

		// Setup redirect info.
		if ($contentTypeID)
		{
			$append .= '&ctid=' . $contentTypeID;
		}
		if ($itemID)
		{
			$append .= '&itid=' . $itemID;
		}

		return $append;
	}
}