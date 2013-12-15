<?php
/**
 * @version     1.1.1
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_extension.content
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

/**
 * Extensions type content
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_extensions.content
 * @since       1.0.0
 */
class plgFieldsandfiltersExtensionsContent extends JPlugin
{
	/**
	 * @var		string	Content context.
	 * @since	1.0.0
	 */
	protected $_context = 'com_content.article';
	
	/**
	 * @var		string	Content state.
	 * @since	1.0.0
	 */
	protected $_states = array( 1, 0 , 2, -2 );
	
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.1.0
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
	 * @since       1.1.0
	 */
	public function onFieldsandfiltersPrepareExtensions( JObject $extensions )
	{
		$extension 			= new JObject;
		$extension->name		= $this->_name;
		$extension->type		= $this->_type;
		$extension->extension		= 'com_content';
		$extension->content_type_alias 	= 'com_content.article';
		
		$extensions->set( $extension->extension,  $extension );
	}
	
	/**
	 * @since       1.0.0
	 */
	public function onFieldsandfiltersPopulateState( $context, $state, &$filter_fields )
	{
		if( $context != 'com_fieldsandfilters.elements.content' )
		{
			return true;
		}
		
		JHtml::addIncludePath( JPATH_SITE . '/components/com_content/helpers/html' );
		
		// Initialise variables.
		$itemCategory = JFactory::getApplication( 'administrator' )->getUserStateFromRequest( 'com_fieldsandfilters.elements.filter.item_category', 'filter_item_category', '', 'string' );
		$state->set( 'filter.item_category', $itemCategory );
		
		$state->set( 'query.item_id', 'a.id' );
		$state->set( 'query.item_name', 'a.title' );
		$state->set( 'query.item_category', 'c.title' );
		
		$state->set( 'filters.options', array(
				'item_category' => array(
						'label' 	=> JText::_( 'JOPTION_SELECT_CATEGORY' ),
						'options'	=> JHtml::_( 'category.options', 'com_content' )
					)
			) );
		
		$state->set( 'enabled_search', true );
		
		array_push( $filter_fields,
				'item_category', 	'a.catid',	'c.title',
				'item_name',		'a.title',
				'item_id',		'a.id'
			);
	}
	
	/**
	 * @since       1.0.0
	 */
	public function onFieldsandfiltersPrepareListQuery( $context, $query, $state )
	{
		if( $context != 'com_fieldsandfilters.elements.content' )
		{
			return true;
		}
		
		$db	= JFactory::getDbo();
		
		// Join over the contnet
		$query->select( array(
				$db->quoteName( 'a.id', 'item_id' ),
				$db->quoteName( 'a.title', 'item_name' ),
				$db->quoteName( 'a.alias', 'item_alias' ),
			) );
		
		$query->join( 'RIGHT', $db->quoteName( '#__content', 'a' ).' ON ' . $db->quoteName( 'a.id' ) . ' = ' . $db->quoteName( 'e.item_id' ) );
		
		// Join over the categories.
		$query->select( $db->quoteName( 'c.title' ,'item_category' ) );
		$query->join( 'LEFT', $db->quoteName( '#__categories', 'c' ) . ' ON ' . $db->quoteName( 'c.id' ) . ' = ' . $db->quoteName( 'a.catid' ) );
		
		$itemCategory = $state->get( 'filter.item_category' );
		if( is_numeric( $itemCategory ) )
		{
			$query->where( $db->quoteName( 'c.id' ) . ' = ' . (int) $itemCategory );
		}
		
		// Filter by search in title
		$search = $state->get( 'filter.search' );
		if( !empty( $search ) )
		{
			if( stripos( $search, 'id:' ) === 0)
			{
				$query->where( $db->quoteName( 'a.id' ) . ' = ' . (int) substr( $search, 3 ) );
			} else {
				$search = $db->quote( '%' . $db->escape( $search, true ) . '%' );
				
				$query->where( '(' . $db->quoteName( 'a.title' ) . ' LIKE ' . $search . ' OR ' . $db->quoteName( 'a.alias' ) . ' LIKE ' . $search . ')' );
			}
		}
		
		return true;
	}
	
