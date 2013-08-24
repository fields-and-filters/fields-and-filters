<?php
/**
 * @version     1.1.0
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
		$this->loadLanguage();
	}
	
	/**
	 * @param	JForm	$form	The form to be altered.
	 * @param	array	$data	The associated data for the form.
	 *
	 * @return	boolean
	 * @since	1.1.0
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
		$arrayHelper	= FieldsandfiltersFactory::getArray();
		$xmlHelper	= FieldsandfiltersFactory::getXML();
		
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
					break;
				}
			}
			
			$element = $rootJson->addChild( 'field' );
			$element->addAttribute( 'labelclass' , 'control-label' );
			
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
				
				// caption
				$element = $rootJson->addChild( 'field' );
				$element->addAttribute( 'name', 'caption' );
				$element->addAttribute( 'type', 'text' );
				$element->addAttribute( 'class', 'inputbox' );
				$element->addAttribute( 'labelclass' , 'control-label' );
				$element->addAttribute( 'label', 'PLG_FAF_TS_IE_FORM_CAPTION_LBL' );
				$element->addAttribute( 'description', 'PLG_FAF_TS_IE_FORM_CAPTION_DESC' );
				$element->addAttribute( 'filter', 'safehtml' );
				
				// alt
				$element = $rootJson->addChild( 'field' );
				$element->addAttribute( 'name', 'alt' );
				$element->addAttribute( 'type', 'text' );
				$element->addAttribute( 'class', 'inputbox' );
				$element->addAttribute( 'labelclass' , 'control-label' );
				$element->addAttribute( 'label', 'PLG_FAF_TS_IE_FORM_ALT_LBL' );
				$element->addAttribute( 'description', 'PLG_FAF_TS_IE_FORM_ALT_DESC' );
				$element->addAttribute( 'filter', 'safehtml' );
				
				if( $field->params->get( 'type.create_thumb' ) )
				{
					//src thumb
					$element = $rootJson->addChild( 'field' );
					$element->addAttribute( 'name', 'src_thumb' );
					$element->addAttribute( 'type', 'hidden' );
					$element->addAttribute( 'filter', 'safehtml' );
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
					
					// link target
					$element = $rootJson->addChild( 'field' );
					$element->addAttribute( 'name', 'target' );
					$element->addAttribute( 'type', 'list' );
					$element->addAttribute( 'class', 'inputbox' );
					$element->addAttribute( 'labelclass' , 'control-label' );
					$element->addAttribute( 'label', 'PLG_FAF_TS_IE_FORM_TARGET_LBL' );
					$element->addAttribute( 'description', 'PLG_FAF_TS_IE_FORM_TARGET_DESC' );
					
					$xmlHelper->addOptionsNode( $element, array(
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
			
			$jregistry->set( 'form.fields.' . $arrayHelper->getEmptySlotObject( $jregistry, $field->ordering ), $root );
			
			unset( $element );
		}
		
		return true;
	}
	
	/**
	 * @since       1.0.0
	 */
	public function onFieldsandfiltersBeforeSaveData( $context, $newItem, $oldItem, $isNew )
	{
		if( $context == 'com_fieldsandfilters.field' && $newItem->field_type == $this->_name && FieldsandfiltersFactory::getPluginTypes()->getModeName( $field->mode ) == 'static' )
		{
			$newItem->params = new JRegistry( $newItem->params );
			$newItem->values->set( 'data', (string) $this->_createImages( new stdClass, $newItem, $newItem->values ) );
		}
		else if( $context == 'com_fieldsandfilters.element' )
		{
			$jregistry 	= JRegistry::getInstance( 'fieldsandfilters' );
			$fieldsItem 	= $newItem->get( 'fields', new JObject );
			
			if( ( $data = $fieldsItem->get( 'data', new JObject ) ) && ( $fields = $jregistry->get( 'fields.' . $this->_name ) ) )
			{
				$fields = is_array( $fields ) ? $fields : array( $fields );
				
				while( $field = array_shift( $fields ) )
				{
					$data->set( $field->field_id, (string) $this->_createImages( $newItem, $field, $data->get( $field->field_id, new JObject ) ) );
				}
			}
		}
		
		return true;
	}
	
	protected function _createImages( $element, $field, $_data )
	{
		jimport( 'joomla.filesystem.file' );
		
		$app		= JFactory::getApplication();
		$jroot		= JPATH_ROOT . '/';
		
		// Load plgFieldsandfiltersTypesImageHelper Helper
		$pluginHeleper = FieldsandfiltersFactory::getPluginHelper( $this->_type, $this->_name );
		
		if( ( $image = $_data->get( 'image' ) ) && file_exists( JPath::clean( $jroot . $image ) ) )
		{
			$_data 			= new JRegistry( $_data->getProperties( true ) );
			
			$scaleImage 	= (int) $field->params->def( 'type.scale',  $this->params->get( 'scale', 0 ) );
			$createThumb	= (boolean) $field->params->get( 'type.create_thumb' );
			$scaleThumb 	= (int) $field->params->def( 'type.scale_thumb', $this->params->get( 'scale_thumb', 0 ) );
			
			if( $scaleImage || ( $createThumb && $scaleThumb ) )
			{
				$imageInfo 			= new JObject();
				$imageInfo->path 		= $image;
				$imageInfo->folder 		= $field->field_id;
				$imageInfo->prefixName		= property_exists( $element, 'element_id' ) ? $element->element_id : 0;
				
				$pluginHeleper->createNameImage( $imageInfo );
				
				$folder = $pluginHeleper->getCacheFolder() . '/'  . $field->field_id . '/';
				
				if( $scaleImage )
				{
					$src 		= JPath::clean(  $folder . $imageInfo->name );
					$srcOld 	= $_data->get( 'src', false );
					
					if( $src != $srcOld || !file_exists( JPath::clean( $jroot . $src ) ) )
					{
						if( $srcOld && file_exists( $srcOld = JPath::clean( $jroot . $srcOld ) ) )
						{
							JFile::delete( $srcOld );
						}
						
						$imageInfo->width 	= (int) $field->params->def( 'type.width', $this->params->get( 'width', 0 ) );
						$imageInfo->height 	= (int) $field->params->def( 'type.height', $this->params->get( 'height', 0 ) );
						$imageInfo->method 	= (int) $scaleImage;
						$imageInfo->quality	= (int) $field->params->def( 'type.quality', $this->params->get( 'quality', 75 ) );
						
						try
						{
							if( $pluginHeleper->createImage( $imageInfo ) )
							{
								$_data->set( 'src', str_replace( JPath::clean( $jroot ), '', $imageInfo->src ) );
								
								$app->enqueueMessage(  JText::sprintf( 'PLG_FAF_TS_IE_SUCCESS_CREATE_IMAGE', $field->field_name ) );
							}
							else
							{
								throw new RuntimeException( JText::sprintf( 'PLG_FAF_TS_IE_ERROR_NOT_CREATE_IMAGE', $field->field_name ) );
							}
						}
						catch( Exception $e )
						{
							$app->enqueueMessage( $e->getMessage(), 'error' );
						}
					}
				}
				
				if( $createThumb && $scaleThumb  )
				{
					$src 		= JPath::clean( $folder . 'thumbs/' . $imageInfo->name );
					$srcOld 	= $_data->get( 'src_thumb', false );
					
					if( $scaleThumb && ( $src != $srcOld || !file_exists( JPath::clean( $jroot . $src ) ) ) )
					{
						if( $srcOld && file_exists( $srcOld = JPath::clean( $jroot . $srcOld ) ) )
						{
							JFile::delete( $srcOld );
						}
						
						unset( $imageInfo->src );
						
						$imageInfo->folder 	= $imageInfo->folder . '/thumbs';
						$imageInfo->width 	= (int) $field->params->def( 'type.width_thumb', $this->params->get( 'width_thumb', 0 ) );
						$imageInfo->height 	= (int) $field->params->def( 'type.height_thumb', $this->params->get( 'height_thumb', 0 ) );
						$imageInfo->method 	= (int) $scaleThumb;
						$imageInfo->quality	= (int) $field->params->def( 'type.quality_thumb', $this->params->get( 'quality_thumb', 0 ) );
						
						try
						{
							if( $pluginHeleper->createImage( $imageInfo ) )
							{
								$_data->set( 'src_thumb', str_replace( JPath::clean( $jroot ), '', $imageInfo->src ) );
								
								$app->enqueueMessage( JText::sprintf( 'PLG_FAF_TS_IE_SUCCESS_CREATE_THUMB', $field->field_name ) );
							}
							else
							{
								throw new RuntimeException( JText::sprintf( 'PLG_FAF_TS_IE_ERROR_NOT_CREATE_THUMB', $field->field_name ) );
							}
						}
						catch( Exception $e )
						{
							$app->enqueueMessage( $e->getMessage(), 'error' );
						}
					}
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
					$app->enqueueMessage( JText::sprintf( 'PLG_FAF_TS_IE_ERROR_DELETE_IMAGE', $field->field_name, $src ), 'error' );
				}
			}
			
			if( ( $src = $_data->get( 'src_thumb' ) ) && file_exists( JPath::clean( $jroot . $src ) ) )
			{
				// delete thumb
				if( !JFile::delete( $jroot . $src ) )
				{
					$app->enqueueMessage( JText::sprintf( 'PLG_FAF_TS_IE_ERROR_DELETE_THUMB', $field->field_name, $src ), 'error' );
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
			
			// Load plgFieldsandfiltersTypesImageHelper Helper
			$pluginHeleper = FieldsandfiltersFactory::getPluginHelper( $this->_type, $this->_name );
			
			$path 		= $pluginHeleper->getCacheFolder() . '/' . $item->field_id;
			$fullname 	= JPath::clean( JPATH_ROOT . '/' . $path );
			
			if( is_dir( $fullname ) )
			{
				if( !JFolder::delete( $fullname ) )
				{
					JFactory::getApplication()->enqueueMessage( JText::sprintf( 'PLG_FAF_TS_IE_ERROR_DELETE_FOLDER_FIELD', $item->field_name, $path ) );
				}
			}
		}
		elseif( $context == 'com_fieldsandfilters.element' )
		{
			$jregistry 	= JRegistry::getInstance( 'fieldsandfilters' );
			$fieldsItem 	= $item->get( 'fields', new JObject );
			
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
					JFactory::getApplication()->enqueueMessage( JText::sprintf( 'PLG_FAF_TS_IE_ERROR_DELETE_FIELD_ELEMENT', $notDelete ) );
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
		
		// Load plgFieldsandfiltersTypesImageHelper Helper
		$pluginHeleper = FieldsandfiltersFactory::getPluginHelper( $this->_type, $this->_name );
		
		// Load Fields Site Helper
		$fieldsSiteHelper = FieldsandfiltersFactory::getFieldsSite();
		
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
			$modeName = $pluginTypesHelper->getModeName( $field->mode );
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
				$fieldsSiteHelper->preparationConetent( $field->description, null, null, null, array( $field->field_id ) );
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
						$imageInfo 		= new JObject();
						$imageInfo->path 	= $image;
						$imageInfo->folder 	= $field->field_id;
						$imageInfo->prefixName	= $element->element_id;
						$imageInfo->width 	= (int) $field->params->def( 'type.width', $this->params->get( 'width', 0 ) );
						$imageInfo->height 	= (int) $field->params->def( 'type.height', $this->params->get( 'height', 0 ) );
						$imageInfo->method 	= (int) $scaleImage;
						$imageInfo->quality	= (int) $field->params->def( 'type.quality', $this->params->get( 'quality', 75 ) );
						$imageInfo->name	= basename( $src );
						
						try
						{
							if( !$pluginHeleper->createImage( $imageInfo ) )
							{
								throw new RuntimeException( JText::sprintf( 'PLG_FAF_TS_IE_ERROR_NOT_CREATE_IMAGE', $field->field_name ) );
							}
						}
						catch( Exception $e )
						{
							JLog::add( $e->getMessage(), JLog::ERROR, 'plgFieldsandfiltersTypesImage' );
						}
					}
					
					if( $createThumb && $scaleThumb && ( $src = $data->get( 'src_thumb' ) ) && !file_exists( JPath::clean( $jroot . $src ) ) )
					{
						$imageInfo 		= new JObject();
						$imageInfo->path 	= $image;
						$imageInfo->folder 	= $field->field_id . '/thumbs';
						$imageInfo->prefixName	= $element->element_id;
						$imageInfo->width 	= (int) $field->params->def( 'type.width_thumb', $this->params->get( 'width_thumb', 0 ) );
						$imageInfo->height 	= (int) $field->params->def( 'type.height_thumb', $this->params->get( 'height_thumb', 0 ) );
						$imageInfo->method 	= (int) $scaleThumb;
						$imageInfo->quality	= (int) $field->params->def( 'type.quality_thumb', $this->params->get( 'quality_thumb', 0 ) );
						$imageInfo->name	= basename( $src );
						
						$pluginHeleper->createImage( $imageInfo );
						
						try
						{
							if( !$pluginHeleper->createImage( $imageInfo ) )
							{
								throw new RuntimeException( JText::sprintf( 'PLG_FAF_TS_IE_ERROR_NOT_CREATE_IMAGE', $field->field_name ) );
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
			
			$template = $extensionsHelper->loadPluginTemplate( $variables, $layoutField );
			$templateFields->set( $arrayHelper->getEmptySlotObject( $templateFields, $field->$ordering, false ), $template );
			
			if( $isParams )
			{
				$field = $paramsTemp;
				unset( $paramsField );
			}
		}
		
		unset( $variables );
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