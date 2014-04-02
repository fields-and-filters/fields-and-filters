<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('JPATH_BASE') or die;

/**
 * Form Field class for the Joomla Platform.
 *
 * @package     fieldsandfilters
 * @subpackage  Form
 * @see         JFormFieldModal_FieldsandfiltersTypes for a select list of type plugins.
 * @since       1.2.0
 */
class JFormFieldModal_FieldsandfiltersTypes extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since       1.2.0
	 */
	protected $type = 'Modal_FieldsandfiltersTypes';

	/**
	 * The size of the form field.
	 *
	 * @var    integer
	 * @since      1.2.0
	 * @deprecated >= J3.2
	 */
	protected $size;

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
			/* @deprecated >= J3.2 */
			case 'size':
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
			/* @deprecated >= J3.2 */
			case 'class':
				// Removes spaces from left & right and extra spaces from middle
				$value       = preg_replace('/\s+/', ' ', trim((string) $value));
				$this->$name = (string) $value;
				break;

			case 'size':
				$this->$name = (int) $value;
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
			/* @deprecated >= J3.2 */
			$attributes = array('class', 'size');
			foreach ($attributes as $attributeName)
			{
				$this->__set($attributeName, $element[$attributeName]);
			}
			/* @end deprecated >= J3.2 */
		}

		return $return;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 * @since       1.2.0
	 */
	protected function getInput()
	{
		// Initialise variables
		$value    = '';
		$html     = array();
		$size     = !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$class    = !empty($this->class) ? ' class="' . $this->class . '"' : 'class="inputbox"';
		$recordId = (int) $this->form->getValue('id', 0);
		$mode     = (int) $this->form->getValue('mode', 0);

		$typesHelper = FieldsandfiltersFactory::getTypes();

		if ($mode && ($pluginType = $typesHelper->getTypes(true)->get($this->value)))
		{
			// Load Extensions Helper
			KextensionsLanguage::load('plg_' . $pluginType->type . '_' . $pluginType->name, JPATH_ADMINISTRATOR);

			$modeName = FieldsandfiltersModes::getModeName($mode, FieldsandfiltersModes::MODE_NAME_TYPE);
			$typeForm = $pluginType->forms->get($modeName, new JObject);

			if (isset($typeForm->group->title))
			{
				$value = JText::_($typeForm->title) . ' [' . JText::_($typeForm->group->title) . ']';
			}
			else
			{
				if (isset($typeForm->title))
				{
					$value = JText::_($typeForm->title);
				}
			}
		}
		// Load the javascript and css
		JHtml::_('behavior.framework');
		JHtml::_('behavior.modal');

		$query = array(
			'option'   => 'com_fieldsandfilters',
			'view'     => 'plugins',
			'tmpl'     => 'component',
			'layout'   => 'types',
			'recordId' => $recordId
		);

		$link = JRoute::_('index.php?' . JURI::buildQuery($query));

		if (FieldsandfiltersFactory::isVersion())
		{
			$html[] = '<span class="input-append">';
			$html[] = '	<input type="text" readonly="readonly" disabled="disabled" value="' . $value . '"' . $size . $class . ' />';
			$html[] = '	<a class="btn btn-primary" onclick="SqueezeBox.fromElement(this, {handler:\'iframe\', size: {x: 600, y: 450}, url:\'' . $link . '\'})">';
			$html[] = '		<i class="icon-list icon-white"></i>';
			$html[] = JText::_('JSELECT');
			$html[] = '	</a>';
			$html[] = '</span>';
			$html[] = '<input class="input-small" type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" />';
		}
		else
		{
			$html[] = '<input type="text" readonly="readonly" disabled="disabled" value="' . $value . '"' . $size . $class . ' />';
			$html[] = '<input type="button" value="' . JText::_('JSELECT') . '" onclick="SqueezeBox.fromElement(this, {handler:\'iframe\', size: {x: 600, y: 450}, url:\'' . $link . '\'})" />';
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" />';
		}

		return implode("\n", $html);

	}
}