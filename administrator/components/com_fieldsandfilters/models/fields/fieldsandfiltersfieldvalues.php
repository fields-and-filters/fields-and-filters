<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('checkboxes');

/**
 * Form Field class for the Joomla Platform.
 *
 * @package     fieldsandfilters
 * @subpackage  Form
 * @see         JFormFieldFields for a select list of fields.
 * @since       1.0.0
 */
class JFormFieldFieldsandfiltersFieldValues extends JFormFieldCheckboxes
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since       1.0.0
	 */
	protected $type = 'FieldsandfiltersFieldValues';

	/**
	 * The parent field of the form field
	 *
	 * @var    mixed
	 * @since  1.2.0
	 */
	protected $parentField = 'id';

	/**
	 * The checked of the form field
	 *
	 * @var    mixed
	 * @since  1.2.0
	 */
	protected $checked = array();

	/**
	 * The states of the form field
	 *
	 * @var    mixed
	 * @since  1.2.0
	 */
	protected $states = 1;

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
			case 'parentField':
			case 'checked':
			case 'states':
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
			case 'parentField':
				$this->$name = (string) $value;
				break;

			case 'checked':
				$value       = (string) $value;
				$this->$name = array_map('trim', explode(',', $value));
				break;

			case 'states':
				$this->$name = explode(',', (string) $value);
				JArrayHelper::toInteger($this->$name);
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
			$attributes = array('parentField', 'checked', 'states', 'class'); /* class is @deprecated >= J3.2 */
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
	 * Method to get the field input markup for check boxes.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since       1.0.0
	 */
	protected function getInput()
	{
		$html = array();

		// Initialize some field attributes.
		$class = $this->class ? ' class="checkboxes ' . $this->class . '"' : ' class="checkboxes"';

		// Start the checkbox field output.
		$html[] = '<fieldset id="' . $this->id . '"' . $class . '>';

		// Get the field options.
		if (!($options = $this->getOptions()))
		{
			return '<span class="readonly">' . JText::_('COM_FIELDSANDFILTERS_ERROR_FIELD_VALUES_EMPTY') . '</span>';
		}

		// Build the checkbox field output.
		$html[] = '<ul class="nav nav-list">';
		foreach ($options as $i => $option)
		{
			// Initialize some option attributes.
			if (!isset($this->value) || empty($this->value))
			{
				$checked = (in_array((string) $option->value, (array) $this->checked) ? ' checked="checked"' : '');
			}
			else
			{
				$value   = !is_array($this->value) ? explode(',', $this->value) : $this->value;
				$checked = (in_array((string) $option->value, $value) ? ' checked="checked"' : '');
			}
			$class    = !empty($option->class) ? ' class="checkbox ' . $option->class . '"' : ' class="checkbox"';
			$required = !empty($option->required) ? ' required="required" aria-required="true"' : '';
			$disabled = !empty($option->disable) ? ' disabled="disabled"' : '';

			// Initialize some JavaScript option attributes.
			$onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';

			$html[] = '<li>';
			$html[] = '	<label' . $class . '>';
			$html[] = '		<input type="checkbox" id="' . $this->id . $i . '" name="' . $this->name . '" value="'
				. htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $class . $onclick . $disabled . $required . '/>';
			$html[] = $option->text;
			$html[] = '	</label>';
			$html[] = '</li>';
		}
		$html[] = '</ul>';

		// End the checkbox field output.
		$html[] = '</fieldset>';

		return implode($html);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since       1.0.0
	 */
	protected function getOptions()
	{
		$options = array();

		if ($fieldID = (int) $this->form->getValue($this->parentField))
		{
			$options = JHtml::_('FieldsandfiltersHtml.options.fieldValues', $fieldID, $this->states);

			// Load the plugin extension.
			if (empty($options))
			{
				return false;
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}