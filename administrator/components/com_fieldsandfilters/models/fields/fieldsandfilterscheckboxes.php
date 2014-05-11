<?php
/**
 * @package    com_fieldsandfilters
 * @copyright  Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license    GNU General Public License version 3 or later; see License.txt
 * @author     KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('checkboxes');

/**
 * Form Field class for the Joomla Platform.
 *
 * @package     fieldsandfilters
 * @subpackage  Form
 * @see         JFormFieldFields for a checbox list of fields.
 * @since       1.0.0
 */
class JFormFieldFieldsandfiltersCheckboxes extends JFormFieldCheckboxes
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since       1.0.0
	 */
	protected $type = 'FieldsandfiltersCheckboxes';

	/**
	 * Method to get the field input markup for check boxes.
	 *
	 * @return string The field input markup.
	 *
	 * @since       1.0.0
	 */
	protected function getInput()
	{
		// Get the field options.
		$options = $this->getOptions();
		if (empty($options))
		{
			return '<span class="readonly">' . JText::_('COM_FIELDSANDFILTERS_ERROR_FIELD_VALUES_EMPTY') . '</span>';
		}

		return parent::getInput();
	}
}