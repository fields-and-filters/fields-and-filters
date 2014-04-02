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
class JFormFieldFieldsandfiltersStates extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since       1.2.0
	 */
	protected $type = 'FieldsandfiltersStates';

	/**
	 * The exclude of the form field
	 *
	 * @var    mixed
	 * @since  1.2.0
	 */
	protected $exclude = array();

	/**
	 * The class of the form field
	 *
	 * @var    mixed
	 * @since      1.2.0
	 * @deprecated >= J3.2
	 */
	protected $class;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string $name The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   1.2.0
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'exclude':
				return $this->$name;

			/* @deprecated >= J3.2 */
			case 'class':
				return $this->$name;
			/* @enddeprecated >= J3.2 */
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string $name  The property name for which to the the value.
	 * @param   mixed  $value The value of the property.
	 *
	 * @return  void
	 *
	 * @since   1.2.0 && J3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'exclude':
				$value       = (string) $value;
				$this->$name = array_map('trim', explode(',', $value));
				break;

			/* @deprecated >= J3.2 */
			case 'class':
				// Removes spaces from left & right and extra spaces from middle
				$value       = preg_replace('/\s+/', ' ', trim((string) $value));
				$this->$name = (string) $value;
				break;

			default:
				if (FieldsandfiltersFactory::isVersion('>=', 3.2))
				{
					parent::__set($name, $value);
				}
				break;
			/* @end deprecated >= J3.2 */
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement $element   The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed            $value     The form field value to validate.
	 * @param   string           $group     The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   1.2.0
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$attributes = array('exclude', 'class'); /* class is @deprecated >= J3.2 */
			foreach ($attributes as $attributeName)
			{
				if (isset($element[$attributeName]))
				{
					$this->__set($attributeName, $element[$attributeName]);
				}
			}
		}

		return $return;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.2.0
	 */
	protected function getOptions()
	{
		$options = JHtml::_('FieldsandfiltersHtml.options.states');

		if (!empty($this->exclude))
		{
			foreach ($options AS $key => $option)
			{
				if (in_array($option->value, $this->exclude))
				{
					unset($options[$key]);
				}
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}