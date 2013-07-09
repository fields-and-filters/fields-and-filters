<?php
/**
 * @version     1.0.0
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_field_type.textarea
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

// Load the Factory Helper
JLoader::import( 'fieldsandfilters.factory', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/helpers' );

/**
 * Checkbox type fild
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_types.textarea
 * @since       1.0.0
 * 
 */
class plgFieldsandfiltersTypesTextarea extends JPlugin
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
		
		$fields 	= is_array( $fields ) ? $fields : array( $fields );
		$staticMode 	= (array) FieldsandfiltersFactory::getPluginTypes()->getMode( 'static' );
		
		while( $field = array_shift( $fields ) )
		{
			$root = new JXMLElement( '<fields />' );
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
			
			$label = '<strong>' . $field->field_name . '</strong> (' . $field->field_id . ')';;
			
			if( $field->state == -1 )
			{
				$label .= ' [' . JText::_( 'PLG_FAF_TS_TA_FORM_ONLY_ADMIN' ) . ']';
			}
			
			if( in_array( $field->mode, $staticMode ) )
			{
				$element->addAttribute( 'type', 'spacer' );
				$element->addAttribute( 'description', $field->data );
				
				$label .= ' [' . JText::_( 'PLG_FAF_TS_TA_FORM_FIELD_STATIC' ) . ']';
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
				}
				
				if( $rows = (int) $field->params->get( 'type.rows' ) )
				{
					$element->addAttribute( 'rows', $rows );
				}
				
				if( $cols = (int) $field->params->get( 'type.cols' ) )
				{
					$element->addAttribute( 'cols', $cols );
				}
				
				$element->addAttribute( 'filter', 'safehtml' );
				
				if( $field->required )
				{
					$element->addAttribute( 'required', 'true' );
				}
			}
			
			$element->addAttribute( 'name', $field->field_id );
			$element->addAttribute( 'labelclass' , 'control-label' );
			$element->addAttribute( 'label', $label );
			$element->addAttribute( 'translate_label', 'false' );
			

			
			// hr bottom spacer
			$element = $root->addChild( 'field' );
			$element->addAttribute( 'type', 'spacer' );
			$element->addAttribute( 'name', 'hr_bottom_spacer_' . $field->field_id );
			$element->addAttribute( 'hr', 'true' );
			
			$jregistry->set( 'form.fields.' . FieldsandfiltersFactory::getArray()->getEmptySlotObject( $jregistry, $field->ordering ), $root );
			
			unset( $element, $elementSpacer );
		}
		
		return true;
	}
	
	/**
	 * @since       1.1.0
	public function onFieldsandfiltersBeforeSaveData( $context, $newItem, $oldItem, $isNew )
	{
		if( $context == 'com_fieldsandfilters.field' && $newItem->field_type == $this->_name )
		{
			$data = $table->get( 'values', new JObject )->get( 'data' );
			
			if( !empty( $data ) )
			{
				
			}
		}
	}
	*/
	
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
			if( !property_exists( $element->data, $field->field_id ) )
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
				$modeName 	= ( ( $v = $pluginTypesHelper->getModeName( $field->mode ) ) != 'filter' ) ? $v : 'field';
				$layoutField	= $modeName . '-default';
			}
			
			$field->params->set( 'type.field_layout', $layoutField );
			
			$this->_variables->field = $field;
			
			$template = $extensionsHelper->loadPluginTemplate( $this->_variables, $layoutField );
			$templateFields->set( $arrayHelper->getEmptySlotObject( $templateFields, $field->$ordering, false ), $template );
			
			if( $isParams )
			{
				$field = $paramsTemp;
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
