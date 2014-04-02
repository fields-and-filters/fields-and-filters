<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package           fieldsandfilters.administrator
 * @subpackage        com_fieldsandfilters
 *
 * @since             1.2.0
 */
abstract class FieldsandfiltersHtmlDropdown
{
	/**
	 * Append a required item to the current dropdown menu
	 *
	 * @param   string $checkboxId ID of corresponding checkbox of the record
	 * @param   string $prefix     The task prefix
	 *
	 * @return  void
	 *
	 * @since       1.0.0
	 */
	public static function required($checkboxId, $prefix = '')
	{
		$task = $prefix . 'required';

		if (FieldsandfiltersFactory::isVersion('>=', 3.2))
		{
			JHtml::_('actionsdropdown.addCustomItem', JText::_('COM_FIELDSANDFILTERS_HTML_REQUIRED_ITEM'), 'star', $checkboxId, $task);
		}
		else
		{
			JHtml::_('dropdown.addCustomItem', JText::_('COM_FIELDSANDFILTERS_HTML_REQUIRED_ITEM'), 'javascript:void(0)', 'onclick="contextAction(\'' . $checkboxId . '\', \'' . $task . '\')"');
		}

		return;
	}

	/**
	 * Append an unrequired item to the current dropdown menu
	 *
	 * @param   string $checkboxId ID of corresponding checkbox of the record
	 * @param   string $prefix     The task prefix
	 *
	 * @return  void
	 *
	 * @since       1.0.0
	 */
	public static function unrequired($checkboxId, $prefix = '')
	{
		$task = $prefix . 'unrequired';
		if (FieldsandfiltersFactory::isVersion('>=', 3.2))
		{
			JHtml::_('actionsdropdown.addCustomItem', JText::_('COM_FIELDSANDFILTERS_HTML_UNREQUIRED_ITEM'), 'star-empty', $checkboxId, $task);
		}
		else
		{
			JHtml::_('dropdown.addCustomItem', JText::_('COM_FIELDSANDFILTERS_HTML_UNREQUIRED_ITEM'), 'javascript:void(0)', 'onclick="contextAction(\'' . $checkboxId . '\', \'' . $task . '\')"');
			JHtml::_('dropdown.addCustomItem', JText::_('COM_FIELDSANDFILTERS_HTML_REQUIRED_ITEM'), 'javascript:void(0)', 'onclick="contextAction(\'' . $checkboxId . '\', \'' . $task . '\')"');
		}

		return;
	}

	/**
	 * Append an published only admin item to the current dropdown menu
	 *
	 * @param   string $checkboxId ID of corresponding checkbox of the record
	 * @param   string $prefix     The task prefix
	 *
	 * @return  void
	 *
	 * @since       1.0.0
	 */
	public static function onlyAdmin($checkboxId, $prefix = '')
	{
		$task = $prefix . 'onlyadmin';
		if (FieldsandfiltersFactory::isVersion('>=', 3.2))
		{
			JHtml::_('actionsdropdown.addCustomItem', JText::_('COM_FIELDSANDFILTERS_HTML_ONLYADMIN'), 'dashboard', $checkboxId, $task);
		}
		else
		{
			JHtml::_('dropdown.addCustomItem', JText::_('COM_FIELDSANDFILTERS_HTML_ONLYADMIN'), 'javascript:void(0)', 'onclick="contextAction(\'' . $checkboxId . '\', \'' . $task . '\')"');
		}

		return;
	}
}
