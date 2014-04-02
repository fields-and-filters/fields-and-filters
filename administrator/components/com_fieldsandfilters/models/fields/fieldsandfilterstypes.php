<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 *
 * @package     fieldsandfilters
 * @subpackage  Form
 * @see         JFormFieldFieldsandfiltersTypes for a select list of type plugins.
 * @since       1.2.0
 */
class JFormFieldFieldsandfiltersTypes extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since       1.2.0
	 */
	protected $type = 'FieldsandfiltersTypes';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.2.0
	 */
	protected function getOptions()
	{
		$options = JHtml::_('FieldsandfiltersHtml.options.types');

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}