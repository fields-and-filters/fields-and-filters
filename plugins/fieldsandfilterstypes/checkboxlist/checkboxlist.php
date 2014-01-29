<?php
/**
 * @version     1.1.1
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_field_type.checkboxlist
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

/**
 * Checkboxlist type fild
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_types.checkboxlist
 * @since       1.0.0
 */
class plgFieldsandfiltersTypesCheckboxlist extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since	1.0.0
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
		
		JForm::addFieldPath( JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/models/fields' );
		
		$fields = is_array( $fields ) ? $fields : array( $fields );
		$syntax = KextensionsPlugin::getParams( 'system', 'fieldsandfilters' )->get( 'syntax', '#{%s}' );
		
		while( $field = array_shift( $fields ) )
		{
			$root = new SimpleXMLElement( '<fields />' );
			$root->addAttribute( 'name', 'connections' );
			
			if( !empty( $field->description ) && $field->params->get( 'base.admin_enabled_description', 0 ) )
			{
				switch( $field->params->get( 'base.admin_description_type', 'description' ) )
				{
					case 'tip':
						$element = $root->addChild( 'field'  );
						$element->addAttribute( 'description', $field->description );
						$element->addAttribute( 'translate_description', 'false' );
						$element->addAttribute( 'fieldset', $fieldset );
					break;
					case 'description':
					default:
						$element = $root->addChild( 'field' );
						$element->addAttribute( 'type', 'spacer' );
						$element->addAttribute( 'name', 'description_spacer_' . $field->id );
						$element->addAttribute( 'label', $field->description );
						$element->addAttribute( 'translate_label', 'false' );
						$element->addAttribute( 'fieldset', $fieldset );
						
						$element = $root->addChild( 'field'  );
						$element->addAttribute( 'fieldset', $fieldset );
					break;
				}
			}
			else
			{
				$element = $root->addChild( 'field'  );
				$element->addAttribute( 'fieldset', $fieldset );
			}
			
			$label = '<strong>' . $field->name . '</strong> ' . sprintf($syntax,$field->id);
				
			if( $field->state == -1 )
			{
				$label .= ' [' . JText::_( 'PLG_FIELDSANDFILTERS_FORM_ONLY_ADMIN' ) . ']';
			}

			$element->addAttribute( 'id', $field->id );
			$element->addAttribute( 'name', $field->id );
			$element->addAttribute( 'class', 'inputbox' );
			$element->addAttribute( 'labelclass' , 'control-label' );
			$element->addAttribute( 'label', $label );
			$element->addAttribute( 'translate_label', 'false' );
			$element->addAttribute( 'type', 'fieldsandfiltersCheckboxes' );
			$element->addAttribute( 'filter', 'int_array' );
			$element->addAttribute( 'translate_options', 'false' );
			
			if( $field->required )
			{
				$element->addAttribute( 'required', 'true' );
			}
			
			// FieldsandfiltersFieldValues
			$values = $field->values->getProperties();
			
			if( !empty( $values ) )
			{
				while( $value = array_shift( $values ) )
				{
					$option = $element->addChild( 'option', ( $value->value . ' [' . $value->alias . ']' ) );
					$option->addAttribute( 'value', $value->id );
				}
				
				if( $isNew && ( $default = $field->params->get( 'type.default' ) ) )
				{
					$default = array_unique( (array) $default );
					JArrayHelper::toInteger( $default );

					$form->setData( 'connections.' . $field->id, $default );
				}
			}
			
			// hr bottom spacer
			$element = $root->addChild( 'field' );
			$element->addAttribute( 'type', 'spacer' );
			$element->addAttribute( 'name', 'hr_bottom_spacer_' . $field->id );
			$element->addAttribute( 'hr', 'true' );
			$element->addAttribute( 'fieldset', $fieldset );
			
			$form->addOrder($field->id, $field->ordering)
				->setField( $field->id, $root );
		}
		
		return true;
	}
	
	/**
	 * @since	1.1.0
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
			
			if( ( $isStaticMode && empty( $field->connections ) ) || ( $modeName == 'field' && ( !isset( $element->connections ) || !property_exists( $element->connections, $field->id ) ) ) )
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
				FieldsandfiltersFieldsField::preparationContent('base.site_enabled_description', $field, 'description', $context, $field->id, $params);
			}

			FieldsandfiltersFieldsField::preparationContentValues('type.prepare_values', $field, $context, $field->id, $params);

//			echo '<pre>';
//			print_r($field->values);
//			echo '</pre>';
			// [todo] po zapisie wartosci wraca na zlego filtra poniewaz nie jest przekazywane field_id

			// [todo] dorobic dla wartosci pol mozliwośsc preparationContent

			$layoutField = $field->params->get('type.field_layout', 'default');
			
			if(!$field->params->get('is.field_layout', false))
			{
				$layoutMode = $isStaticMode ? $modeName : 'field';
				if (strpos($layoutField, ':') !== false)
				{
					$layoutField = str_replace(':', ':'.$layoutMode.'/', $layoutField);
				}
				else
				{
					$layoutField = $layoutMode.'/'.$layoutField;
				}

				$field->params->set('is.field_layout', true);
				$field->params->set('type.field_layout', $layoutField);
			}

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
	
	/**
	 * @since	1.1.0
	 */
	public function getFieldsandfiltersFiltersHTML( JObject $layoutFields, JObject $fields, $context = 'filters', JRegistry $params = null, $ordering = 'ordering' )
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
		
		$isParams = ( $params && $params instanceof JRegistry );
		
		while( $field = array_shift( $fields ) )
		{
			if( $isParams )
			{
				$paramsTemp 	= $field->params;
				$paramsFilter 	= clone $field->params;
				
				$paramsFilter->merge( $params );
				$field->params 	= $paramsFilter;
			}

			if ($field->params->get( 'base.site_enabled_description', 0 ) && ($prepareType =  $field->params->get( 'base.prepare_description', 0 )))
			{
				FieldsandfiltersFieldsField::preparationContent($prepareType, $field->description, $context, $field->id, $params);
			}
			
			$layoutFilter = $field->params->get( 'type.filter_layout' );
			
			if( !$layoutFilter )
			{
				$layoutFilter	= 'filter-default';
			}
			
			$field->params->set( 'type.filter_layout', $layoutFilter );
			
			$variables->field = $field;
			
			$layout = KextensionsPlugin::renderLayout( $variables, $layoutFields );
			$layoutFields->set( KextensionsArray::getEmptySlotObject( $layoutFields, $field->$ordering, false ), $layout );
			
			if( $isParams )
			{
				$field->params = $paramsTemp;
				unset( $paramsFilter );
			}
		}
		
		unset( $variables );
	}
}
