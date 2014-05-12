<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access
defined('_JEXEC') or die;

/**
 * @since       1.0.0
 */
class FieldsandfiltersFieldsHelper
{
	/**
	 * Simple syntax - #{field_id,item_id:option,context}
	 *
	 * @since       1.2.0
	 **/
	const SYNTAX_SIMPLE = 1;

	/**
	 * Simple syntax + params - #{field_id,item_id:option,context,{params}}
	 *
	 * @since       1.2.0
	 **/
	const SYNTAX_EXTENDED = 2;

	/**
	 * Old syntax - #{field_id,item_id,option}
	 *
	 * @since       1.2.0
	 **/
	const SYNTAX_OLD = -1;

	/**
	 * @since       1.1.0
	 **/
	public static function getFieldsByItemID($option = null, $itemID = null, $fieldsID = null, $getAllextensions = true)
	{
		$app = JFactory::getApplication();

		// Load PluginExtensions Helper
		$extensionsHelper = FieldsandfiltersFactory::getExtensions();

		if (is_null($option))
		{
			$option = $app->input->get('option');
		}

		$extensions[] = strtolower($option);

		if ($getAllextensions)
		{
			$extensions[] = FieldsandfiltersExtensions::EXTENSION_DEFAULT;
		}

		$extensionsID = $extensionsHelper->getExtensionsByOptionColumn('content_type_id', $extensions);

		if (empty($extensionsID))
		{
			return self::_returnEmpty();
		}

		// Load elements Helper
		$element = false;
		if ((!($isNullItemID = is_null($itemID)) && !($element = FieldsandfiltersFactory::getElements()->getElementsByItemIDPivot('item_id', $extensionsID, $itemID, 1, FieldsandfiltersElements::VALUES_BOTH)->get($itemID))))
		{
			if (empty($fieldsID))
			{
				return self::_returnEmpty();
			}
		}

		if (!($isNullFieldsID = is_null($fieldsID)) && $element)
		{
			$values = FieldsandfiltersFields::VALUES_BOTH;
		}
		else
		{
			if ($isNullFieldsID && $element)
			{
				$fieldsID = array_merge(array_keys($element->connections->getProperties(true)), array_keys($element->data->getProperties(true)));

				if (empty($fieldsID))
				{
					return self::_returnEmpty();
				}

				$values = FieldsandfiltersFields::VALUES_VALUES;
			}
			else
			{
				if ((!$isNullFieldsID && $isNullItemID) || !empty($fieldsID))
				{
					$values = FieldsandfiltersFields::VALUES_BOTH;

					if (!$element)
					{
						$element = new stdClass(); // when element not exist an we have allextension with fields
					}
				}
				else
				{
					return self::_returnEmpty();
				}
			}
		}

		// Load Fields Helper
		if (!($fields = FieldsandfiltersFactory::getFields()->getFieldsByID($extensionsID, $fieldsID, 1, $values)))
		{
			return self::_returnEmpty();
		}

		return new JObject(array('element' => $element, 'fields' => $fields));
	}

	/**
	 * @since       1.2.0
	 **/
	public static function getFieldsLayouts(JObject $object, $context = '', JRegistry $params = null, $ordering = 'ordering')
	{
		$templateFields = new JObject;

		if (!isset($object->fields) || !($object->element))
		{
			return $templateFields;
		}

		$fields = $object->fields->getProperties();

		if (empty($fields))
		{
			return $templateFields;
		}

		$app = JFactory::getApplication();

		$fields = new JObject(JArrayHelper::pivot($fields, 'type'));
		JPluginHelper::importPlugin('fieldsandfilterstypes');

		if (is_array($object->element))
		{
			foreach ($object->element AS &$element)
			{
				$app->triggerEvent('getFieldsandfiltersFieldsHTML', array($templateFields, $fields, $element, $context, $params, $ordering));
			}
		}
		else
		{
			$app->triggerEvent('getFieldsandfiltersFieldsHTML', array($templateFields, $fields, $object->element, $context, $params, $ordering));
		}

		return $templateFields;
	}

	/**
	 * @since       1.2.0
	 **/
	public static function getFieldsLayoutsByItemID($option = null, $itemID = null, $fieldsID = null, $getAllextensions = true, $context = '', JRegistry $params = null, $ordering = 'ordering')
	{
		$object = self::getFieldsByItemID($option, $itemID, $fieldsID, $getAllextensions);

		return self::getFieldsLayouts($object, $context, $params, $ordering);
	}

