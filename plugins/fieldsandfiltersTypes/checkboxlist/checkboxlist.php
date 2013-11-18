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
			// load plugin language
			KextensionsLanguage::load( 'plg_' . $this->_type . '_' . $this->_name, JPATH_ADMINISTRATOR );
		}
	}
	
	/**
	 * @param	JForm	$form	The form to be altered.
	 * @param	array	$data	The associated data for the form.
	 *
	 * @return	boolean
	 * @since	1.1.0
	 */
	public function onFieldsandfiltersPrepareFormField( $fieldsForm, $isNew = false, $fieldset = 'fieldsandfilters' )
	{
		if( !$fieldsForm instanceof KextensionsFormElement )
		{
			return true;
		}
		
		if( !( $fields = $fieldsForm->getElement( $this->_name ) ) )
		{
			return true;
		}
		
		JForm::addFieldPath( JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/models/fields' );
		
		$fields = is_array( $fields ) ? $fields : array( $fields );
		
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
						$element->addAttribute( 'name', 'description_spacer_' . $field->field_id );
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
			
			$label = '<strong>' . $field->field_name . '</strong> {' . $field->field_id . '}';
				
			if( $field->state == -1 )
			{
				$label .= ' [' . JText::_( 'PLG_FIELDSANDFILTERS_FORM_ONLY_ADMIN' ) . ']';
			}

			$element->addAttribute( 'id', $field->field_id );
			$element->addAttribute( 'name', $field->field_id );
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
					$option = $element->addChild( 'option', ( $value->field_value . ' [' . $value->field_value_alias . ']' ) );
					$option->addAttribute( 'value', $value->field_value_id );
				}
				
				if( $isNew && ( $default = $field->params->get( 'type.default' ) ) )
				{
					$default = array_unique( (array) $default );
					JArrayHelper::toInteger( $default );
					
					$fieldsForm->setData( 'connections.' . $field->field_id, $default );
				}
			}
			
			// hr bottom spacer
			$element = $root->addChild( 'field' );
			$element->addAttribute( 'type', 'spacer' );
			$element->addAttribute( 'name', 'hr_bottom_spacer_' . $field->field_id );
			$element->addAttribute( 'hr', 'true' );
			$element->addAttribute( 'fieldset', $fieldset );
			
			$fieldsForm->setField( $field->ordering, $root );
		}
		
		return true;
	}
	
	/**
	 * @since	1.1.0
	 */
	public function getFieldsandfiltersFieldsHTML( $layoutFields, $fields, $element, $params = false, $ordering = 'ordering' )
	{
		if( !( $fields = $fields->get( $this->_name ) ) )
		{
			return;
		}
		
		$fields = is_array( $fields ) ? $fields : array( $fields );
		
		// Load Types Helper
		$typesHelper = FieldsandfiltersFactory::getTypes();
		
		$variables 		= new JObject;
		$variables->type	= $this->_type;
		$variables->name	= $this->_name;
		$variables->params	= $this->params;
		$variables->element 	= $element;
		
		$isParams = ( $params && $params instanceof JRegistry );
		
		while( $field = array_shift( $fields ) )
		{
			$modeName 	= $typesHelper->getModeName( $field->mode );
			$isStaticMode 	= (  $modeName == 'static' );
			
			if( ( $isStaticMode && empty( $field->connections ) ) || ( $modeName == 'field' && ( !isset( $element->connections ) || !property_exists( $element->connections, $field->field_id ) ) ) )
			{
				continue;
			}
			
			if( $isParams )
			{
				$paramsTemp 	= $field->params;
				$paramsField 	= clone $field->params;
				
				$paramsField->merge( $params );
				$field->params 	= $paramsField;
			}
			
			if( $field->params->get( 'base.prepare_description', 0 ) && $field->params->get( 'base.site_enabled_description', 0 ) )
			{
				FieldsandfiltersFieldsHelper::preparationConetent( $field->description, null, null, null, array( $field->field_id ) );
				// [TODO] do poprawy nie ta metoda i nie ma context
			}
			
			$layoutField = $field->params->get( 'type.field_layout' );
			
			if( !$layoutField )
			{
				$layoutField	= ( $isStaticMode ? $modeName : 'field' ) . '-default';
			}
			
			$field->params->set( 'type.field_layout', $layoutField );
			
			$variables->field = $field;
			
			$layout = KextensionsPlugin::renderLayout( $variables, $layoutField );
			$layoutFields->set( KextensionsArray::getEmptySlotObject( $layoutFields, $field->$ordering, false ), $layout );
			
			if( $isParams )
			{
				$field = $paramsTemp;
				unset( $paramsField );
			}
		}
		
		unset( $variables );
	}
	
	/**
	 * @since	1.1.0
	 */
	public function getFieldsandfiltersFiltersHTML( $layoutFields, $fields, $params = false, $ordering = 'ordering' )
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
			
			if( $field->params->get( 'base.prepare_description', 0 ) && $field->params->get( 'base.site_enabled_description', 0 ) )
			{
				FieldsandfiltersFieldsHelper::preparationConetent( $field->description, null, null, null, array( $field->field_id ) );
			}
			
			$layoutFilter = $field->params->get( 'type.filter_layout' );
			
			if( !$layoutFilter )
			{
				$layoutFilter	= 'filter-default';
			}
			
			$field->params->set( 'type.filter_layout', $layoutFilter );
			
			$variables->field = $field;
			
			$layout = KextensionsPlugin::renderLayout( $variables, $layoutField );
			$layoutFields->set( KextensionsArray::getEmptySlotObject( $layoutFields, $field->$ordering, false ), $layout );
			
			if( $isParams )
			{
				$field = $paramsTemp;
				unset( $paramsFilter );
			}
		}
		
		unset( $variables );
	}
}
