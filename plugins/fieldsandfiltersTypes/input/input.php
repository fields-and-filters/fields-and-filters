<?php
/**
 * @version     1.0.0
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_field_type.input
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

// Load the Factory Helper
JLoader::import( 'fieldsandfilters.factory', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/helpers' );

/**
 * Input type fild
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_types.input
 * @since       1.0.0
 * 
 */
class plgFieldsandfiltersTypesInput extends JPlugin
{
	/**
	 * @since       1.0.0
	 */
	protected $_variables;
	
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
		$this->loadLanguage();
	}
	
	/**
	 * @param	JForm	$form	The form to be altered.
	 * @param	array	$data	The associated data for the form.
	 *
	 * @return	boolean
	 * @since       1.1.0
	 */
	public function onFieldsandfiltersPrepareFormField( $isNew = false )
	{
		$jregistry = JRegistry::getInstance( 'fieldsandfilters' );
		
		if( !( $fields = $jregistry->get( 'fields.' . $this->_name ) ) )
		{
			return true;
		}
		
		// Load Array Helper
		$fields 	= is_array( $fields ) ? $fields : array( $fields );
		$staticMode 	= (array) FieldsandfiltersFactory::getPluginTypes()->getMode( 'static' );
		$arrayHelper	= FieldsandfiltersFactory::getArray();
		
		while( $field = array_shift( $fields ) )
		{
			$root = new JXMLElement( '<fields />' );
			$root->addAttribute( 'name', 'data' );
			
			if( $field->params->get( 'type.hidden', 0 ) )
			{
				$element = $root->addChild( 'field'  );
				$element->addAttribute( 'type', 'hidden' );
			}
			else
			{
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
							$element->addAttribute( 'name', 'description_spacer_' . $field->field_id );
							$element->addAttribute( 'label', $field->description );
							$element->addAttribute( 'translate_label', 'false' );
							
							$element = $root->addChild( 'field'  );
						break;
					}
				}
				else
				{
					$element = $root->addChild( 'field'  );
				}
				
				$label = '<strong>' . $field->field_name . '</strong> (' . $field->field_id . ')';
				
				if( $field->state == -1 )
				{
					$label .= ' [' . JText::_( 'PLG_FAF_TS_IT_FORM_ONLY_ADMIN' ) . ']';
				}
				
				if( in_array( $field->mode, $staticMode ) )
				{
					$element->addAttribute( 'type', 'spacer' );
					$element->addAttribute( 'description', $field->data );
					
					$label .= ' [' . JText::_( 'PLG_FAF_TS_TA_FORM_FIELD_STATIC' ) . ']';
				}
				else
				{
					if( $field->params->get( 'type.readonly', 0 ) )
					{
						$element->addAttribute( 'class', 'readonly' );
					}
					else
					{
						$element->addAttribute( 'class', 'inputbox' );
					}
					
					$element->addAttribute( 'type', 'text' );
					
					if( $field->required )
					{
						$element->addAttribute( 'required', 'true' );
					}
				}
				
				$element->addAttribute( 'labelclass' , 'control-label' );
				$element->addAttribute( 'label', $label );
				$element->addAttribute( 'translate_label', 'false' );
			}
			
			$element->addAttribute( 'id', $field->field_id );
			$element->addAttribute( 'name', $field->field_id );
			
			// hr bottom spacer
			$element = $root->addChild( 'field' );
			$element->addAttribute( 'type', 'spacer' );
			$element->addAttribute( 'name', 'hr_bottom_spacer_' . $field->field_id );
			$element->addAttribute( 'hr', 'true' );
			
			$jregistry->set( 'form.fields.' . $arrayHelper->getEmptySlotObject( $jregistry, $field->ordering ), $root );
			
			unset( $element, $elementSpacer );
		}
		
		return true;
	}
	
	/**
	 * @since       1.1.0
	 */
	public function getFieldsandfiltersFieldsHTML( $templateFields, $fields, $element, $params = false, $ordering = 'ordering' )
	{
		if( !( $fields = $fields->get( $this->_name ) ) )
		{
			return;
		}
		
		$fields = is_array( $fields ) ? $fields : array( $fields );
		
		// Load Extensions Helper
		$extensionsHelper = FieldsandfiltersFactory::getExtensions();
		
		// Load Array Helper
		$arrayHelper = FieldsandfiltersFactory::getArray();
		
		// Load Plugin Types Helper
		$pluginTypesHelper = FieldsandfiltersFactory::getPluginTypes();
		
		if( is_null( $this->_variables ) )
		{
			$this->_variables = new JObject( array( 'type' => $this->_type, 'name' => $this->_name, 'params' => $this->params ) );
		}
		
		$this->_variables->element = $element;
		
		while( $field = array_shift( $fields ) )
		{
			$modeName = $pluginTypesHelper->getModeName( $field->mode );
			
			if( ( $modeName == 'static' && empty( $field->data ) ) || ( $modeName == 'field' && !property_exists( $element->data, $field->field_id ) ) )
			{
				continue;
			}
			
			if( $isParams = ( $params && $params instanceof JRegistry ) )
			{
				$paramsTemp 	= $field->params;
				$paramsField 	= clone $field->params;
				
				$paramsField->merge( $params );
				$field->params 	= $paramsField;
			}
			
			$layoutField = $field->params->get( 'type.field_layout' );
			
			if( !$layoutField )
			{
				$layoutField	= $modeName . '-default';
			}
			
			$field->params->set( 'type.field_layout', $layoutField );
			
			$this->_variables->field = $field;
			
			$template = $extensionsHelper->loadPluginTemplate( $this->_variables, $layoutField );
			$templateFields->set( $arrayHelper->getEmptySlotObject( $templateFields, $field->$ordering, false ), $template );
			
			if( $isParams )
			{
				$field = $paramsTemp;
				unset( $paramsField );
			}
		}
		
		unset( $this->_variables->element, $this->_variables->field );
	}
	
	/**
	 * Loads the plugin language file
	 *
	 * @param   string  $extension  The extension for which a language file should be loaded
	 * @param   string  $basePath   The basepath to use
	 *
	 * @return  boolean  True, if the file has successfully loaded.
	 *
	 * @since       1.0.0
	 */
	public function loadLanguage( $extension = '', $basePath = JPATH_ADMINISTRATOR )
	{
		if( empty( $extension ) )
		{
			$extension = 'plg_' . $this->_type . '_' . $this->_name;
		}
		
		$lang = JFactory::getLanguage();
		
		return $lang->load( $extension, $basePath, null, false, false )
			|| $lang->load( $extension, JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name, null, false, false )
			|| $lang->load( $extension , $basePath, $lang->getDefault(), false, false )
			|| $lang->load( $extension, JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name, $lang->getDefault(), false, false );
	}
}