	/**
	 * @since       1.2.0
	 **/
	public static function preparationContent(&$text, $context = '', $option = null, $itemID = null, array $excluded = array(), $syntax = null, $syntaxType = self::SYNTAX_SIMPLE)
	{
		$syntax     = $syntax ? $syntax : KextensionsPlugin::getParams('system', 'fieldsandfilters')->get('syntax', '#{%s}');
		$syntaxType = $syntaxType ? $syntaxType : KextensionsPlugin::getParams('system', 'fieldsandfilters')->get('syntax_type', self::SYNTAX_SIMPLE);

		if (strpos($syntax, '%s') !== false)
		{
			$prefix = explode('%s', $syntax);

			// simple performance check to determine whether bot should process further
			if (!($prefix = $prefix[0]) || strpos($text, $prefix) === false)
			{
				return true;
			}

			/* @deprecated  1.2.0 */
			if ($syntaxType == self::SYNTAX_OLD)
			{
				$regex = '(?P<field_id>\d+)(?:,(?P<item_id>\d+)|)(?:,(?P<option>[\w.-]+)|)';
			}
			/* @end deprecated  1.2.0 */
			else
			{
				$regex = '(?P<field_id>\d+)(?:,(?P<item_id>\d+|)(?::(?P<option>[\w.-]+)|)|)(?:,(?P<context>[\w.-]+)|)(?:,(?P<params>{.*?})|)';
			}

			$regex = '/' . sprintf($syntax, $regex) . '/i';

			// Find all instances of plugin and put in $matches for loadposition
			// $matches[0] is full pattern match
			preg_match_all($regex, $text, $matches, PREG_SET_ORDER);

			if (!$matches)
			{
				return true;
			}

			$jinput            = JFactory::getApplication()->input;
			$itemID            = ($itemID = (int) $itemID) ? $itemID : $jinput->get('id', 0, 'int');
			$option            = $option ? $option : $jinput->get('option');
			$extensionsOptions = FieldsandfiltersFactory::getExtensions()->getExtensionsColumn('option');
			$combinations      = array();
			$getAllextensions  = true;
			$excludes          = array();
			$isExtended        = $syntaxType == self::SYNTAX_EXTENDED;

			foreach ($matches as $match)
			{
				/* field id */
				if (!($fieldID = (int) $match['field_id']) || in_array($fieldID, $excluded))
				{
					$excludes[] = $match[0];
					continue;
				}

				/* context */
				if (!empty($match['context']) && $match['context'] != $context)
				{
					$excludes[] = $match[0];
					continue;
				}

				/* component - option + item id */
				$_itemID = (isset($match['item_id']) && ($val = (int) $match['item_id'])) ? $val : $itemID;
				$_option = !empty($match['option']) ? $match['option'] : $option;

				if (!in_array($_option, $extensionsOptions))
				{
					$excludes[] = $match[0];
					continue;
				}

				$key = $_option . '-' . $_itemID;
				if (!array_key_exists($key, $combinations))
				{
					$combinations[$key] = array(
						'item_id'   => $_itemID,
						'option'    => $_option,
						'fields_id' => array()
					);

					if ($isExtended)
					{
						$combinations[$key]['elements'] = array();
					}
					else
					{
						$combinations[$key]['matches'] = array();
					}
				}

				/* params */
				if ($isExtended)
				{
					$keyElement = $params = null;
					if (!empty($match['params']))
					{
						$params     = new JRegistry($match['params']);
						$keyElement = $params->toString();

						if ($keyElement != '{}')
						{
							$keyElement = md5($keyElement);
						}
						else
						{
							$keyElement = $params = null;
						}
					}

					if (!array_key_exists($keyElement, $combinations[$key]['elements']))
					{
						$combinations[$key]['elements'][$keyElement] = array(
							'matches' => array(),
							'params'  => $params
						);
					}

					$combinations[$key]['elements'][$keyElement]['matches'][$fieldID][] = $match[0];
				}
				else
				{
					$combinations[$key]['matches'][$fieldID][] = $match[0];
				}

				if (!in_array($fieldID, $combinations[$key]['fields_id']))
				{
					$combinations[$key]['fields_id'][] = $fieldID;
				}
			}

			if (!empty($combinations))
			{
				while ($combination = array_shift($combinations))
				{
					$object = self::getFieldsByItemID($combination['option'], $combination['item_id'], $combination['fields_id'], $getAllextensions);

					if ($isExtended)
					{
						$isFields = $object->fields->getProperties(true);
						$isFields = !empty($isFields);

						while ($element = array_shift($combination['elements']))
						{
							if (!$isFields)
							{
								$excludes = array_merge($excludes, KextensionsArray::flatten($element['matches']));
								continue;
							}

							$_object   = clone $object;
							$_fieldsID = array_keys($element['matches']);

							foreach ($_fieldsID AS $_fieldID)
							{
								if (!isset($_object->fields->$_fieldID))
								{
									unset($_object->fields->$_fieldID);

									$excludes = array_merge($excludes, $element['matches'][$_fieldID]);
									unset($element['matches'][$_fieldID]);
								}
							}

							$fieldsLayouts = self::getFieldsLayouts($_object, $context, $element['params'], 'id');

							foreach ($element['matches'] AS $fieldID => &$match)
							{
								$text = str_replace($match, $fieldsLayouts->get($fieldID, ''), $text);
							}
						}
					}
					else
					{
						$fieldsLayouts = self::getFieldsLayouts($object, $context, null, 'id');

						foreach ($combination['matches'] AS $fieldID => &$match)
						{
							$text = str_replace($match, $fieldsLayouts->get($fieldID, ''), $text);
						}
					}
				}
			}

			if (!empty($excludes))
			{
				$text = str_replace(array_unique($excludes), '', $text);
			}
		}

		return true;
	}

