<?php
/**
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_field_type.url
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

/**
 * Checkbox type fild
 *
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_types.url
 * @since       1.0.0
 */
class plgFieldsandfiltersTypesUrl extends JPlugin
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

		$fields     = is_array($fields) ? $fields : array($fields);
		$staticMode = (array) FieldsandfiltersModes::getMode(FieldsandfiltersModes::MODE_STATIC);

		$syntax = KextensionsPlugin::getParams('system', 'fieldsandfilters')->get('syntax', '#{%s}');

		while ($field = array_shift($fields))
		{
			$root = new SimpleXMLElement('<fields />');
			$root->addAttribute('name', 'data');

			$rootJson = $root->addChild('fields');
			$rootJson->addAttribute('name', $field->id);

			$label = '<strong>' . $field->name . '</strong> ' . sprintf($syntax, $field->id);

			if ($field->state == -1)
			{
				$label .= ' [' . JText::_('PLG_FIELDSANDFILTERS_FORM_ONLY_ADMIN') . ']';
			}

			if (!($isStaticMode = in_array($field->mode, $staticMode)))
			{
				// name spacer
				$element = $rootJson->addChild('field');
				$element->addAttribute('type', 'spacer');
				$element->addAttribute('name', 'name_spacer_' . $field->id);
				$element->addAttribute('label', $label);
				$element->addAttribute('translate_label', 'false');
				$element->addAttribute('class', 'text');
				$element->addAttribute('fieldset', $fieldset);
			}

			if (!empty($field->description) && $field->params->get('base.admin_enabled_description', 0))
			{
				switch ($field->params->get('base.admin_description_type', 'description'))
				{
					case 'tip':
						$element->addAttribute('description', $field->description);
						$element->addAttribute('translate_description', 'false');
						break;
					case 'description':
					default:
						$element = $rootJson->addChild('field');
						$element->addAttribute('type', 'spacer');
						$element->addAttribute('name', 'description_spacer_' . $field->id);
						$element->addAttribute('label', $field->description);
						$element->addAttribute('translate_label', 'false');
						$element->addAttribute('fieldset', $fieldset);
						break;
				}
			}

			$element = $rootJson->addChild('field');
			$element->addAttribute('labelclass', 'control-label');
			$element->addAttribute('fieldset', $fieldset);

			if ($isStaticMode)
			{
				$label .= ' [' . JText::_('PLG_FIELDSANDFILTERS_FORM_GROUP_STATIC_TITLE') . ']';

				$element->addAttribute('type', 'spacer');
				$element->addAttribute('description', $field->data);
				$element->addAttribute('name', $field->id);
				$element->addAttribute('label', $label);
				$element->addAttribute('translate_label', 'false');
				$element->addAttribute('translate_description', 'false');
			}
			else
			{
				// url
				$element->addAttribute('name', 'url');
				$element->addAttribute('type', 'text');
				$element->addAttribute('class', 'inputbox');
				$element->addAttribute('label', 'PLG_FAF_TS_UL_FORM_URL_LBL');
				$element->addAttribute('description', 'PLG_FAF_TS_UL_FORM_URL_DESC');
				$element->addAttribute('filter', 'safehtml');

				if ($field->required)
				{
					$element->addAttribute('required', 'true');
				}

				// title
				$element = $rootJson->addChild('field');
				$element->addAttribute('name', 'title');
				$element->addAttribute('type', 'text');
				$element->addAttribute('class', 'inputbox');
				$element->addAttribute('labelclass', 'control-label');
				$element->addAttribute('label', 'PLG_FAF_TS_UL_FORM_TITLE_LBL');
				$element->addAttribute('description', 'PLG_FAF_TS_UL_FORM_TITLE_DESC');
				$element->addAttribute('filter', 'safehtml');
				$element->addAttribute('fieldset', $fieldset);

				// alt
				$element = $rootJson->addChild('field');
				$element->addAttribute('name', 'alt');
				$element->addAttribute('type', 'text');
				$element->addAttribute('class', 'inputbox');
				$element->addAttribute('labelclass', 'control-label');
				$element->addAttribute('label', 'PLG_FAF_TS_UL_FORM_ALT_LBL');
				$element->addAttribute('description', 'PLG_FAF_TS_UL_FORM_ALT_DESC');
				$element->addAttribute('filter', 'safehtml');
				$element->addAttribute('fieldset', $fieldset);

				// target
				$element = $rootJson->addChild('field');
				$element->addAttribute('name', 'target');
				$element->addAttribute('type', 'list');
				$element->addAttribute('class', 'inputbox');
				$element->addAttribute('labelclass', 'control-label');
				$element->addAttribute('label', 'PLG_FAF_TS_UL_FORM_TARGET_LBL');
				$element->addAttribute('description', 'PLG_FAF_TS_UL_FORM_TARGET_DESC');
				$element->addAttribute('fieldset', $fieldset);

				KextensionsXML::addOptionsNode($element, array(
					'PLG_FAF_TS_UL_FORM_TARGET_OPTION_DEFAULT' => '',
					'PLG_FAF_TS_UL_FORM_TARGET_OPTION_BLANK'   => 1,
					'PLG_FAF_TS_UL_FORM_TARGET_OPTION_POPUP'   => 2,
					'PLG_FAF_TS_UL_FORM_TARGET_OPTION_MODAL'   => 3,
					'PLG_FAF_TS_UL_FORM_TARGET_OPTION_PARENT'  => 4

				));
			}

			// hr bottom spacer
			$element = $rootJson->addChild('field');
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
	 * @since       1.0.0
	 */
	public function onFieldsandfiltersBeforeSaveData($context, $newItem, $oldItem, $isNew)
	{
		if ($context == 'com_fieldsandfilters.field' && $newItem->type == $this->_name && FieldsandfiltersModes::getModeName($newItem->mode) == FieldsandfiltersModes::MODE_STATIC)
		{
			$newItem->params = new JRegistry($newItem->params);
			$_data           = new JRegistry($newItem->values);

			if (!$_data->get('url'))
			{
				$_data = null;
			}

			$newItem->values->set('data', (string) $_data);
		}
		elseif ($context == 'com_fieldsandfilters.element')
		{
			$data   = $newItem->get('fields')->get('data', new JObject);
			$fields = FieldsandfiltersFieldsHelper::getFieldsByTypeIDColumnFieldType($newItem->get('content_type_id'))->get($this->_name);

			if ($fields)
			{
				$fields = is_array($fields) ? $fields : array($fields);

				while ($field = array_shift($fields))
				{
					$_data = new JRegistry($data->get($field->id, new JObject)->getProperties(true));

					if (!$_data->get('url'))
					{
						$_data = null;
					}

					$data->set($field->id, (string) $_data);
				}
			}
		}

		return true;
	}

	/**
	 * @since       1.0.0
	 */
	public function onFieldsandfiltersPrepareElementFields($context, $item, $isNew, $state)
	{
		if ($isNew)
		{
			return true;
		}

		if ($context == 'com_fieldsandfilters.field' && isset($item->type) && $item->type == $this->_name)
		{
			if (!empty($item->values->data) && !is_object($item->values->data))
			{
				$_data = new JRegistry($item->values->data);
				$_data = new JObject($_data->toObject());

				$item->values = $_data;
			}
		}
		elseif ($context == 'com_fieldsandfilters.element')
		{
			$data   = $item->get('fields', new JObject)->get('data', new JObject);
			$fields = FieldsandfiltersFieldsHelper::getFieldsByTypeIDColumnFieldType($item->get('content_type_id'))->get($this->_name);

			if ($fields)
			{
				$fields = is_array($fields) ? $fields : array($fields);

				while ($field = array_shift($fields))
				{
					$_data = $data->get($field->id, '');

					if (empty($_data) || is_object($_data))
					{
						continue;
					}

					$_data = new JRegistry($_data);
					$_data = new JObject($_data->toObject());

					$data->set($field->id, $_data);
				}
			}
		}

		return true;
	}

	/**
	 * @since       1.1.0
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

			if (($isStaticMode && empty($field->data)) || ($modeName == 'field' && (!isset($element->data) || !property_exists($element->data, $field->id))))
			{
				continue;
			}

			$dataElement = ($isStaticMode) ? $field->data : $element->data->get($field->id);

			if (is_string($dataElement))
			{
				if ($isStaticMode)
				{
					$field->data = new JRegistry($dataElement);
				}
				else
				{
					$element->data->set($field->id, new JRegistry($dataElement));
				}
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

			if ($isStaticMode)
			{
				FieldsandfiltersFieldsField::preparationContent('type.prepare_data', $field, 'data', $context, $field->id, $params);
			}
			else
			{
				FieldsandfiltersFieldsField::preparationContentData('type.prepare_data', $field, $element, $context, $field->id, $params);
			}

			$layoutField = FieldsandfiltersFieldsField::getLayout('field', $modeName, $field->params);

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
}