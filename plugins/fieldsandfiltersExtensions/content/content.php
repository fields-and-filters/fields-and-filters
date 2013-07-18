<?php
/**
 * @version     1.0.0
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_extension.content
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

// Load the Fieldsandfilters Helper
JLoader::import( 'fieldsandfilters.factory', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/helpers' );

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
		$this->loadLanguage();
	}
	
	/**
	 * @since       1.1.0
	 */
	public function onFieldsandfiltersPreparePluginExtensionsHelper( $context, $data = null )
	{
		if( 'pluginextensions.options' )
		{
			$data->set( 'com_content', $this->_name );
			return;
		}
		
		return;
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
		
		$categoryOptions =  JHtml::_( 'category.options', 'com_content' );
		array_unshift( $categoryOptions, JHtml::_( 'select.option', '*', JText::_( 'JOPTION_SELECT_CATEGORY' ) ) );
		
		$state->set( 'filters.options', array( 'item_category' => $categoryOptions ) );
		
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
	public function onFieldsandfiltersPrepareFields( $context, $extensionsTypeID )
	{
		if( empty( $extensionsTypeID ) || !in_array( $context, array( $this->_context, 'com_fieldsandfilters.element.content' ) ) )
		{
			return true;
		}
		
		// Load Fields Helper
		$fieldsHelper = FieldsandfiltersFactory::getFields();
		
		if( $this->params->get( 'show_static_fields', 1 ) && JComponentHelper::getParams( 'com_fieldsandfilters' )->get( 'show_static_fields', 1 ) )
		{
			$fields = $fieldsHelper->getFieldsPivot( 'field_type', $extensionsTypeID, array( 1, -1 ), 'both' );
		}
		else
		{
			$pluginTypesHelper 	= FieldsandfiltersFactory::getPluginTypes();
			$staticMode		= (array) $pluginTypesHelper->getMode( 'static' );
			$othersMode		= (array) $pluginTypesHelper->getModes( null, array(), true, $staticMode );
			
			$fields = $fieldsHelper->getFieldsByModeIDPivot( 'field_type', $extensionsTypeID, $othersMode, array( 1, -1 ), 'both' );
		}
		
		JRegistry::getInstance( 'fieldsandfilters' )->set( 'fields', $fields );
	}
	
	/**
	 * @since       1.0.0
	 */
	public function onFieldsandfiltersPrepareItem( $context, $item, $isNew, $state )
	{
		if( !in_array( $context, array( $this->_context, 'com_fieldsandfilters.element.content' ) ) || empty( $item->item_id ) )
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
		
		$formName = $form->getName();
		
		if( $formName != $this->_context )
		{
			return true;
		}
		
		// Load PluginExtensions Helper
		$extensions = FieldsandfiltersFactory::getPluginExtensions()->getExtensionsByName( array( 'allextensions', $this->_name ) );
		
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
		
		// Load Extensions Helper
		FieldsandfiltersFactory::getExtensions()->loadLanguage( 'com_fieldsandfilters', JPATH_ADMINISTRATOR );
		
		$jregistry = JRegistry::getInstance( 'fieldsandfilters' );
		
		if( !( $elementModel->prepareFields() && ( $fields = $jregistry->get( 'fields' ) ) ) )
		{
			return true;
		}
		
		if( !empty( $data ) )
		{
			$data = is_object( $data ) ? $data : new JObject( $data );
			$elementModel->setState( 'element.item_id', $data->id );
			$registry = new JRegistry( $data->attribs  );
			$registry->set( 'fieldsandfilters.fields', $elementModel->getItem()->get( 'fields', new JObject ) );
			$data->attribs = $registry->toArray();
			unset( $registry );
		}
		
		$elementsForm = new JXMLElement( '<fields />' );
		$elementsForm->addAttribute( 'name', 'attribs' );
		
		$fafForm = $elementsForm->addChild( 'fields' );
		$fafForm->addAttribute( 'name', 'fieldsandfilters' );
		
		$fields = $fafForm->addChild( 'fields' );
		$fields->addAttribute( 'name', 'fields' );
		
		$fieldsetForm = $fields->addChild( 'fieldset' );
		$fieldsetForm->addAttribute( 'name', 'fields' );
		$fieldsetForm->addAttribute( 'label', 'COM_FIELDSANDFILTERS' );
		// $fieldsetForm->addAttribute( 'description', 'COM_MENUS_ITEM_ASSOCIATIONS_FIELDSET_DESC' );
		
		JPluginHelper::importPlugin( 'fieldsandfiltersTypes' );
		
		// Trigger the onFieldsandfiltersPrepareFormField event.
		FieldsandfiltersFactory::getDispatcher()->trigger( 'onFieldsandfiltersPrepareFormField', array( !(boolean) $elementModel->getState( 'element.element_id', 0 ) ) );
		
		if( $fieldsForm = $jregistry->get( 'form.fields' ) )
		{
			$fieldsForm = get_object_vars( $fieldsForm );
			
			ksort( $fieldsForm );
			
			// Load the XML Helper
			FieldsandfiltersFactory::getXML()->setFields( $fieldsetForm , $fieldsForm );
			
			unset( $fieldsForm );
			
			// For joomla 2.5 && Key Reference
			if( !FieldsandfiltersFactory::isVersion() )
			{
				$fieldsetJ25 = $fields->addChild( 'fieldset' );
				$fieldsetJ25->addAttribute( 'name', 'key_reference' );
				$fieldsetJ25->addAttribute( 'label', 'Key Reference' );	
			}
			$form->setFields( $elementsForm, 'attribs' );
			
			if( $defaultForm = $jregistry->get( 'form.default' ) )
			{
				foreach( $defaultForm AS $fieldID => $default )
				{
					if( $defaultName = $default->get( 'name' ) )
					{
						$form->setValue( $fieldID, 'attribs.fieldsandfilters.fields.' . $defaultName, $default->get( 'default' ) );
					}
					else
					{
						$form->setValue( $fieldID, 'attribs.fieldsandfilters.fields', $default->get( 'default' ) );
					}
				}
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
		if( !( $extensionContent = FieldsandfiltersFactory::getPluginExtensions()->getExtensionsByName( $this->_name )->get( $this->_name ) ) )
		{
			return true;
		}
		
		JModelLegacy::addIncludePath( ( JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/models' ), 'FieldsandfiltersModel' );
		
		if( !( $elementModel = JModelLegacy::getInstance( 'element', 'FieldsandfiltersModel', array( 'ignore_request' => true, 'table_path' => ( JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/tables' ) ) ) ) )
		{
			return true;
		}
		
		$data = new JRegistry();
		$data->set( 'item_id', 			$item->id );
		$data->set( 'state', 			$item->get( 'state', 0 ) );
		$data->set( 'extension_type_id', 	$extensionContent->extension_type_id );
		$data->set( 'fields', 			$itemData );
		$data = $data->toArray();
		
		if( !$elementModel->save( $data ) )
		{
			$item->setError( $elementModel->getError() );
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
		if( !( $extensionContent = FieldsandfiltersFactory::getPluginExtensions()->getExtensionsByName( $this->_name )->get( $this->_name ) ) )
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
		if( !( $extensionContent = FieldsandfiltersFactory::getPluginExtensions()->getExtensionsByName( $this->_name )->get( $this->_name ) ) )
		{
			return true;
		}
		
		// Load Elements Helper
		$elementsID = FieldsandfiltersFactory::getElements()->getElementsID( $extensionContent->extension_type_id, $pks, $this->_states, false );
		
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
		$pluginExtensionsHelper = FieldsandfiltersFactory::getPluginExtensions();
		
		if( !( $extensionContent = $pluginExtensionsHelper->getExtensionsByName( $this->_name )->get( $this->_name ) ) )
		{
			return;
		}
		
		// Load Fields Helper
		$fieldsHelper = FieldsandfiltersFactory::getFields();
		
		if( $isStaticFields = $this->params->get( 'use_static_fields', 1 ) )
		{
			// Load Plugin Types Helper
			$staticMode = FieldsandfiltersFactory::getPluginTypes()->getMode( 'static' );
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
	public function onFieldsandfiltersPrepareFiltersHTML( $context, $fieldsID = null, $params = false, $ordering = 'ordering' )
	{
		if( $context != 'com_content.category' )
		{
			return;
		}
		
		$jinput = JFactory::getApplication()->input;
		$id 	= $jinput->get( 'id', 0, 'int' );
		
		// Load PluginExtensions Helper
		$pluginExtensionsHelper = FieldsandfiltersFactory::getPluginExtensions();
		$extensionContent 	= $pluginExtensionsHelper->getExtensionsByName( $this->_name )->get( $this->_name );
		
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
		$counts = (array) FieldsandfiltersFactory::getFiltersSite()->getFiltersValuesCount( $extensionContent->extension_type_id, $fieldsID, $itemsID );
		
		if( empty( $counts ) )
		{
			return;
		}
		
		$extensionsID = $pluginExtensionsHelper->getExtensionsByNameColumn( 'extension_type_id', array( 'allextensions' , $this->_name ) );
		
		// Load Fields Helper
		$fieldsHelper = FieldsandfiltersFactory::getFields();
		
		if( is_null( $fieldsID ) )
		{
			// Load PluginTypes Helper
			if( $modes = FieldsandfiltersFactory::getPluginTypes()->getMode( 'values' ) );
			{
				// multi extensions array( $this->_name, 'allextensions' )
				// $fields = $fieldsHelper->getFieldsByModeIDPivot( 'field_type', $extensionsID, $modes, 1, true );
				// single extension $this->_name
				$fields = $fieldsHelper->getFieldsByModeIDPivot( 'field_type', $extensionContent->extension_type_id, $modes, 1, true );
			}
		}
		else if( is_numeric( $fieldsID ) || is_array( $fieldsID ) )
		{
			// multi extensions array( $this->_name, 'allextensions' )
			// $fields = $fieldsHelper->getFieldsByIDPivot( 'field_type', $extensionContent->extension_type_id, $fieldsID, 1, true );
			// single extension $this->_name
			$fields = $fieldsHelper->getFieldsByIDPivot( 'field_type', $extensionContent->extension_type_id, $fieldsID, 1, true );
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
		$jregistry->set( 'filters.pagination.limitstart', 0 );
		
		if( FieldsandfiltersFactory::isVersion() )
		{
			$jregistry->set( 'filters.pagination.start', 0 );
		}
		else
		{
			// For joomla 2.5 && Key Reference
			$jregistry->set( 'filters.pagination.limitstart', 0 );
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
                $extension = FieldsandfiltersFactory::getPluginExtensions()->getExtensionsByName( $this->_name )->get( $this->_name );
		
                if( !$id || !$extension )
                {
                        return false;
                }
		
		// Include dependancies
		JLoader::import( 'com_content.helpers.route', JPATH_SITE . '/components' );
		JLoader::import( 'com_content.helpers.query', JPATH_SITE . '/components' );
		
		// Load Extensions Helper
		if( !( $controller = FieldsandfiltersFactory::getExtensions()->getControllerInstance( null, 'contentController', array( 'base_path' => $basePath, 'view_path' => ( $basePath . '/views' ) ) ) ) )
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
		
		// Load FiltersSite Helper
		$itemsID = !empty( $fieldsandfilters ) ? FieldsandfiltersFactory::getFiltersSite()->getItemsIDByFilters( $extension->extension_type_id, $fieldsandfilters ) : array();
		
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
		
		FieldsandfiltersFactory::getExtensions()->loadLanguage( $option, JPATH_COMPONENT );
		
		$model->setState( 'fieldsandfilters.itemsID', $itemsID );
		
		ob_start();
			
		$view->display();
		
		$body = ob_get_contents();
		
		ob_end_clean();

		$itemsID 	= $model->getState( 'fieldsandfilters.itemsID', array() );
		$fieldsID 	= $jinput->get( 'fields', array(), 'array' );
		
		if( !empty( $itemsID ) && !empty( $fieldsID ) )
		{
			// Load Filters Helper
			$counts = (array) FieldsandfiltersFactory::getFiltersSite()->getFiltersValuesCount( $extension->extension_type_id, $fieldsID, $itemsID );
			
			JRegistry::getInstance( 'fieldsandfilters' )->set( 'filters.counts', $counts );
		}
		
		$document->setBuffer( $body, array( 'type' => 'component', 'name' => 'fieldsandfilters', 'title' => null ) );
		
		$js[] = 'jQuery(document).ready(function($) {';
		$js[] = '	$(".pagination").fieldsandfilters("pagination"'
					. ( !FieldsandfiltersFactory::isVersion() ? ',{pagination: "limitstart"}' : '' )
					. ');';
		$js[] = '});';
		
		$document->addScriptDeclaration( implode( "\n", $js ) );
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