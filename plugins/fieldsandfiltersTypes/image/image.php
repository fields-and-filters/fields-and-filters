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
JLoader::import( 'fieldsandfilters.factory', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/helpers' );


/**
 * Checkbox type fild
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
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.0.0
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
		
		$fields 	= is_array( $fields ) ? $fields : array( $fields );
		$staticMode 	= (array) FieldsandfiltersFactory::getTypes()->getMode( 'static' );
		
		JHtml::_( 'behavior.formvalidation' );
		$script[] = 'window.addEvent( "domready", function(){';
		$script[] = '	if( document.formvalidator ){';
		$script[] = '		document.formvalidator.setHandler( "url", function( value ){';
		$script[] = '			regex = new RegExp("^(https?|ftp|rmtp|mms):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?", "i");';
		$script[] = '			return regex.test(value);';
		$script[] = '		});';
		$script[] = '	};';
		$script[] = '});';
		
		JFactory::getDocument()->addScriptDeclaration( implode( "\n", $script ) );
		
		while( $field = array_shift( $fields ) )
		{
			$root = new JXMLElement( '<fields />' );
			$root->addAttribute( 'name', 'data' );
			
			$rootJson = $root->addChild( 'fields' );
			$rootJson->addAttribute( 'name', $field->field_id );
			
			$label = '<strong>' . $field->field_name . '</strong> {' . $field->field_id . '}';
			
			if( $field->state == -1 )
			{
				$label .= ' [' . JText::_( 'PLG_FIELDSANDFILTERS_FORM_ONLY_ADMIN' ) . ']';
			}
			
			if( !( $isStaticMode = in_array( $field->mode, $staticMode ) ) )
			{
				// name spacer
				$element = $rootJson->addChild( 'field' );
				$element->addAttribute( 'type', 'spacer' );
				$element->addAttribute( 'name', 'name_spacer_' . $field->field_id );
				$element->addAttribute( 'label', $label );
				$element->addAttribute( 'translate_label', 'false' );
				$element->addAttribute( 'class', 'text' );
				$element->addAttribute( 'fieldset', $fieldset );
			}
			
			if( !empty( $field->description ) && $field->params->get( 'base.admin_enabled_description', 0 ) )
			{
				switch( $field->params->get( 'base.admin_description_type', 'description' ) )
				{
					case 'tip':
						$element->addAttribute( 'description', $field->description );
						$element->addAttribute( 'translate_description', 'false' );
					break;
					case 'description':
					default:
						$element = $rootJson->addChild( 'field' );
						$element->addAttribute( 'type', 'spacer' );
						$element->addAttribute( 'name', 'description_spacer_' . $field->field_id );
						$element->addAttribute( 'label', $field->description );
						$element->addAttribute( 'translate_label', 'false' );
						$element->addAttribute( 'fieldset', $fieldset );
					break;
				}
			}
			
			$element = $rootJson->addChild( 'field' );
			$element->addAttribute( 'labelclass' , 'control-label' );
			$element->addAttribute( 'fieldset', $fieldset );
			
			if( $isStaticMode )
			{
				$label .= ' [' . JText::_( 'PLG_FIELDSANDFILTERS_FORM_GROUP_STATIC_TITLE' ) . ']';
				
				$element->addAttribute( 'type', 'spacer' );
				$element->addAttribute( 'description', $field->data );
				$element->addAttribute( 'name', $field->field_id );
				$element->addAttribute( 'label', $label );
				$element->addAttribute( 'translate_label', 'false' );
				$element->addAttribute( 'translate_description', 'false' );
			}
			else
			{
				//image
				$element->addAttribute( 'name', 'image' );
				$element->addAttribute( 'type', 'media' );
				$element->addAttribute( 'class', 'inputbox' );
				$element->addAttribute( 'label', 'PLG_FAF_TS_IE_FORM_IMAGE_LBL' );
				$element->addAttribute( 'description', 'PLG_FAF_TS_IE_FORM_IMAGE_DESC' );
				$element->addAttribute( 'filter', 'safehtml' );
				
				if( $field->required )
				{
					$element->addAttribute( 'required', 'true' );
				}
				
				//src
				$element = $rootJson->addChild( 'field' );
				$element->addAttribute( 'name', 'src' );
				$element->addAttribute( 'type', 'hidden' );
				$element->addAttribute( 'filter', 'safehtml' );
				$element->addAttribute( 'fieldset', $fieldset );
				
				// caption
				$element = $rootJson->addChild( 'field' );
				$element->addAttribute( 'name', 'caption' );
				$element->addAttribute( 'type', 'text' );
				$element->addAttribute( 'class', 'inputbox' );
				$element->addAttribute( 'labelclass' , 'control-label' );
				$element->addAttribute( 'label', 'PLG_FAF_TS_IE_FORM_CAPTION_LBL' );
				$element->addAttribute( 'description', 'PLG_FAF_TS_IE_FORM_CAPTION_DESC' );
				$element->addAttribute( 'filter', 'safehtml' );
				$element->addAttribute( 'fieldset', $fieldset );
				
				// alt
				$element = $rootJson->addChild( 'field' );
				$element->addAttribute( 'name', 'alt' );
				$element->addAttribute( 'type', 'text' );
				$element->addAttribute( 'class', 'inputbox' );
				$element->addAttribute( 'labelclass' , 'control-label' );
				$element->addAttribute( 'label', 'PLG_FAF_TS_IE_FORM_ALT_LBL' );
				$element->addAttribute( 'description', 'PLG_FAF_TS_IE_FORM_ALT_DESC' );
				$element->addAttribute( 'filter', 'safehtml' );
				$element->addAttribute( 'fieldset', $fieldset );
				
				if( $field->params->get( 'type.create_thumb' ) )
				{
					//src thumb
					$element = $rootJson->addChild( 'field' );
					$element->addAttribute( 'name', 'src_thumb' );
					$element->addAttribute( 'type', 'hidden' );
					$element->addAttribute( 'filter', 'safehtml' );
					$element->addAttribute( 'fieldset', $fieldset );
				}
				else
				{
					// link
					$element = $rootJson->addChild( 'field' );
					$element->addAttribute( 'name', 'link' );
					$element->addAttribute( 'type', 'text' );
					$element->addAttribute( 'class', 'validate-url' );
					$element->addAttribute( 'labelclass' , 'control-label' );
					$element->addAttribute( 'label', 'PLG_FAF_TS_IE_FORM_LINK_LBL' );
					$element->addAttribute( 'description', 'PLG_FAF_TS_IE_FORM_LINK_DESC' );
					$element->addAttribute( 'validate', 'url' );
					$element->addAttribute( 'fieldset', $fieldset );
					
					// link target
					$element = $rootJson->addChild( 'field' );
					$element->addAttribute( 'name', 'target' );
					$element->addAttribute( 'type', 'list' );
					$element->addAttribute( 'class', 'inputbox' );
					$element->addAttribute( 'labelclass' , 'control-label' );
					$element->addAttribute( 'label', 'PLG_FAF_TS_IE_FORM_TARGET_LBL' );
					$element->addAttribute( 'description', 'PLG_FAF_TS_IE_FORM_TARGET_DESC' );
					$element->addAttribute( 'fieldset', $fieldset );
					
					KextensionsXML::addOptionsNode( $element, array(
							'PLG_FAF_TS_IE_FORM_TARGET_OPTION_DEFAULT'	=> '',
							'PLG_FAF_TS_IE_FORM_TARGET_OPTION_BLANK'	=> 1,
							'PLG_FAF_TS_IE_FORM_TARGET_OPTION_POPUP'	=> 2,
							'PLG_FAF_TS_IE_FORM_TARGET_OPTION_MODAL'	=> 3,
							'PLG_FAF_TS_IE_FORM_TARGET_OPTION_PARENT'	=> 4
							
						) );
				}
			}
			
			// hr bottom spacer
			$element = $rootJson->addChild( 'field' );
			$element->addAttribute( 'type', 'spacer' );
			$element->addAttribute( 'name', 'hr_bottom_spacer_' . $field->field_id );
			$element->addAttribute( 'hr', 'true' );
			$element->addAttribute( 'fieldset', $fieldset );
			
			$fieldsForm->setField( $field->ordering, $root );
		}
		
		return true;
	}
	
	/**
	 * @since       1.0.0
	 */
	public function onFieldsandfiltersBeforeSaveData( $context, $newItem, $oldItem, $isNew )
	{
		if( $context == 'com_fieldsandfilters.field' && $newItem->field_type == $this->_name && FieldsandfiltersFactory::getTypes()->getModeName( $newItem->mode ) == 'static' )
		{
			$newItem->params = new JRegistry( $newItem->params );
			$newItem->values->set( 'data', (string) $this->createImages( new stdClass, $newItem, $newItem->values ) );
		}
		else if( $context == 'com_fieldsandfilters.element' )
		{
			// [TODO] NO idea for now
			
			$jregistry 	= JRegistry::getInstance( 'fieldsandfilters' ); // ~
			$fieldsItem 	= $newItem->get( 'fields', new JObject ); // ~
			
			if( ( $data = $fieldsItem->get( 'data', new JObject ) ) && ( $fields = $jregistry->get( 'fields.' . $this->_name ) ) )
			{
				$fields = is_array( $fields ) ? $fields : array( $fields );
				
				while( $field = array_shift( $fields ) )
				{
					$data->set( $field->field_id, (string) $this->createImages( $newItem, $field, $data->get( $field->field_id, new JObject ) ) );
				}
			}
		}
		
		return true;
	}
	
	protected function createImages( $element, $field, $_data )
	{
		jimport( 'joomla.filesystem.file' );
		
		$app		= JFactory::getApplication();
		$jroot		= JPATH_ROOT . '/';
		
		if( ( $image = $_data->get( 'image' ) ) && file_exists( JPath::clean( $jroot . $image ) ) )
		{
			$_data 			= new JRegistry( $_data->getProperties( true ) );
			
			$scaleImage 	= (int) $field->params->def( 'type.scale',  $this->params->get( 'scale', 0 ) );
			$createThumb	= (boolean) $field->params->get( 'type.create_thumb' );
			$scaleThumb 	= (int) $field->params->def( 'type.scale_thumb', $this->params->get( 'scale_thumb', 0 ) );
			
			if( $scaleImage || ( $createThumb && $scaleThumb ) )
			{
				$isCreated		= true;
				$isCreatedThumb		= true;
				
				$folder 		= FieldsandfiltersImage::getCacheFolder() . '/'  . $field->field_id . '/';
				$imageInfo		= self::prepareImageInfo( $field, $element, $image, false, $scaleImage );
				
				if( $scaleImage )
				{
					$src 		= JPath::clean(  $folder . $imageInfo->name );
					$srcOld 	= $_data->get( 'src', false );
					
					if( $src != $srcOld || !file_exists( JPath::clean( $jroot . $src ) ) )
					{
						try
						{
							if( $srcOld && file_exists( $srcOld = JPath::clean( $jroot . $srcOld ) ) )
							{
								JFile::delete( $srcOld );
							}
							
							if( FieldsandfiltersImage::createImage( $field->field_name, $imageInfo ) )
							{
								$_data->set( 'src', str_replace( JPath::clean( $jroot ), '', $imageInfo->src ) );
								
								$app->enqueueMessage(  JText::sprintf( 'COM_FIELDSANDFILTERS_SUCCESS_CREATE_IMAGE', $field->field_name ) );
							}
							else
							{
								throw new RuntimeException( JText::sprintf( 'COM_FIELDSANDFILTERS_ERROR_NOT_CREATE_IMAGE', $field->field_name ) );
							}
						}
						catch( Exception $e )
						{
							$isCreated = false;
							
							$_data->set( 'src', '' );
							$app->enqueueMessage( $e->getMessage(), 'error' );
						}
					}
				}
				
				if( $createThumb && $scaleThumb )
				{
					$src 		= JPath::clean( $folder . 'thumbs/' . $imageInfo->name );
					$srcOld 	= $_data->get( 'src_thumb', false );
					
					if( $scaleThumb && ( $src != $srcOld || !file_exists( JPath::clean( $jroot . $src ) ) ) )
					{
						$imageInfo = self::prepareImageInfo( $field, $element, $image, $imageInfo->name, $scaleThumb, 'thumb' );
						
						try
						{
							if( $srcOld && file_exists( $srcOld = JPath::clean( $jroot . $srcOld ) ) )
							{
								JFile::delete( $srcOld );
							}
							
							if( FieldsandfiltersImage::createImage( ( $field->field_name . ' Thumbs' ), $imageInfo ) )
							{
								$_data->set( 'src_thumb', str_replace( JPath::clean( $jroot ), '', $imageInfo->src ) );
								
								$app->enqueueMessage( JText::sprintf( 'COM_FIELDSANDFILTERS_SUCCESS_CREATE_IMAGE', $field->field_name . ' Thumb' ) );
							}
							else
							{
								throw new RuntimeException( JText::sprintf( 'COM_FIELDSANDFILTERS_ERROR_NOT_CREATE_IMAGE', $field->field_name . ' Thumb' ) );
							}
						}
						catch( Exception $e )
						{
							$isCreatedThumb = false;
							
							$_data->set( 'src_thumb', '' );
							$app->enqueueMessage( $e->getMessage(), 'error' );
						}
					}
				}
				
				if( !$isCreated && !( $createThumb && $isCreatedThumb ) )
				{
					$_data = null;
				}
			}
			
			unset( $imageInfo );
		}
		else
		{
			if( ( $src = $_data->get( 'src' ) ) && file_exists( JPath::clean( $jroot . $src ) ) )
			{
				// delete image
				if( !JFile::delete( $jroot . $src ) )
				{
					$app->enqueueMessage( JText::sprintf( 'PLG_FAF_TS_IE_SUCCESS_DELETE_IMAGE', $field->field_name, $src ), 'error' );
				}
			}
			
			if( ( $src = $_data->get( 'src_thumb' ) ) && file_exists( JPath::clean( $jroot . $src ) ) )
			{
				// delete thumb
				if( !JFile::delete( $jroot . $src ) )
				{
					$app->enqueueMessage( JText::sprintf( 'PLG_FAF_TS_IE_SUCCESS_DELETE_IMAGE', $field->field_name . ' Thumb', $src ), 'error' );
				}
			}
			
			$_data = null;
		}
		
		return $_data;
	}
	/**
	 * @since       1.1.0
	 */
	public function onFieldsandfiltersBeforeDeleteData( $context, $item )
	{
		if( $context == 'com_fieldsandfilters.field' && $item->field_type == $this->_name )
		{
			jimport( 'joomla.filesystem.folder' );
			
			$path 		= FieldsandfiltersImage::getCacheFolder() . '/' . $item->field_id;
			$fullname 	= JPath::clean( JPATH_ROOT . '/' . $path );
			
			if( is_dir( $fullname ) )
			{
				if( !JFolder::delete( $fullname ) )
				{
					JFactory::getApplication()->enqueueMessage( JText::sprintf( 'PLG_FAF_TS_IE_SUCCESS_DELETE_FOLDER', $item->field_name, $path ) );
				}
			}
		}
		elseif( $context == 'com_fieldsandfilters.element' )
		{
			// [TODO] no idea
			
			$jregistry 	= JRegistry::getInstance( 'fieldsandfilters' ); // ~
			$fieldsItem 	= $item->get( 'fields', new JObject ); // ~
			
			if( ( $data = $fieldsItem->get( 'data', new JObject ) ) && ( $fields = $jregistry->get( 'fields.' . $this->_name ) ) )
			{
				$jroot		= JPATH_ROOT . '/' ;
				$notDelete	= array();
				
				$fields = is_array( $fields ) ? $fields : array( $fields );
				
				jimport( 'joomla.filesystem.file' );
				
				while( $field = array_shift( $fields ) )
				{
					$_data 		= $data->get( $field->field_id, new JObject );
					
					if( ( $src = $_data->get( 'src' ) ) && file_exists( $fullname = JPath::clean( $jroot . $src ) ) )
					{
						if( !JFile::delete( $fullname ) )
						{
							array_push( $notDelete, $src );
						}
					}
					
					if( ( $src = $_data->get( 'src_thumb' ) ) && file_exists( $fullname = JPath::clean( $jroot . $src ) ) )
					{
						if( !JFile::delete( $fullname ) )
						{
							array_push( $notDelete, $src );
						}
					}
				}
				
				if( !empty( $notDelete ) )
				{
					JFactory::getApplication()->enqueueMessage( JText::sprintf( 'PLG_FAF_TS_IE_SUCCESS_DELETE_ELEMENT', $notDelete ) );
				}
			}
		}
		
		return true;
	}
	
	/**
	 * @since       1.0.0
	 */
	public function onFieldsandfiltersPrepareElementFields( $context, $item, $isNew, $state )
	{
		if( $isNew )
		{
			return true;
		}
		
		if( $context == 'com_fieldsandfilters.field' && isset( $item->field_type ) && $item->field_type == $this->_name )
		{
			if( !empty( $item->values->data ) && !is_object( $item->values->data ) )
			{
				$_data = new JRegistry( $item->values->data );
				$_data = new JObject( $_data->toObject() );
				
				$item->values = $_data;
			}
		}
		elseif( $context == 'com_fieldsandfilters.element' )
		{
			$fieldsItem = $item->get( 'fields', new JObject );
			if( ( $data = $fieldsItem->get( 'data', new JObject ) ) && ( $fields = JRegistry::getInstance( 'fieldsandfilters' )->get( 'fields.' . $this->_name ) ) )
			{
				$fields = is_array( $fields ) ? $fields : array( $fields );
				
				while( $field = array_shift( $fields ) )
				{
					$_data = $data->get( $field->field_id, '' );
					
					if( empty( $_data ) || is_object( $_data ) )
					{
						continue;
					}
					
					$_data = new JRegistry( $_data );
					$_data = new JObject( $_data->toObject() );
					
					$data->set( $field->field_id, $_data );
				}
			}
		}
		
		return true;
	}
	
	/**
	 * @since       1.1.0
	 */
	public function getFieldsandfiltersFieldsHTML( $layoutFields, $fields, $element, $params = false, $ordering = 'ordering' )
	{
		if( !( $fields = $fields->get( $this->_name ) ) )
		{
			return;
		}
		
		$fields = is_array( $fields ) ? $fields : array( $fields );
		
		// Load Plugin Types Helper
		$typesHelper = FieldsandfiltersFactory::getTypes();
		
		$variables 		= new JObject;
		$variables->type	= $this->_type;
		$variables->name	= $this->_name;
		$variables->params	= $this->params;
		$variables->element 	= $element;
		
		$isParams = ( $params && $params instanceof JRegistry );
		
		$jroot = JPATH_ROOT . '/';
		
		jimport( 'joomla.filesystem.file' );
		
		while( $field = array_shift( $fields ) )
		{
			$modeName = $typesHelper->getModeName( $field->mode );
			$isStaticMode = ( $modeName == 'static' );
			
			if( ( $isStaticMode && empty( $field->data ) ) || ( $modeName == 'field' && ( !isset( $element->data ) || !property_exists( $element->data, $field->field_id ) ) ) )
			{
				continue;
			}
			
			$dataElement = ( $isStaticMode ) ? $field->data :  $element->data->get( $field->field_id );
			
			if( is_string( $dataElement ) )
			{
				if( $isStaticMode )
				{
					$field->data = new JRegistry( $dataElement );
				}
				else
				{
					$element->data->set( $field->field_id, new JRegistry( $dataElement ) );
				}
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
			}
			
			// create new image if not exists		
			$scaleImage 	= (int) $field->params->def( 'type.scale',  $this->params->get( 'scale', 0 ) );
			$createThumb	= (boolean) $field->params->get( 'type.create_thumb' );
			$scaleThumb 	= (int) $field->params->def( 'type.scale_thumb', $this->params->get( 'scale_thumb', 0 ) );
			
			if( $scaleImage || ( $createThumb && $scaleThumb ) )
			{
				$data = $isStaticMode ? $field->data : $element->data->get( $field->field_id, new JRegistry );
				
				if( ( $image = $data->get( 'image' ) ) && file_exists( JPath::clean( $jroot . $image ) ) )
				{
					if( $scaleImage && ( $src = $data->get( 'src' ) ) && !file_exists( JPath::clean( $jroot . $src ) ) )
					{
						$imageInfo = self::prepareImageInfo( $field, $element, $image, basename( $src ), $scaleImage );
						
						try
						{
							if( !FieldsandfiltersImage::createImage( $field->field_name, $imageInfo ) )
							{
								throw new RuntimeException( JText::sprintf( 'COM_FIELDSANDFILTERS_ERROR_NOT_CREATE_IMAGE', $field->field_name ) );
							}
						}
						catch( Exception $e )
						{
							JLog::add( $e->getMessage(), JLog::ERROR, 'plgFieldsandfiltersTypesImage' );
						}
					}
					
					if( $createThumb && $scaleThumb && ( $src = $data->get( 'src_thumb' ) ) && !file_exists( JPath::clean( $jroot . $src ) ) )
					{
						$imageInfo = self::prepareImageInfo( $field, $element, $image, basename( $src ), $scaleThumb, 'thumb' );
						
						try
						{
							if( !FieldsandfiltersImage::createImage( ( $field->field_name . ' Thumbs' ), $imageInfo ) )
							{
								throw new RuntimeException( JText::sprintf( 'COM_FIELDSANDFILTERS_ERROR_NOT_CREATE_IMAGE', $field->field_name . ' Thumb' ) );
							}
						}
						catch( Exception $e )
						{
							JLog::add( $e->getMessage(), JLog::ERROR, 'plgFieldsandfiltersTypesImage' );
						}
					}
					
				}
				
				unset( $data, $imageInfo );
			}
			
			unset( $fieldTypeParams );
			
			$layoutField = $field->params->get( 'type.field_layout' );
			
			if( !$layoutField )
			{
				$layoutField	= $modeName . '-default';
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
		
		unset( $variables, $imageInfo );
	}
	
	protected static function prepareImageInfo( $field, $element, $image, $name = false, $method = 1, $suffix = false )
	{
		$paramSuffix = $suffix ? '_' . $suffix : '';
		
		$info 			= new JObject();
		$info->path 		= $image;
		$info->folder 		= $field->field_id . ( $suffix ? '/' . $suffix . 's' : '' );
		$info->prefixName	= $element->element_id;
		$info->width 		= (int) $field->params->def( 'type.width' . $paramSuffix, $this->params->get( 'width' . $paramSuffix, 0 ) );
		$info->height 		= (int) $field->params->def( 'type.height' . $paramSuffix, $this->params->get( 'height' . $paramSuffix, 0 ) );
		$info->method 		= (int) $method;
		$info->quality		= (int) $field->params->def( 'type.quality' . $paramSuffix, $this->params->get( 'quality' . $paramSuffix, 0 ) );
		
		if( $name && !empty( $name ) )
		{
			$info->name = FieldsandfiltersImage::createNameImage( $imageInfo );
		}
		
		return $info;
	}
}