<?php
/**
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_field_type.list
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

/**
 * List type fild
 *
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_types.list
 * @since       1.0.0
 */
class plgFieldsandfiltersTypesList extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access      protected
	 *
	 * @param       object $subject The object to observe
	 * @param       array  $config  An array that holds the plugin configuration
	 *
	 * @since       1.0.0
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		if (JFactory::getApplication()->isAdmin())
		{
			$this->loadLanguage();
		}
	}

	/**
	 * onFieldsandfiltersPrepareFormField
	 *
	 * @param    KextensionsForm $form     The form to be altered.
	 * @param    JObject         $data     The associated data for the form.
	 * @param   boolean          $isNew    Is element is new
	 * @param   string           $fieldset Fieldset name
	 *
	 * @return    boolean
	 * @since    1.1.0
	 */
	public function onFieldsandfiltersPrepareFormField(KextensionsForm $form, JObject $data, $isNew = false, $fieldset = 'fieldsandfilters')
	{
		if (!($fields = $data->get($this->_name)))
		{
			return true;
		}

		JForm::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/models/fields');

		$fields = is_array($fields) ? $fields : array($fields);
		$syntax = KextensionsPlugin::getParams('system', 'fieldsandfilters')->get('syntax', '#{%s}');

		while ($field = array_shift($fields))
		{
			$root = new SimpleXMLElement('<fields />');
			$root->addAttribute('name', 'connections');

			if (!empty($field->description) && $field->params->get('base.admin_enabled_description', 0))
			{
				switch ($field->params->get('base.admin_description_type', 'description'))
				{
					case 'tip':
						$element = $root->addChild('field');
						$element->addAttribute('description', $field->description);
						$element->addAttribute('translate_description', 'false');
						$element->addAttribute('fieldset', $fieldset);
						break;
					case 'description':
					default:
						$element = $root->addChild('field');
						$element->addAttribute('type', 'spacer');
						$element->addAttribute('name', 'description_spacer_' . $field->id);
						$element->addAttribute('label', $field->description);
						$element->addAttribute('translate_label', 'false');
						$element->addAttribute('fieldset', $fieldset);

						$element = $root->addChild('field');
						$element->addAttribute('fieldset', $fieldset);
						break;
				}
			}
			else
			{
				$element = $root->addChild('field');
				$element->addAttribute('fieldset', $fieldset);
			}

			$label = '<strong>' . $field->name . '</strong> ' . sprintf($syntax, $field->id);

			if ($field->state == -1)
			{
				$label .= ' [' . JText::_('PLG_FIELDSANDFILTERS_FORM_ONLY_ADMIN') . ']';
			}

			$element->addAttribute('id', $field->id);
			$element->addAttribute('name', $field->id);
			$element->addAttribute('class', 'inputbox');
			$element->addAttribute('labelclass', 'control-label');
			$element->addAttribute('label', $label);
			$element->addAttribute('translate_label', 'false');
			$element->addAttribute('filter', 'int_array');
			$element->addAttribute('translate_options', 'false');

			switch ($field->params->get('type.style', 'checkbox'))
			{
				case 'multiselect':
					$element->addAttribute('multiple', 'true');
				case 'select':
					$type = 'fieldsandfiltersList';
					break;
				case 'checkbox':
				default:
					$type = 'fieldsandfiltersCheckboxes';
					break;
			}

			$element->addAttribute('type', $type);

			if ($field->required)
			{
				$element->addAttribute('required', 'true');
			}

			// FieldsandfiltersFieldValues
			$values = $field->values->getProperties();

			if (!empty($values))
			{
				while ($value = array_shift($values))
				{
					$option = $element->addChild('option', ($value->value . ' [' . $value->alias . ']'));
					$option->addAttribute('value', $value->id);
				}

				if ($isNew && ($default = $field->params->get('type.default')))
				{
					$default = array_unique((array) $default);
					JArrayHelper::toInteger($default);

					$form->setData('connections.' . $field->id, $default);
				}
			}

			// hr bottom spacer
			$element = $root->addChild('field');
			$element->addAttribute('type', 'spacer');
			$element->addAttribute('name', 'hr_bottom_spacer_' . $field->id);
			$element->addAttribute('hr', 'true');
			$element->addAttribute('fieldset', $fieldset);

			$form->addOrder($field->id, $field->ordering)
				->setField($field->id, $root);
		}

		return true;
	}

	/**
	 * @since    1.1.0
	 */
	public function getFieldsandfiltersFieldsHTML(JObject $layoutFields, Jobject $fields, stdClass $element = null, $context = 'fields', JRegistry $params = null, $ordering = 'ordering')
	{
		if (!($fields = $fields->get($this->_name)))
		{
			return;
		}

		$fields = is_array($fields) ? $fields : array($fields);

		$variables          = new JObject;
		$variables->type    = $this->_type;
		$variables->name    = $this->_name;
		$variables->params  = $this->params;
		$variables->element = $element;

		while ($field = array_shift($fields))
		{
			$modeName     = FieldsandfiltersModes::getModeName($field->mode);
			$isStaticMode = ($modeName == FieldsandfiltersModes::MODE_STATIC);

			if (($isStaticMode && empty($field->connections)) || ($modeName == 'filter' && (!isset($element->connections) || !property_exists($element->connections, $field->id))))
			{
				continue;
			}

			if ($params)
			{
				$paramsTemp  = $field->params;
				$paramsField = clone $field->params;

				$paramsField->merge($params);
				$field->params = $paramsField;
			}

			if ($field->params->get('base.show_name'))
			{
				FieldsandfiltersFieldsField::preparationContent('base.prepare_name', $field, 'name', $context, $field->id, $params);
			}

			if ($field->params->get('base.site_enabled_description'))
			{
				FieldsandfiltersFieldsField::preparationContent('base.prepare_description', $field, 'description', $context, $field->id, $params);
			}

			FieldsandfiltersFieldsField::preparationContentValues('type.prepare_values', $field, $context, $field->id, $params);

			$layoutField = FieldsandfiltersFieldsField::getLayout('field', ($isStaticMode ? $modeName : 'field'), $field->params);

			$variables->field = $field;

			$layout = KextensionsPlugin::renderLayout($variables, $layoutField);
			$layoutFields->set(KextensionsArray::getEmptySlotObject($layoutFields, $field->$ordering, false), $layout);

			if ($params)
			{
				$field->params = $paramsTemp;
				unset($paramsField);
			}
		}

		unset($variables);
	}

	/**
	 * @since    1.1.0
	 */
	public function getFieldsandfiltersFiltersHTML(JObject $layoutFilters, JObject $fields, $context = 'filters', JRegistry $params = null, $ordering = 'ordering')
	{
		if (!($fields = $fields->get($this->_name)))
		{
			return;
		}

		$fields = is_array($fields) ? $fields : array($fields);

		$variables         = new JObject;
		$variables->type   = $this->_type;
		$variables->name   = $this->_name;
		$variables->params = $this->params;

		while ($field = array_shift($fields))
		{
			if ($params)
			{
				$paramsTemp  = $field->params;
				$paramsField = clone $field->params;

				$paramsField->merge($params);
				$field->params = $paramsField;
			}

			if ($field->params->get('base.show_name'))
			{
				FieldsandfiltersFieldsField::preparationContent('base.prepare_name', $field, 'name', $context, $field->id, $params);
			}

			if ($field->params->get('base.site_enabled_description'))
			{
				FieldsandfiltersFieldsField::preparationContent('base.prepare_description', $field, 'description', $context, $field->id, $params);
			}

			FieldsandfiltersFieldsField::preparationContentValues('type.prepare_values', $field, $context, $field->id, $params);

			$layoutFilter = FieldsandfiltersFieldsField::getLayout('filter', 'filter', $field->params);

			$variables->field = $field;

			$layout = KextensionsPlugin::renderLayout($variables, $layoutFilter);
			$layoutFilters->set(KextensionsArray::getEmptySlotObject($layoutFilters, $field->$ordering, false), $layout);

			if ($params)
			{
				$field->params = $paramsTemp;
				unset($paramsFilter);
			}
		}

		unset($variables);
	}
}