	/**
	 * @since       1.2.0
	 **/
	public static function getFieldsByTypeIDColumnFieldType($contentTypeID, $withStatic = null, $states = array(1, -1), $getAllextensions = true)
	{
		$withStatic = !is_null($withStatic) ? $withStatic : JComponentHelper::getParams('com_fieldsandfilters')->get('show_static_fields', true);

		$contentTypeID = (array) $contentTypeID;

		if ($getAllextensions && ($extension = FieldsandfiltersFactory::getExtensions()->getExtensionsByName(FieldsandfiltersExtensions::EXTENSION_DEFAULT)->get(FieldsandfiltersExtensions::EXTENSION_DEFAULT)))
		{
			$contentTypeID[] = (int) $extension->content_type_id;
		}

		$fieldsHelper = FieldsandfiltersFactory::getFields();

		if ($withStatic)
		{
			$fields = $fieldsHelper->getFieldsPivot('type', $contentTypeID, $states, FieldsandfiltersFields::VALUES_BOTH);
		}
		else
		{
			$staticMode = (array) FieldsandfiltersModes::getMode(FieldsandfiltersModes::MODE_STATIC, array());
			$otherMode  = (array) FieldsandfiltersModes::getModes(null, array(), true, $staticMode);

			$fields = $fieldsHelper->getFieldsByModeIDPivot('type', $contentTypeID, $otherMode, $states, FieldsandfiltersFields::VALUES_BOTH);
		}

		return $fields;
	}

	/**
	 * @since       1.0.0
	 **/
	protected static function _returnEmpty()
	{
		return new JObject(array('element' => new JObject(), 'fields' => new JObject()));
	}

	/**
	 * @since       1.1.0
	 * @deprecated  1.2.0
	 * @use         FieldsandfiltersFieldsSite::preparationContent()
	 **/
	public static function preparationConetent(&$text, $option = null, $itemID = null, $syntax = null, array $excluded = array())
	{
		self::preparationContent($text, '', $option, $itemID, $excluded, $syntax, self::SYNTAX_OLD);
	}

	/**
	 * @since         1.1.0
	 * @deprecated    1.2.0
	 * @use           FieldsandfiltersFieldsSite::getFieldsLayoutsByItemID()
	 **/
	public static function getFieldsByItemIDWithTemplate($option = null, $itemID = null, $fieldsID = null, $getAllextensions = true, $params = false, $ordering = 'ordering')
	{
		$object         = self::getFieldsByItemID($option, $itemID, $fieldsID, $getAllextensions);
		$templateFields = new JObject;

		$fields = $object->fields->getProperties();
		if (empty($fields))
		{
			return $templateFields;
		}

		$fields = new JObject(JArrayHelper::pivot($fields, 'field_type'));
		JPluginHelper::importPlugin('fieldsandfilterstypes');

		FieldsandfiltersFactory::getDispatcher()->trigger('getFieldsandfiltersFieldsHTML', array($templateFields, $fields, $object->element, $params, $ordering));

		return $templateFields;
	}
}