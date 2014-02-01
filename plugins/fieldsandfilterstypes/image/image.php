<?php
/**
 * @version     1.1.1
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_field_type.image
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

// Load the Factory Helper
JLoader::import('fieldsandfilters.factory', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/helpers');

/**
 * Checkbox type fild
 *
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_types.image
 * @since       1.0.0
 */
class plgFieldsandfiltersTypesImage extends JPlugin
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

		$syntax = KextensionsPlugin::getParams( 'system', 'fieldsandfilters' )->get( 'syntax', '#{%s}' );

		JHtml::_('behavior.formvalidation');
		$script[] = 'window.addEvent("domready", function() {';
		$script[] = '	if( document.formvalidator ) {';
		$script[] = '		document.formvalidator.setHandler("url", function(value) {';
		$script[] = '			regex = new RegExp("^(https?|ftp|rmtp|mms):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?", "i");';
		$script[] = '			return regex.test(value);';
		$script[] = '		});';
		$script[] = '	};';
		$script[] = '});';

		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		while ($field = array_shift($fields))
		{
			$root = new SimpleXMLElement('<fields />');
			$root->addAttribute('name', 'data');

			$rootJson = $root->addChild('fields');
			$rootJson->addAttribute('name', $field->id);

			$label = '<strong>'.$field->name.'</strong> '.sprintf($syntax,$field->id);

			if ($field->state == -1)
			{
				$label .= ' ['.JText::_('PLG_FIELDSANDFILTERS_FORM_ONLY_ADMIN').']';
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
				$label .= ' ['.JText::_('PLG_FIELDSANDFILTERS_FORM_GROUP_STATIC_TITLE').']';

				$element->addAttribute('type', 'spacer');
				$element->addAttribute('description', $field->data);
				$element->addAttribute('name', $field->id);
				$element->addAttribute('label', $label);
				$element->addAttribute('translate_label', 'false');
				$element->addAttribute('translate_description', 'false');
			}
			else
			{
				//image
				$element->addAttribute('name', 'image');
				$element->addAttribute('type', 'media');
				$element->addAttribute('class', 'inputbox');
				$element->addAttribute('label', 'PLG_FAF_TS_IE_FORM_IMAGE_LBL');
				$element->addAttribute('description', 'PLG_FAF_TS_IE_FORM_IMAGE_DESC');
				$element->addAttribute('filter', 'safehtml');

				if ($field->required)
				{
					$element->addAttribute('required', 'true');
				}

				//src
				$element = $rootJson->addChild('field');
				$element->addAttribute('name', 'src');
				$element->addAttribute('type', 'hidden');
				$element->addAttribute('filter', 'safehtml');
				$element->addAttribute('fieldset', $fieldset);

				// caption
				$element = $rootJson->addChild('field');
				$element->addAttribute('name', 'caption');
				$element->addAttribute('type', 'text');
				$element->addAttribute('class', 'inputbox');
				$element->addAttribute('labelclass', 'control-label');
				$element->addAttribute('label', 'PLG_FAF_TS_IE_FORM_CAPTION_LBL');
				$element->addAttribute('description', 'PLG_FAF_TS_IE_FORM_CAPTION_DESC');
				$element->addAttribute('filter', 'safehtml');
				$element->addAttribute('fieldset', $fieldset);

				// alt
				$element = $rootJson->addChild('field');
				$element->addAttribute('name', 'alt');
				$element->addAttribute('type', 'text');
				$element->addAttribute('class', 'inputbox');
				$element->addAttribute('labelclass', 'control-label');
				$element->addAttribute('label', 'PLG_FAF_TS_IE_FORM_ALT_LBL');
				$element->addAttribute('description', 'PLG_FAF_TS_IE_FORM_ALT_DESC');
				$element->addAttribute('filter', 'safehtml');
				$element->addAttribute('fieldset', $fieldset);

				if ($field->params->get('type.create_thumb'))
				{
					//src thumb
					$element = $rootJson->addChild('field');
					$element->addAttribute('name', 'src_thumb');
					$element->addAttribute('type', 'hidden');
					$element->addAttribute('filter', 'safehtml');
					$element->addAttribute('fieldset', $fieldset);
				}
				else
				{
					// link
					$element = $rootJson->addChild('field');
					$element->addAttribute('name', 'link');
					$element->addAttribute('type', 'text');
					$element->addAttribute('class', 'validate-url');
					$element->addAttribute('labelclass', 'control-label');
					$element->addAttribute('label', 'PLG_FAF_TS_IE_FORM_LINK_LBL');
					$element->addAttribute('description', 'PLG_FAF_TS_IE_FORM_LINK_DESC');
					$element->addAttribute('validate', 'url');
					$element->addAttribute('fieldset', $fieldset);

					// link target
					$element = $rootJson->addChild('field');
					$element->addAttribute('name', 'target');
					$element->addAttribute('type', 'list');
					$element->addAttribute('class', 'inputbox');
					$element->addAttribute('labelclass', 'control-label');
					$element->addAttribute('label', 'PLG_FAF_TS_IE_FORM_TARGET_LBL');
					$element->addAttribute('description', 'PLG_FAF_TS_IE_FORM_TARGET_DESC');
					$element->addAttribute('fieldset', $fieldset);

					KextensionsXML::addOptionsNode($element, array(
						'PLG_FAF_TS_IE_FORM_TARGET_OPTION_DEFAULT' => '',
						'PLG_FAF_TS_IE_FORM_TARGET_OPTION_BLANK'   => 1,
						'PLG_FAF_TS_IE_FORM_TARGET_OPTION_POPUP'   => 2,
						'PLG_FAF_TS_IE_FORM_TARGET_OPTION_MODAL'   => 3,
						'PLG_FAF_TS_IE_FORM_TARGET_OPTION_PARENT'  => 4

					));
				}
			}

			// hr bottom spacer
			$element = $rootJson->addChild('field');
			$element->addAttribute('type', 'spacer');
			$element->addAttribute('name', 'hr_bottom_spacer_' . $field->id);
			$element->addAttribute('hr', 'true');
			$element->addAttribute('fieldset', $fieldset);

			$form->addOrder($field->id, $field->ordering)
				->setField( $field->id, $root );
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
			$newItem->values->set('data', (string) $this->createImages(new stdClass, $newItem, $newItem->values));
		}
		elseif ($context == 'com_fieldsandfilters.element')
		{
			$data = $newItem->get('fields')->get('data', new JObject);
			$fields = FieldsandfiltersFieldsHelper::getFieldsByTypeIDColumnFieldType($newItem->get('content_type_id'))->get($this->_name);

			if ($fields)
			{
				$fields = is_array($fields) ? $fields : array($fields);

				while ($field = array_shift($fields))
				{
					$data->set($field->id, (string) $this->createImages($newItem, $field, $data->get($field->id, new JObject)));
				}
			}
		}

		return true;
	}

	/**
	 * @param $element
	 * @param $field
	 * @param $_data
	 *
	 * @return JRegistry|null
	 */
	protected function createImages($element, $field, $_data)
	{
		jimport('joomla.filesystem.file');

		$app = JFactory::getApplication();

		if (($image = $_data->get('image')) && file_exists(JPath::clean(JPATH_ROOT.'/'.$image)))
		{
			$_data = new JRegistry($_data->getProperties(true));

			$scaleImage  = (int) $field->params->def('type.scale', $this->params->get('scale', 0));
			$createThumb = (boolean) $field->params->get('type.create_thumb');
			$scaleThumb  = (int) $field->params->def('type.scale_thumb', $this->params->get('scale_thumb', 0));

			if ($scaleImage || ($createThumb && $scaleThumb))
			{
				$isCreated      = true;
				$isCreatedThumb = true;

				$folder    = FieldsandfiltersImage::getCacheFolder().'/'.$field->id.'/';
				$imageInfo = $this->prepareImageInfo($field, $element, $image, false, $scaleImage);

				if ($scaleImage)
				{
					$src    = JPath::clean($folder . $imageInfo->name);
					$srcOld = $_data->get('src', false);

					if ($src != $srcOld || !file_exists(JPath::clean(JPATH_ROOT.'/'.$src)))
					{
						try
						{
							if ($srcOld && file_exists($srcOld = JPath::clean(JPATH_ROOT.'/'.$srcOld)))
							{
								JFile::delete($srcOld);
							}

							if ($src = FieldsandfiltersImage::createImage($field->name, $imageInfo))
							{
								$_data->set('src', $src);

								$app->enqueueMessage(JText::sprintf('COM_FIELDSANDFILTERS_SUCCESS_CREATE_IMAGE', $field->name));
							}
							else
							{
								throw new RuntimeException(JText::sprintf('COM_FIELDSANDFILTERS_ERROR_NOT_CREATE_IMAGE', $field->name));
							}
						} catch (Exception $e)
						{
							$isCreated = false;

							$_data->set('src', '');
							$app->enqueueMessage($e->getMessage(), 'error');
						}
					}
				}

				if ($createThumb && $scaleThumb)
				{
					$src    = JPath::clean($folder.'thumbs/'.$imageInfo->name);
					$srcOld = $_data->get('src_thumb', false);

					if ($scaleThumb && ($src != $srcOld || !file_exists(JPath::clean(JPATH_ROOT.'/'.$src))))
					{
						$imageInfo = $this->prepareImageInfo($field, $element, $image, $imageInfo->name, $scaleThumb, 'thumb');

						try
						{
							if ($srcOld && file_exists($srcOld = JPath::clean(JPATH_ROOT.'/'.$srcOld)))
							{
								JFile::delete($srcOld);
							}

							if ($src = FieldsandfiltersImage::createImage(($field->name . ' Thumbs'), $imageInfo))
							{
								$_data->set('src_thumb', $src);

								$app->enqueueMessage(JText::sprintf('COM_FIELDSANDFILTERS_SUCCESS_CREATE_IMAGE', $field->name.' Thumb'));
							}
							else
							{
								throw new RuntimeException(JText::sprintf('COM_FIELDSANDFILTERS_ERROR_NOT_CREATE_IMAGE', $field->name.' Thumb'));
							}
						} catch (Exception $e)
						{
							$isCreatedThumb = false;

							$_data->set('src_thumb', '');
							$app->enqueueMessage($e->getMessage(), 'error');
						}
					}
				}

				if (!$isCreated && !($createThumb && $isCreatedThumb))
				{
					$_data = null;
				}
			}

			unset($imageInfo);
		}
		else
		{
			if (($src = $_data->get('src')) && file_exists(JPath::clean(JPATH_ROOT.'/'.$src)))
			{
				// delete image
				if (!JFile::delete(JPATH_ROOT.'/'.$src))
				{
					$app->enqueueMessage(JText::sprintf('PLG_FAF_TS_IE_SUCCESS_DELETE_IMAGE', $field->name, $src), 'error');
				}
			}

			if (($src = $_data->get('src_thumb')) && file_exists(JPath::clean(JPATH_ROOT.'/'.$src)))
			{
				// delete thumb
				if (!JFile::delete(JPATH_ROOT.'/'.$src))
				{
					$app->enqueueMessage(JText::sprintf('PLG_FAF_TS_IE_SUCCESS_DELETE_IMAGE', $field->name.' Thumb', $src), 'error');
				}
			}

			$_data = null;
		}

		return $_data;
	}

	/**
	 * @since       1.1.0
	 */
	public function onFieldsandfiltersBeforeDeleteData($context, $item)
	{
		if ($context == 'com_fieldsandfilters.field' && isset($item->type) && $item->type == $this->_name)
		{
			jimport('joomla.filesystem.folder');

			$path     = FieldsandfiltersImage::getCacheFolder().'/'.$item->id;
			$fullname = JPath::clean(JPATH_ROOT.'/'.$path);

			if (is_dir($fullname))
			{
				if (!JFolder::delete($fullname))
				{
					JFactory::getApplication()->enqueueMessage(JText::sprintf('PLG_FAF_TS_IE_SUCCESS_DELETE_FOLDER', $item->name, $path));
				}
			}
		}
		elseif ($context == 'com_fieldsandfilters.element')
		{
			$data   = $item->get('fields', new JObject)->get('data', new JObject);
			$fields = FieldsandfiltersFieldsHelper::getFieldsByTypeIDColumnFieldType($item->get('content_type_id'))->get($this->_name);

			if ($fields)
			{
				$notDelete = array();

				$fields = is_array($fields) ? $fields : array($fields);

				jimport('joomla.filesystem.file');

				while ($field = array_shift($fields))
				{
					$_data = $data->get($field->id, new JObject);

					if (($src = $_data->get('src')) && file_exists($fullname = JPath::clean(JPATH_ROOT.'/'.$src)))
					{
						if (!JFile::delete($fullname))
						{
							array_push($notDelete, $src);
						}
					}

					if (($src = $_data->get('src_thumb')) && file_exists($fullname = JPath::clean(JPATH_ROOT.'/'.$src)))
					{
						if (!JFile::delete($fullname))
						{
							array_push($notDelete, $src);
						}
					}
				}

				if (!empty($notDelete))
				{
					JFactory::getApplication()->enqueueMessage(JText::sprintf('PLG_FAF_TS_IE_SUCCESS_DELETE_ELEMENT', implode(', ', $notDelete)));
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
	public function getFieldsandfiltersFieldsHTML(JObject $layoutFields, Jobject $fields, stdClass $element, $context = 'fields', JRegistry $params = null, $ordering = 'ordering' )
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

		jimport('joomla.filesystem.file');

		while ($field = array_shift($fields))
		{
			$modeName 	= FieldsandfiltersModes::getModeName( $field->mode );
			$isStaticMode 	= (  $modeName == FieldsandfiltersModes::MODE_STATIC );

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

			if( $params )
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

			// create new image if not exists		
			$scaleImage  = (int) $field->params->def('type.scale', $this->params->get('scale', 0));
			$createThumb = (boolean) $field->params->get('type.create_thumb');
			$scaleThumb  = (int) $field->params->def('type.scale_thumb', $this->params->get('scale_thumb', 0));

			if ($scaleImage || ($createThumb && $scaleThumb))
			{
				$data = $isStaticMode ? $field->data : $element->data->get($field->id, new JRegistry);

				if (($image = $data->get('image')) && file_exists(JPath::clean(JPATH_ROOT.'/'.$image)))
				{
					if ($scaleImage && ($src = $data->get('src')) && !file_exists(JPath::clean(JPATH_ROOT.'/'.$src)))
					{
						$imageInfo = self::prepareImageInfo($field, $element, $image, basename($src), $scaleImage);

						try
						{
							if (!FieldsandfiltersImage::createImage($field->name, $imageInfo))
							{
								throw new RuntimeException(JText::sprintf('COM_FIELDSANDFILTERS_ERROR_NOT_CREATE_IMAGE', $field->name));
							}
						} catch (Exception $e)
						{
							JLog::add($e->getMessage(), JLog::ERROR, 'plgFieldsandfiltersTypesImage');
						}
					}

					if ($createThumb && $scaleThumb && ($src = $data->get('src_thumb')) && !file_exists(JPath::clean(JPATH_ROOT.'/'.$src)))
					{
						$imageInfo = $this->prepareImageInfo($field, $element, $image, basename($src), $scaleThumb, 'thumb');

						try
						{
							if (!FieldsandfiltersImage::createImage(($field->name . ' Thumbs'), $imageInfo))
							{
								throw new RuntimeException(JText::sprintf('COM_FIELDSANDFILTERS_ERROR_NOT_CREATE_IMAGE', $field->name . ' Thumb'));
							}
						} catch (Exception $e)
						{
							JLog::add($e->getMessage(), JLog::ERROR, 'plgFieldsandfiltersTypesImage');
						}
					}

				}

				unset($data, $imageInfo);
			}

			unset($fieldTypeParams);

			$layoutField = FieldsandfiltersFieldsField::getLayout('field', ($isStaticMode ? $modeName : 'field'), $field->params);

			$variables->field = $field;

			$layout = KextensionsPlugin::renderLayout($variables, $layoutField);
			$layoutFields->set(KextensionsArray::getEmptySlotObject($layoutFields, $field->$ordering, false), $layout);

			if( $params )
			{
				$field->params = $paramsTemp;
				unset( $paramsField );
			}
		}

		unset($variables, $imageInfo);
	}

	protected function prepareImageInfo($field, $element, $image, $name = false, $method = 1, $suffix = false)
	{
		$paramSuffix = $suffix ? '_' . $suffix : '';

		$info             = new JObject();
		$info->path       = $image;
		$info->folder     = $field->id . ($suffix ? '/'.$suffix.'s' : '');
		$info->prefixName = $element->id;
		$info->width      = (int) $field->params->def('type.width' . $paramSuffix, $this->params->get('width' . $paramSuffix, 0));
		$info->height     = (int) $field->params->def('type.height' . $paramSuffix, $this->params->get('height' . $paramSuffix, 0));
		$info->method     = (int) $method;
		$info->quality    = (int) $field->params->def('type.quality' . $paramSuffix, $this->params->get('quality' . $paramSuffix, 0));

		$info->name = !empty($name) ? $name : FieldsandfiltersImage::createNameImage($info);

		return $info;
	}
}