	/**
	 * @since       1.1.0
	 */
	public function onFieldsandfiltersPrepareFields( $context, $fieldsForm, $extensionsTypeID  )
	{
		// Check we have a xml element
		if( !( $fieldsForm instanceof KextensionsFormElement ) )
		{
			$this->_subject->setError( 'JERROR_NOT_A_FORM' );
			return false;
		}
		
		if( empty( $extensionsTypeID ) || !in_array( $context, array( $this->_context, 'com_fieldsandfilters.element.content' ) ) )
		{
			return true;
		}
		
		// Load Fields Helper
		$fieldsHelper = FieldsandfiltersFactory::getFields();
		
		$extensionsParams = new JObject( array(
					'module.off'		=> true,
					'plugin.value'		=> $this->params->get( 'show_static_fields' )
			) );
		
		// Load Extensions Helper
		if( FieldsandfiltersExtensionsHelper::getParams( 'show_static_fields', $extensionsParams, true ) )
		{
			$fields = $fieldsHelper->getFieldsPivot( 'field_type', $extensionsTypeID, array( 1, -1 ), 'both' );
		}
		else
		{
			$typesHelper 	= FieldsandfiltersFactory::getTypes();
			$staticMode	= (array) $typesHelper->getMode( 'static' );
			$othersMode	= (array) $typesHelper->getModes( null, array(), true, $staticMode );
			
			$fields = $fieldsHelper->getFieldsByModeIDPivot( 'field_type', $extensionsTypeID, $othersMode, array( 1, -1 ), 'both' );
		}
		
		$fieldsForm->setElements( $fields );
	}
	
	/**
	 * @since       1.0.0
	 */
	public function onFieldsandfiltersPrepareElement( $context, $item, $isNew, $state )
	{
		if( !( $context == $this->_context || $context == 'com_fieldsandfilters.element.content' ) || empty( $item->item_id ) )
		{
			return true;
		}
		
		if( $contentTable = JTable::getInstance( 'content' ) )
		{
			if( $contentTable->load( $item->item_id ) )
			{
				$item->state 		= $contentTable->state;
				$item->item_name 	= $contentTable->title;
			}
		}
		
		return true;
	}
	
