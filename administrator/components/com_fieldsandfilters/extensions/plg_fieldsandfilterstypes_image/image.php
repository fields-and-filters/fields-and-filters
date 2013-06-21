<?php
/**
 * @version     1.0.0
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_field_type.image
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

jimport('joomla.utilities.utility');

/**
 * Checkbox type fild
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_types.image
 * @since       1.0.0
 */
class plgFieldsandfiltersTypesImage extends JPlugin
{
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
	 * @since	1.0.0
	 */
	public function onFieldsandfiltersPrepareFormField( $isNew = false )
	{
		$jregistry = JRegistry::getInstance( 'fieldsandfilters' );
		
		if( !( $fields = $jregistry->get( 'fields.' . $this->_name ) ) )
		{
			return true;
		}
		
		// Load Array Helper
		JLoader::import( 'helpers.fieldsandfilters.arrayhelper', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
		
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
		
		$fields = is_array( $fields ) ? $fields : array( $fields );
		while( $field = array_shift( $fields ) )
		{
			$root = new JXMLElement( '<fields />' );
			$root->addAttribute( 'name', 'data' );
			
			$rootJson = $root->addChild( 'fields' );
			$rootJson->addAttribute( 'name', $field->field_id );
			
			// name spacer
			$element = $rootJson->addChild( 'field' );
			$element->addAttribute( 'type', 'spacer' );
			$element->addAttribute( 'name', 'name_spacer_' . $field->field_id );
			$element->addAttribute( 'label', ( $field->state == -1 ? $field->field_name . ' [' . JText::_( 'PLG_FAF_TS_IE_FORM_ONLY_ADMIN' ) . ']' : $field->field_name ) );
			$element->addAttribute( 'translate_label', 'false' );
			$element->addAttribute( 'class', 'text' );
			
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
			
			//image
			$element = $rootJson->addChild( 'field' );
			$element->addAttribute( 'name', 'image' );
			$element->addAttribute( 'type', 'media' );
			$element->addAttribute( 'class', 'inputbox' );
			$element->addAttribute( 'labelclass' , 'control-label' );
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
				// Load XML Helper
				JLoader::import( 'helpers.fieldsandfilters.xmlhelper', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
				
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
				
				FieldsandfiltersXMLHelper::addOptionsNode( $element, array(
						'PLG_FAF_TS_IE_FORM_TARGET_OPTION_DEFAULT'	=> '',
						'PLG_FAF_TS_IE_FORM_TARGET_OPTION_BLANK'	=> 1,
						'PLG_FAF_TS_IE_FORM_TARGET_OPTION_POPUP'	=> 2,
						'PLG_FAF_TS_IE_FORM_TARGET_OPTION_MODAL'	=> 3,
						'PLG_FAF_TS_IE_FORM_TARGET_OPTION_PARENT'	=> 4
						
					) );
			}
			
			// hr bottom spacer
			$element = $rootJson->addChild( 'field' );
			$element->addAttribute( 'type', 'spacer' );
			$element->addAttribute( 'name', 'hr_bottom_spacer_' . $field->field_id );
			$element->addAttribute( 'hr', 'true' );
			
			$jregistry->set( 'form.fields.' . FieldsandfiltersArrayHelper::getEmptySlotObject( $jregistry, $field->ordering ), $root );
			
			unset( $element );
		}
		
		return true;
	}
	
	/**
	 * @since   1.0.0
	 */
	public function onFieldsandfiltersBeforeSaveData( $context, $newItem, $oldItem, $isNew )
	{
		if( $context == 'com_fieldsandfilters.element' )
		{
			jimport( 'joomla.filesystem.file' );
			
			$app		= JFactory::getApplication();
			$jregistry 	= JRegistry::getInstance( 'fieldsandfilters' );
			$fieldsItem 	= $newItem->get( 'fields', new JObject );
			
			if( ( $data = $fieldsItem->get( 'data', new JObject ) ) && ( $fields = $jregistry->get( 'fields.' . $this->_name ) ) )
			{
				$jroot		= JPATH_ROOT . '/';
				$fieldsItemOld	= $oldItem->get( 'fields', new JObject );
				$dataOld	= $fieldsItem->get( 'data', new JObject );
				
				// Load plgFieldsandfiltersTypesImageHelper Helper
				JLoader::import( 'fieldsandfiltersTypes.image.helper', JPATH_PLUGINS );
				
				$fields = is_array( $fields ) ? $fields : array( $fields );
				
				while( $field = array_shift( $fields ) )
				{
					$_data 		= $data->get( $field->field_id, new JObject );
					$_dataOld 	= $data->get( $field->field_id, new JObject );
					
					if( ( $image = $_data->get( 'image' ) ) && file_exists( JPath::clean( $jroot . $image ) ) )
					{
						$_data 			= new JRegistry( $_data->getProperties( true ) );
						$_dataOld 		= new JRegistry( $_dataOld->getProperties( true ) );
						
						$scaleImage 	= (int) $field->params->def( 'type.scale',  $this->params->get( 'scale', 0 ) );
						$createThumb	= (boolean) $field->params->get( 'type.create_thumb' );
						$scaleThumb 	= (int) $field->params->def( 'type.scale_thumb', $this->params->get( 'scale_thumb', 0 ) );
						
						if( $scaleImage || ( $createThumb && $scaleThumb ) )
						{
							$imageInfo 			= new JObject();
							$imageInfo->path 		= $image;
							$imageInfo->folder 		= $field->field_id;
							$imageInfo->prefixName		= $newItem->element_id;
							
							plgFieldsandfiltersTypesImageHelper::createNameImage( $imageInfo );
							
							$folder = plgFieldsandfiltersTypesImageHelper::getCacheFolder() . '/' . $field->field_id . '/';
							
							if( $scaleImage )
							{
								$src 		= JPath::clean(  $folder . $imageInfo->name );
								$srcOld 	= $_dataOld->get( 'src', false );
								
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
										if( plgFieldsandfiltersTypesImageHelper::createImage( $imageInfo ) )
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
								$srcOld 	= $_dataOld->get( 'src_thumb', false );
								
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
										if( plgFieldsandfiltersTypesImageHelper::createImage( $imageInfo ) )
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
					
					$data->set( $field->field_id, (string) $_data );
					
					unset( $_data, $_dataOld );
				}
			}
		}
		
		return true;
	}
	
	public function onFieldsandfiltersBeforeDeleteData( $context, $item )
	{
		if( $context == 'com_fieldsandfilters.field' && $item->field_type == $this->_name )
		{
			jimport( 'joomla.filesystem.folder' );
			
			// Load plgFieldsandfiltersTypesImageHelper Helper
			JLoader::import( 'fieldsandfiltersTypes.image.helper', JPATH_PLUGINS );
			
			$path 		= plgFieldsandfiltersTypesImageHelper::getCacheFolder() . '/' . $item->field_id;
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
	
	public function onFieldsandfiltersPrepareItem( $context, $item, $isNew, $state )
	{
		if( $isNew )
		{
			return true;
		}
		
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
		
		return true;
	}
	
	public function getFieldsandfiltersFieldsHTML( $fields, $element, $templateFields )
	{
		if( !( $fields = $fields->get( $this->_name ) ) )
		{
			return;
		}
		
		$fields = is_array( $fields ) ? $fields : array( $fields );
		
		// Load Extensions Helper
		JLoader::import( 'helpers.fieldsandfilters.extensionshelper', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
		
		// Load plgFieldsandfiltersTypesImageHelper Helper
		JLoader::import( 'fieldsandfiltersTypes.image.helper', JPATH_PLUGINS );
		
		// Load Array Helper
		JLoader::import( 'helpers.fieldsandfilters.arrayhelper', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );
		
		if( is_null( $this->_variables ) )
		{
			$this->_variables = new JObject( array( 'type' => $this->_type, 'name' => $this->_name, 'params' => $this->params ) );
		}
		
		$this->_variables->element = $element;
		
		$jroot = JPATH_ROOT . '/';
		
		jimport( 'joomla.filesystem.file' );
		
		while( $field = array_shift( $fields ) )
		{
			if( !property_exists( $element->data, $field->field_id ) )
			{
				continue;
			}
			
			$dataElement = $element->data->get( $field->field_id );
			
			if( is_string( $dataElement ) )
			{
				$element->data->set( $field->field_id, new JRegistry( $dataElement ) );
			}
			
			// create new image if not exists		
			$scaleImage 	= (int) $field->params->def( 'type.scale',  $this->params->get( 'scale', 0 ) );
			$createThumb	= (boolean) $field->params->get( 'type.create_thumb' );
			$scaleThumb 	= (int) $field->params->def( 'type.scale_thumb', $this->params->get( 'scale_thumb', 0 ) );
			
			if( $scaleImage || ( $createThumb && $scaleThumb ) )
			{
				$data = $element->data->get( $field->field_id, new JRegistry );
				
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
							if( !plgFieldsandfiltersTypesImageHelper::createImage( $imageInfo ) )
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
						
						plgFieldsandfiltersTypesImageHelper::createImage( $imageInfo );
						
						try
						{
							if( !plgFieldsandfiltersTypesImageHelper::createImage( $imageInfo ) )
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
			
			$this->_variables->field = $field;
			
			$templateFields->set( FieldsandfiltersArrayHelper::getEmptySlotObject( $templateFields, $field->ordering, false ), FieldsandfiltersExtensionsHelper::loadPluginTemplate( $this->_variables ) );
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
	 * @since   11.1
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