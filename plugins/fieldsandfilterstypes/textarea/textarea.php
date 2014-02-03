<?php
/**
 * @version     1.1.1
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_field_type.textarea
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

/**
 * Textarea type fild
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_types.textarea
 * @since       1.0.0
 */
class plgFieldsandfiltersTypesTextarea extends JPlugin
{	
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.0.0
	 */
	public function __construct( &$subject, $config )
	{
		parent::__construct( $subject, $config );

		if( JFactory::getApplication()->isAdmin() )
		{
			$this->loadLanguage();
		}
	}

	/**
	 * onFieldsandfiltersPrepareFormField
	 *
	 * @param	KextensionsForm     $form	The form to be altered.
	 * @param	JObject     $data	The associated data for the form.
	 * @param   boolean     $isNew  Is element is new
	 * @param   string      $fieldset   Fieldset name
	 *
	 * @return	boolean
	 * @since	1.1.0
	 */
	public function onFieldsandfiltersPrepareFormField( KextensionsForm $form, JObject $data, $isNew = false, $fieldset = 'fieldsandfilters' )
	{
		if( !( $fields = $data->get( $this->_name ) ) )
		{
			return true;
		}
		
		$fields 	= is_array( $fields ) ? $fields : array( $fields );
		$staticMode 	= (array) FieldsandfiltersModes::getMode(FieldsandfiltersModes::MODE_STATIC);
		$syntax         = KextensionsPlugin::getParams( 'system', 'fieldsandfilters' )->get( 'syntax', '#{%s}' );
		
		while( $field = array_shift( $fields ) )
		{
			$root = new SimpleXMLElement( '<fields />' );
			$root->addAttribute( 'name', 'data' );
			
			if( !empty( $field->description ) && $field->params->get( 'base.admin_enabled_description', 0 ) )
			{
				switch( $field->params->get( 'base.admin_description_type', 'description' ) )
				{
					case 'tip':
						$element = $root->addChild( 'field'  );
						$element->addAttribute( 'description', $field->description );
						$element->addAttribute( 'translate_description', 'false' );
					break;
					case 'description':
					default:
						$element = $root->addChild( 'field' );
						$element->addAttribute( 'type', 'spacer' );
						$element->addAttribute( 'name', 'description_spacer_' . $field->id );
						$element->addAttribute( 'label', $field->description );
						$element->addAttribute( 'translate_label', 'false' );
						$element->addAttribute('fieldset', $fieldset);
						
						$element = $root->addChild( 'field'  );
					break;
				}
			}
			else
			{
				$element = $root->addChild( 'field'  );
			}

			$label = '<strong>'.$field->name.'</strong> '.sprintf($syntax,$field->id);
			
			if( $field->state == -1 )
			{
				$label .= ' [' . JText::_( 'PLG_FIELDSANDFILTERS_FORM_ONLY_ADMIN' ) . ']';
			}
			
			if( in_array( $field->mode, $staticMode ) )
			{
				$element->addAttribute( 'type', 'spacer' );
				$element->addAttribute( 'description', $field->data );
				$element->addAttribute( 'translate_description', 'false' );
				
				$label .= ' [' . JText::_( 'PLG_FIELDSANDFILTERS_FORM_GROUP_STATIC_TITLE' ) . ']';
			}
			else
			{
				if( $field->params->get( 'type.display_editor' ) )
				{
					$element->addAttribute( 'type', 'editor' );
					$element->addAttribute( 'hide', 'readmore,pagebreak' );
					
					if( $editor = $field->params->get( 'type.editor' ) )
					{
						$element->addAttribute( 'editor', $editor );
					}
				}
				else
				{
					$element->addAttribute( 'type', 'textarea' );
					$element->addAttribute( 'class', 'inputbox' );
				}
				
				if( $rows = (int) $field->params->get( 'type.rows' ) )
				{
					$element->addAttribute( 'rows', $rows );
				}
				
				if( $cols = (int) $field->params->get( 'type.cols' ) )
				{
					$element->addAttribute( 'cols', $cols );
				}
				
				$element->addAttribute( 'filter', 'JComponentHelper::filterText' );
				
				if( $field->required )
				{
					$element->addAttribute( 'required', 'true' );
				}
			}
			
			$element->addAttribute( 'name', $field->id );
			$element->addAttribute( 'labelclass' , 'control-label' );
			$element->addAttribute( 'label', $label );
			$element->addAttribute( 'translate_label', 'false' );
			$element->addAttribute('fieldset', $fieldset);
			
			// hr bottom spacer
			$element = $root->addChild( 'field' );
			$element->addAttribute( 'type', 'spacer' );
			$element->addAttribute( 'name', 'hr_bottom_spacer_' . $field->id );
			$element->addAttribute( 'hr', 'true' );
			$element->addAttribute('fieldset', $fieldset);

			$form->addOrder($field->id, $field->ordering)
				->setField( $field->id, $root );
		}
		
		return true;
	}
	
	/**
	 * @since       1.1.0
	 */
	public function getFieldsandfiltersFieldsHTML( JObject $layoutFields, Jobject $fields, stdClass $element, $context = 'fields', JRegistry $params = null, $ordering = 'ordering' )
	{
		if( !( $fields = $fields->get( $this->_name ) ) )
		{
			return;
		}
		
		$fields = is_array( $fields ) ? $fields : array( $fields );
		
		$variables 		= new JObject;
		$variables->type	= $this->_type;
		$variables->name	= $this->_name;
		$variables->params	= $this->params;
		$variables->element 	= $element;

		while( $field = array_shift( $fields ) )
		{
			$modeName 	= FieldsandfiltersModes::getModeName( $field->mode );
			$isStaticMode 	= (  $modeName == FieldsandfiltersModes::MODE_STATIC );
			
			if( ( $isStaticMode && empty( $field->data ) ) || ( $modeName == 'field' && ( !isset( $element->data ) || !property_exists( $element->data, $field->id ) ) ) )
			{
				continue;
			}
			
			if( $params )
			{
				$paramsTemp 	= $field->params;
				$paramsField 	= clone $field->params;
				
				$paramsField->merge( $params );
				$field->params 	= $paramsField;
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

			$layout = KextensionsPlugin::renderLayout( $variables, $layoutField );
			$layoutFields->set( KextensionsArray::getEmptySlotObject( $layoutFields, $field->$ordering, false ), $layout );

			if( $params )
			{
				$field->params = $paramsTemp;
				unset( $paramsField );
			}
		}
		
		unset( $variables );
	}
}