	/**
	 * @param	JForm	$form	The form to be altered.
	 * @param	array	$data	The associated data for the form.
	 *
	 * @return	boolean
	 * @since       1.0.0
	 */
	public function onFieldsandfiltersPrepareForm( $form, $data )
	{
		// Check we have a xml element
		if( !( $form instanceof JForm ) )
		{
			$this->_subject->setError( 'JERROR_NOT_A_FORM' );
			return false;
		}
		
		if( $form->getName() != $this->_context )
		{
			return true;
		}
		
		// Load Extensions Helper
		$extensions = FieldsandfiltersFactory::getExtensions()->getExtensionsByName( array( 'allextensions', $this->_name ) );
		
		if( !( $extensionContent = $extensions->get( $this->_name ) ) )
		{
			return true;
		}
		
		JModelLegacy::addIncludePath( ( JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/models' ), 'FieldsandfiltersModel' );
		
		if( !( $elementModel = JModelLegacy::getInstance( 'element', 'FieldsandfiltersModel', array( 'ignore_request' => true, 'table_path' => ( JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/tables' ) ) ) ) )
		{
			return true;
		}
		
		$elementModel->setState( 'element.extension_type_id', $extensionContent->extension_type_id );
		
		// Load Extension Language
		KextensionsLanguage::load( 'com_fieldsandfilters', JPATH_ADMINISTRATOR );
		
		$fieldsForm = $elementModel->prepareFieldsForm();
		
		if( !$fieldsForm instanceof KextensionsFormElement )
		{
			return true;
		}
		
		$fieldsForm->setPath( 'attribs.fieldsandfilters' );
		
		$fieldsetXML =  new SimpleXMLElement( '<fieldset />' );
		$fieldsetXML->addAttribute( 'name', 'fieldsandfilters' );
		$fieldsetXML->addAttribute( 'label', 'COM_FIELDSANDFILTERS' );
		// $fieldsetForm->addAttribute( 'description', 'COM_FIELDSANDFILTERS_FIELDSET_DESC' );
		
		$fielsXML = $fieldsetXML->addChild( 'fields' );
		$fielsXML->addAttribute( 'name', 'fieldsandfilters' );
		
		if( !empty( $data ) )
		{
			$data = (object) $data;
			$elementModel->setState( 'element.item_id', $data->id );
			$itemFields = $elementModel->getItem()->get( 'fields', new JObject );
		}
		
		$isNew = !(boolean) $elementModel->getState( 'element.element_id', 0 );
		
		JPluginHelper::importPlugin( 'fieldsandfiltersTypes' );
		
		// Trigger the onFieldsandfiltersPrepareFormField event.
		FieldsandfiltersFactory::getDispatcher()->trigger( 'onFieldsandfiltersPrepareFormField', array( $fieldsForm, $isNew ) );
		
		if( $fieldsFormXML = $fieldsForm->getFields() )
		{
			// Load the XML Helper
			KextensionsXML::setFields( $fielsXML , $fieldsFormXML );
			
			$form->setField( $fieldsetXML, 'attribs' );
			
			// For joomla 2.5 && Key Reference
			if( !FieldsandfiltersFactory::isVersion() )
			{
				$fieldsetXML = new SimpleXMLElement( '<fieldset />' );
				$fieldsetXML->addAttribute( 'name', 'key_reference' );
				$fieldsetXML->addAttribute( 'label', 'Key Reference' );
				
				$form->setField( $fieldsetXML, 'attribs' );
			}
			
			if( $default = $fieldsForm->getData() )
			{
				$form->bind( $default );
			}
			
			if( !$isNew )
			{
				$attribs    = new JRegistry();
				$attribs->set( 'attribs.fieldsandfilters.fields', $itemFields );
				
				$form->bind( $attribs );
			}
		}
		
		return true;
	}
	
	/**
	 * @since       1.0.0
	 */
	public function onFieldsandfiltersBeforeSave( $context, $item, $isNew )
	{
		if( $context == 'com_fieldsandfilters.element.content' && ( $contentTable = JTable::getInstance( 'content' ) ) )
		{
			if( $contentTable->load( $item->item_id ) )
			{
				$item->state 		= $contentTable->state;
			}
		}
		elseif( $context == $this->_context && property_exists( $item, 'attribs' ) )
		{
			$attribs = new JRegistry;
			$attribs->loadString( $item->attribs );
			
			JRegistry::getInstance( 'fieldsandfilters' )->set( $this->_name . '.save', $attribs->get( 'fieldsandfilters.fields' ) );
			
			$attribs = $attribs->toArray();
			
			unset( $attribs['fieldsandfilters'] );
			
			$attribs = new JRegistry( $attribs );
			
			$item->attribs = (string) $attribs;
			
			unset( $attribs );
		}
		
		return true;
	}
	
	/**
	 * @since       1.1.0
	 */
	public function onFieldsandfiltersAfterSave( $context, $item, $isNew )
	{
		if( $context != $this->_context || empty( $item->id ) )
		{
			return true;
		}
		
		if( !( $itemData = JRegistry::getInstance( 'fieldsandfilters' )->get( $this->_name . '.save' ) ) )
		{
			return true;
		}
		
		// Load PluginExtensions Helper		
		if( !( $extensionContent = FieldsandfiltersFactory::getExtensions()->getExtensionsByName( $this->_name )->get( $this->_name ) ) )
		{
			return true;
		}
		
		$app = JFactory::getApplication();
		
		JModelLegacy::addIncludePath( ( JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/models' ), 'FieldsandfiltersModel' );
		
		if( !( $elementModel = JModelLegacy::getInstance( 'element', 'FieldsandfiltersModel', array( 'ignore_request' => true, 'table_path' => ( JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/tables' ) ) ) ) )
		{
			$app->enqueueMessage( JText::sprintf( 'JLIB_APPLICATION_ERROR_MODELCLASS_NOT_FOUND', ( $prefix . ucfirst( $type ) ) ), 'error' );
			return false;
		}
		
		$data = array(
			'item_id'		=> $item->id,
			'state'			=> $item->get( 'state', 0 ),
			'extension_type_id'	=> $extensionContent->extension_type_id,
			'fields'		=> $itemData
		);
		
		// Get the form.
		JForm::addFormPath( JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/models/forms' );
		JForm::addFieldPath( JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/models/fields' );
		
		// Validate the posted data.
		// Sometimes the form needs some posted data, such as for plugins and modules.
		if( !( $form = $elementModel->getForm( $data, false ) ) )
		{
			$app->enqueueMessage( $elementModel->getError(), 'error' );
			return false;
		}
		
		// Test whether the data is valid.
		$validData = $elementModel->validate( $form, $data );
		
		// Check for validation errors.
		if( $validData === false )
		{
			// Get the validation messages.
			$errors = $elementModel->getErrors();

			// Push up to three validation messages out to the user.
			for( $i = 0, $n = count( $errors ); $i < $n && $i < 3; $i++ )
			{
				if( $errors[$i] instanceof Exception )
				{
					$app->enqueueMessage( $errors[$i]->getMessage(), 'warning' );
				}
				else
				{
					$app->enqueueMessage( $errors[$i], 'warning' );
				}
			}
		}
		
		if( !$elementModel->save( $validData ) )
		{
			$app->enqueueMessage( $elementModel->getError(), 'error' );
			return false;
		}
		
		return true;
	}
	
	/**
	 * @since       1.1.0
	 */
	public function onFieldsandfiltersBeforeDelete( $context, $item )
	{
		if( $context != $this->_context || empty( $item->id ) )
		{
			return true;
		}
		
		// Load PluginExtensions Helper
		if( !( $extensionContent = FieldsandfiltersFactory::getExtensions()->getExtensionsByName( $this->_name )->get( $this->_name ) ) )
		{
			return true;
		}
		
		JModelLegacy::addIncludePath( ( JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/models' ), 'FieldsandfiltersModel' );
		
		// Load Elements Helper
		if( ( $elementID = FieldsandfiltersFactory::getElements()->getElementID( $extensionContent->extension_type_id, $item->id, $this->_states ) ) &&
		   ( $elementModel = JModelLegacy::getInstance( 'element', 'FieldsandfiltersModel', array( 'ignore_request' => true, 'table_path' => ( JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/tables' ) ) ) ) )
		{
			if( !$elementModel->delete( $elementID ) )
			{
				$item->setError( $elementModel->getError() );
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * @since       1.1.0
	 */
	public function onFieldsandfiltersChangeState( $context, $pks, $value )
	{
		if( $context != $this->_context )
		{
			return true;
		}
		
		// Load PluginExtensions Helper
		if( !( $extensionContent = FieldsandfiltersFactory::getExtensions()->getExtensionsByName( $this->_name )->get( $this->_name ) ) )
		{
			return true;
		}
		
		// Load Elements Helper
		$elementsID = FieldsandfiltersFactory::getElements()->getElementsByIDColumn( 'element_id', $extensionContent->extension_type_id, $pks, $this->_states, false );
		
		if( empty( $elementsID ) )
		{
			return true;
		}
		
		JModelLegacy::addIncludePath( ( JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/models' ), 'FieldsandfiltersModel' );
		
		if( !( $elementModel = JModelLegacy::getInstance( 'element', 'FieldsandfiltersModel', array( 'ignore_request' => true, 'table_path' => ( JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/tables' ) ) ) ) )
		{
			return true;
		}
		
		if( !$elementModel->publish( $elementsID, $value ) )
		{
			$item->setError( $elementModel->getError() );
			return false;
		}
		
		return true;
	}
	
	/**
	 * @since       1.1.0
	 */
	public function onFieldsandfiltersContentAfterTitle( $context, &$row, &$params, $page )
	{
		switch( $context )
		{
			case 'com_content.category':
				return $this->_onFieldsandfiltersContent( 'onContentAfterTitleCategory', $row, $params, $page );
			break;
			case 'com_content.article':
				return $this->_onFieldsandfiltersContent( 'onContentAfterTitleArticle', $row, $params, $page );
			break;
			case 'com_content.featured':
				return $this->_onFieldsandfiltersContent( 'onContentAfterTitleFeatured', $row, $params, $page );
			break;	
		}
		
		return;
	}
	
	/**
	 * @since       1.1.0
	 */
	public function onFieldsandfiltersContentBeforeDisplay( $context, &$row, &$params, $page )
	{
		switch( $context )
		{
			case 'com_content.category':
				return $this->_onFieldsandfiltersContent( 'onContentBeforeDisplayCategory', $row, $params, $page );
			break;
			case 'com_content.article':
				return $this->_onFieldsandfiltersContent( 'onContentBeforeDisplayArticle', $row, $params, $page );
			break;
			case 'com_content.featured':
				return $this->_onFieldsandfiltersContent( 'onContentBeforeDisplayFeatured', $row, $params, $page );
			break;	
		}
		
		return;
	}
	
	/**
	 * @since       1.1.0
	 */
	public function onFieldsandfiltersContentAfterDisplay( $context, &$row, &$params, $page )
	{
		switch( $context )
		{
			case 'com_content.category':
				return $this->_onFieldsandfiltersContent( 'onContentAfterDisplayCategory', $row, $params, $page );
			break;
			case 'com_content.article':
				return $this->_onFieldsandfiltersContent( 'onContentAfterDisplayArticle', $row, $params, $page );
			break;
			case 'com_content.featured':
				return $this->_onFieldsandfiltersContent( 'onContentAfterDisplayFeatured', $row, $params, $page );
			break;	
		}
		
		return;
	}
	
	/**
	 * @since       1.1.0
	 */
	protected function _onFieldsandfiltersContent( $location, &$row, &$params, $page )
	{
		// Load PluginExtensions Helper
		$extensionsHelper = FieldsandfiltersFactory::getExtensions();
		
		if( !( $extensionContent = $extensionsHelper->getExtensionsByName( $this->_name )->get( $this->_name ) ) )
		{
			return;
		}
		
		// Load Fields Helper
		$fieldsHelper = FieldsandfiltersFactory::getFields();
		
		$extensionsParams = new JObject( array(
					'module.off'		=> true,
					'plugin.value'		=> $this->params->get( 'use_static_fields' )
			) );
		
		// Load Extensions Helper
		if( $isStaticFields = FieldsandfiltersExtensionsHelper::getParams( 'use_static_fields', $extensionsParams, true ) )
		{
			// Load Plugin Types Helper
			$staticMode = FieldsandfiltersFactory::getTypes()->getMode( 'static' );
			if( $fieldsStatic = $fieldsHelper->getFieldsByModeIDPivot( 'location', $extensionContent->extension_type_id, $staticMode, 1, 2 )->get( $location ) )
			{
				$fieldsStatic	= is_array( $fieldsStatic ) ? $fieldsStatic : array( $fieldsStatic );
			}
			else
			{
				$isStaticFields = false;
			}
		}
		
		// Load elements Helper
		if( !( $element = FieldsandfiltersFactory::getElements()->getElementsByItemIDPivot( 'item_id', $extensionContent->extension_type_id, $row->id, $row->state, 3 )->get( $row->id ) ) && !$isStaticFields )
		{
			return;
		}
		
		if( $element )
		{
			$fieldsID = array_merge( array_keys( $element->connections->getProperties( true ) ), array_keys( $element->data->getProperties( true ) ) );
			
			if( empty( $fieldsID ) && !$isStaticFields )
			{
				return;
			}
			
			if( !( $fields = $fieldsHelper->getFieldsByIDPivot( 'location', $extensionContent->extension_type_id, $fieldsID, 1, 1 )->get( $location ) ) && !$isStaticFields )
			{
				return;
			}
		}
		else
		{
			$fields = false;
		}
		
		if( $fields && $isStaticFields )
		{
			$fields = is_array( $fields ) ? $fields : array( $fields );
			$fields = array_merge( $fields, $fieldsStatic );
		}
		else if( $fields )
		{
			$fields = is_array( $fields ) ? $fields : array( $fields );
		}
		else if( $isStaticFields )
		{
			$fields = $fieldsStatic;
		}
		
		$fields = new JObject( JArrayHelper::pivot( (array) $fields, 'field_type' ) );
		
		$templateFields = new JObject;
		
		JPluginHelper::importPlugin( 'fieldsandfiltersTypes' );
		// Trigger the onFieldsandfiltersPrepareFormField event.
		FieldsandfiltersFactory::getDispatcher()->trigger( 'getFieldsandfiltersFieldsHTML', array( $templateFields, $fields, $element ) );
		
		$templateFields = $templateFields->getProperties( true );
		
		if( empty( $templateFields ) )
		{
			return;
		}
		
		ksort( $templateFields );
		
		return implode( "\n", $templateFields );
	}
	
	/**
	 * @since       1.1.0
	 */
	public function onFieldsandfiltersPrepareFiltersHTML( $context, $fieldsID = null, $getAllextensions = true, $params = false, $ordering = 'ordering' )
	{
		if( $context != 'com_content.category' )
		{
			return;
		}
		
		$app 	= JFactory::getApplication();
		$jinput = $app->input;
		$id 	= $jinput->get( 'id', 0, 'int' );
		
		// Load PluginExtensions Helper
		$extensionsHelper = FieldsandfiltersFactory::getExtensions();
		$extensionContent = $extensionsHelper->getExtensionsByName( $this->_name )->get( $this->_name );
		
		if( !$id || !$extensionContent )
		{
			return;
		}
		
		// add model path
		$prefix = get_class( $this );
		JModelLegacy::addIncludePath( ( JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/overrides' ), ( $prefix . 'Model' ) );
		
		if( !( $model = JModelLegacy::getInstance( 'category', ( $prefix . 'Model' ), array( 'ignore_request' => false, 'table_path' => JPATH_ADMINISTRATOR . '/components/' . $jinput->get( 'option' ) . '/tables' ) ) ) )
		{
			return;
		}
		
		$itemsID = $model->getContentItemsID();
		
		if( empty( $itemsID ) )
		{
			return;
		}
		
		// Load Filters Helper
		$counts = (array) FieldsandFieldsandfiltersFilters::getFiltersValuesCount( $extensionContent->extension_type_id, $fieldsID, $itemsID );
		
		if( empty( $counts ) )
		{
			return;
		}
		
		$extensionsID = ( $getAllextensions ) ? $extensionsHelper->getExtensionsByNameColumn( 'extension_type_id', array( 'allextensions' , $this->_name ) ) : $extensionContent->extension_type_id;
		
		// Load Fields Helper
		$fieldsHelper = FieldsandfiltersFactory::getFields();
		
		if( is_null( $fieldsID ) )
		{
			// Load PluginTypes Helper
			if( $modes = FieldsandfiltersFactory::getTypes()->getMode( 'filter' ) );
			{
				// multi extensions array( $this->_name, 'allextensions' )
				// $fields = $fieldsHelper->getFieldsByModeIDPivot( 'field_type', $extensionsID, $modes, 1, true );
				// single extension $this->_name
				$fields = $fieldsHelper->getFieldsByModeIDPivot( 'field_type', $extensionsID, $modes, 1, true );
			}
		}
		else if( is_numeric( $fieldsID ) || is_array( $fieldsID ) )
		{
			// multi extensions array( $this->_name, 'allextensions' )
			// $fields = $fieldsHelper->getFieldsByIDPivot( 'field_type', $extensionContent->extension_type_id, $fieldsID, 1, true );
			// single extension $this->_name
			$fields = $fieldsHelper->getFieldsByIDPivot( 'field_type', $extensionsID, $fieldsID, 1, true );
		}
		else
		{
			return;
		}
		
		$templateFields = new JObject;
		
		JPluginHelper::importPlugin( 'fieldsandfiltersTypes' );
		// Trigger the onFieldsandfiltersPrepareFormField event.
		FieldsandfiltersFactory::getDispatcher()->trigger( 'getFieldsandfiltersFiltersHTML', array( $templateFields, $fields, $params, $ordering ) );
		
		$templateFields = $templateFields->getProperties( true );
		
		if( empty( $templateFields ) )
		{
			return;
		}
		
		ksort( $templateFields );
		
		$jregistry = JRegistry::getInstance( 'fieldsandfilters' );
		
		$request = new stdClass;
		$request->extensionID 	= (int) $extensionContent->extension_type_id;
		$request->id 		= JFactory::getApplication()->input->get( 'id', 0, 'int' );
		// $request->limitstart	= (int) $model->getState( 'list.start', 0 );
		
		$jregistry->set( 'filters.request', $request );
		$jregistry->set( 'filters.counts', $counts );
		
		if( $app->getCfg( 'sef', 0 ) )
		{
			$jregistry->set( 'filters.pagination', array( 'start' => 0 ) );
		}
		else
		{
			$jregistry->set( 'filters.pagination', array( 'limitstart' => 0 ) );
		}
		
		return implode( "\n", $templateFields );
	}
	
	/**
	 * @since       1.1.0
	 */
	public function onFieldsandfiltersRequestJSON( $context )
	{
		if( $context != 'com_fieldsandfilters.filters.content' )
		{
			return;
		}
		
		$app 		= JFactory::getApplication();
		$jinput 	= $app->input;
		$document	= JFactory::getDocument();
		$basePath	= JPATH_SITE . '/components/com_content';
		$id 		= $jinput->get( 'id', 0, 'int' );
		
		// Load PluginExtensions Helper
                $extension = FieldsandfiltersFactory::getExtensions()->getExtensionsByName( $this->_name )->get( $this->_name );
		
                if( !$id || !$extension )
                {
                        return false;
                }
		
		// Include dependancies
		JLoader::import( 'com_content.helpers.route', JPATH_SITE . '/components' );
		JLoader::import( 'com_content.helpers.query', JPATH_SITE . '/components' );
		
		// Get controller Instance
		if( !( $controller = KextensionsController::getInstance( null, 'contentController', array( 'base_path' => $basePath, 'view_path' => ( $basePath . '/views' ) ) ) ) )
		{
			return false;
		}
		
		// load view
		if( !( $view = $controller->getView( 'category', 'html', '', array( 'base_path' => $basePath, 'layout' => $jinput->get( 'layout', 'default' ) ) ) ) )
		{
			return false;
		}
		
		// For joomla 2.5 && Key Reference
		if( !FieldsandfiltersFactory::isVersion() )
		{
			$view->addTemplatePath( JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_content/category' );
		}
		
		$fieldsandfilters = $jinput->get( 'fieldsandfilters', array(), 'array' );
		
		if( !empty( $fieldsandfilters ) )
		{
			$extensionsParams = new JObject( array(
					'plugin.value'		=> $this->params->get( 'comparison_between_filters' )
			) );
			
			if( $moduleID = $jinput->get( 'module', 0, 'int' ) )
			{
				$extensionsParams->set( 'module.id', $moduleID );
			}
	
			$betweenFilters = FieldsandfiltersExtensionsHelper::getParams( 'comparison_between_filters', $extensionsParams, 'OR' );
			
			$extensionsParams->set( 'plugin.value', $this->params->get( 'comparison_between_values_filters' ) );
			$betweenValues = FieldsandfiltersExtensionsHelper::getParams( 'comparison_between_values_filters', $extensionsParams, 'OR' );
			
			$itemsID = FieldsandfiltersFilters::getItemsIDByFilters( $extension->extension_type_id, $fieldsandfilters, 1, $betweenFilters, $betweenValues );
		}
		else
		{
			$itemsID = FieldsandfiltersFilters::getSimpleItemsID( false );
		}
		
		// set new jinput values
		$jinput->set( 'option', 'com_content' );
		$jinput->set( 'view', 'category' );
		// $jinput->set( 'layout', 'blog' );
		$jinput->set( 'id', $jinput->get( 'id', 0, 'int' ) );
		
		// add model path
		$prefix = get_class( $this );
		$controller->addModelPath( ( JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/overrides' ), ( $prefix . 'Model' ) );
		
		if( !( $model = $controller->getModel( 'category', ( $prefix . 'Model' ), array( 'ignore_request' => false, 'table_path' => JPATH_ADMINISTRATOR . '/components/' . $jinput->get( 'option' ) . '/tables' ) ) ) )
		{
			return false;
		}
		
		// set module to view
		$view->setModel( $model, true );
		$view->document = $document;
		
		// add helpers
		JHtml::addIncludePath( JPATH_SITE . '/components/com_content/helpers' );
		JHtml::addIncludePath( JPATH_SITE . '/components/com_content/helpers/html' );
			
		// Load Extensions Helper && Load common and local language files.
		$option = 'com_content';
		
		KextensionsLanguage::load( $option );
		
		$emptyItemsID = $itemsID->get( 'empty', false );
		$model->setState( 'fieldsandfilters.itemsID', (array) $itemsID->get( 'itemsID' ) );
		$model->setState( 'fieldsandfilters.emptyItemsID', $emptyItemsID );
		
		if( !$emptyItemsID )
		{
			ob_start();
				
			$view->display();
			
			$body = ob_get_contents();
			
			ob_end_clean();
		}
		else
		{
			$body = JText::_( 'PLG_FAF_ES_CT_ERROR_NOT_MATCH_TO_FILTERS' );
		}
		
		$itemsID 	= $model->getState( 'fieldsandfilters.itemsID', array() );
		$fieldsID 	= $jinput->get( 'fields', array(), 'array' );
		$jregistry	= JRegistry::getInstance( 'fieldsandfilters' );
		
		if( !empty( $itemsID ) && !empty( $fieldsID ) && !$emptyItemsID  )
		{
			// Load Filters Helper
			$counts = (array) FieldsandfiltersFilters::getFiltersValuesCount( $extension->extension_type_id, $fieldsID, $itemsID );
			
			$jregistry->set( 'filters.counts', $counts );
		}
		else if( $emptyItemsID )
		{
			// [TODO] when is empty display all fields with 0 counts or display special buttons to reset filters
			$jregistry->set( 'filters.empty', $emptyItemsID );
		}
		
		$document->setBuffer( $body, array( 'type' => 'component', 'name' => 'fieldsandfilters', 'title' => null ) );
		
		if( !$emptyItemsID )
		{
			$js[] = 'jQuery(document).ready(function($) {';
			$js[] = '	$("' . $this->params->get( 'selector_pagination_filters', '.pagination' ) . '").fieldsandfilters("pagination"'
						. ( $app->getCfg( 'sef', 0 ) ? ',{pagination: "start"}' : '' )
						. ');';
			$js[] = '});';
			
			$document->addScriptDeclaration( implode( "\n", $js ) );
		}
	}
}