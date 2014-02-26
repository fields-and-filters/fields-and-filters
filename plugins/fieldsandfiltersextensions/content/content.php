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
		$extension->option		= 'com_content';
		$extension->content_type_alias 	= 'com_content.article';

		$extensions->set( $extension->option,  $extension );
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

		$state->set('list.view.extension.dir', __DIR__ . '/views/elements');
		$state->set('list.query.ordering', 'a.id');
		$state->set('list.query.item_id', 'a.id');
		$state->set('list.query.item_state', 'a.state');
		$state->set('list.query.item_name', 'a.title');

		array_push( $filter_fields,
			'item_id',		'a.id',
			'item_name',		'a.title',
			'item_state',		'a.state',
			'item_category', 	'a.catid',
			'item_category_name', 	'c.title'
		);

		if( FieldsandfiltersFactory::isVersion('<', 3.2) )
		{
			JHtml::addIncludePath( JPATH_SITE . '/components/com_content/helpers/html' );

			$state->set( 'filters.options', array(
				'item_category' => array(
					'label' 	=> JText::_( 'JOPTION_SELECT_CATEGORY' ),
					'options'	=> JHtml::_( 'category.options', 'com_content' )
				)
			) );
		}
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
			$db->quoteName( 'a.state', 'item_state' ),
			$db->quoteName( 'a.catid', 'item_category' ),
			$db->quoteName( 'c.title' ,'item_category_name' )
		) )
			->join( 'RIGHT', $db->quoteName( '#__content', 'a' ).' ON ' . $db->quoteName( 'a.id' ) . ' = ' . $db->quoteName( 'e.item_id' ) )
			->join( 'LEFT', $db->quoteName( '#__categories', 'c' ) . ' ON ' . $db->quoteName( 'c.id' ) . ' = ' . $db->quoteName( 'a.catid' ) );

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
	public function onFieldsandfiltersPrepareForm(JForm $form, $data )
	{
		$context = $form->getName();

		if( !($context == $this->_context || $context == 'com_fieldsandfilters.elements.filter' ))
		{
			return true;
		}

		$app = JFactory::getApplication();

		if( $context == 'com_fieldsandfilters.elements.filter' )
		{
			$form->addFieldPath(JPATH_ADMINISTRATOR . '/components/com_categories/models/fields');

			$addform = new SimpleXMLElement('<form />');
			$fields = $addform->addChild('fields');
			$fields->addAttribute('name', 'filter');

			$field = $fields->addChild('field');
			$field->addAttribute('name', 'item_category');
			$field->addAttribute('type', 'category');
			$field->addAttribute('label', 'JOPTION_FILTER_CATEGORY');
			$field->addAttribute('description', 'JOPTION_FILTER_CATEGORY_DESC');
			$field->addAttribute('extension', 'com_content');
			$field->addAttribute('onchange', 'this.form.submit();');

			KextensionsXML::addOptionsNode( $field, array(
				'JOPTION_SELECT_CATEGORY' => '',
			) );

			$form->load($addform, false);
		}
		elseif ($context == $this->_context)
		{

			if (($app->isSite() && !$this->params->get('frontend_edit', false)) || !( $extension = FieldsandfiltersFactory::getExtensions()->getExtensionsByName( $this->_name )->get( $this->_name )))
			{
				return true;
			}

			JModelLegacy::addIncludePath( ( JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/models' ), 'FieldsandfiltersModel' );

			if( !( $elementModel = JModelLegacy::getInstance( 'element', 'FieldsandfiltersModel', array( 'ignore_request' => true, 'table_path' => ( JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/tables' ) ) ) ) )
			{
				return true;
			}

			// Load Extension Language
			KextensionsLanguage::load( 'com_fieldsandfilters', JPATH_ADMINISTRATOR );

			$fieldsForm = new KextensionsForm( $this->_context . '.' . $this->_name );
			$fieldsData = FieldsandfiltersFieldsHelper::getFieldsByTypeIDColumnFieldType($extension->content_type_id);

			$formPath = $app->isAdmin() ? 'attribs.fieldsandfilters' : 'fieldsandfilters';
			$fieldsForm->setPath( $formPath );

			$fieldsetXML =  new SimpleXMLElement( '<fieldset />' );
			$fieldsetXML->addAttribute( 'name', 'fieldsandfilters' );
			$fieldsetXML->addAttribute( 'label', 'COM_FIELDSANDFILTERS' );
			// $fieldsetXML->addAttribute( 'description', 'COM_FIELDSANDFILTERS_FIELDSET_DESC' );

			$fielsXML = $fieldsetXML->addChild( 'fields' );
			$fielsXML->addAttribute( 'name', 'fieldsandfilters' );

			$fieldXML = $fielsXML->addChild('field');
			$fieldXML->addAttribute('name', '_fieldsandfilters');
			$fieldXML->addAttribute('type', 'hidden');
			$fieldXML->addAttribute( 'fieldset', 'fieldsandfilters');

			if( !empty( $data ) )
			{
				$data = (object) $data;
				$elementModel->setState( $elementModel->getName() . '.item_id', $data->id );
				$elementModel->setState( $elementModel->getName() . '.content_type_id', $extension->content_type_id );
				$elementItem = $elementModel->getItem();
			}

			$isNew = empty($elementItem->id);

			JPluginHelper::importPlugin( 'fieldsandfilterstypes' );

			// Trigger the onFieldsandfiltersPrepareFormField event.
			$app->triggerEvent( 'onFieldsandfiltersPrepareFormField', array( $fieldsForm, $fieldsData, $isNew ) );

			if( $fieldsFormXML = $fieldsForm->getFormFields() )
			{
				// Load the XML Helper
				KextensionsXML::setFields( $fielsXML , $fieldsFormXML );

				$form->setField( $fieldsetXML, $app->isAdmin() ? 'attribs' : null );

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
					$data    = new JRegistry();
					$data->set( $formPath, $elementItem->get( 'fields', new JObject ) );

					$form->bind( $data );
				}
			}

			return true;
		}
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
				$item->state = $contentTable->state;
			}
		}
		elseif($context == $this->_context && property_exists( $item, 'attribs' ))
		{
			$attribs = new JRegistry;
			$attribs->loadString( $item->attribs );

			$item->set('_fieldsandfilters', $attribs->get( 'fieldsandfilters' ));

			$attribs = $attribs->toObject();
			unset( $attribs->fieldsandfilters );

			$attribs = new JRegistry( $attribs );
			$item->attribs = (string) $attribs;
		}
		elseif($context == 'com_content.form' && $this->params->get('frontend_edit', false))
		{
			$jform = JFactory::getApplication()->input->post->get('jform', array(), 'array');

			if (array_key_exists('fieldsandfilters', $jform))
			{
				$item->set('_fieldsandfilters', $jform['fieldsandfilters']);
			}
		}

		return true;
	}

	/**
	 * @since       1.1.0
	 */
	public function onFieldsandfiltersAfterSave( $context, $item, $isNew )
	{
		if( !in_array($context, array($this->_context, 'com_content.form')) || empty( $item->id ) )
		{
			return true;
		}

		if( !( $itemData = $item->get('_fieldsandfilters') ) )
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
			$app->enqueueMessage( JText::sprintf( 'JLIB_APPLICATION_ERROR_MODELCLASS_NOT_FOUND', 'FieldsandfiltersModelElement' ), 'error' );
			return false;
		}

		$data = array(
			'item_id'		=> $item->id,
			'state'			=> $item->get( 'state', 0 ),
			'content_type_id'	=> $extensionContent->content_type_id,
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
		$elementsID = FieldsandfiltersFactory::getElements()->getElementsByIDColumn( 'id', $extensionContent->content_type_id, $pks, $this->_states, false );

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
				return $this->_onFieldsandfiltersContent( 'onContentAfterTitleCategory', $row, $params, $page, $context );
				break;
			case 'com_content.article':
				return $this->_onFieldsandfiltersContent( 'onContentAfterTitleArticle', $row, $params, $page, $context );
				break;
			case 'com_content.featured':
				return $this->_onFieldsandfiltersContent( 'onContentAfterTitleFeatured', $row, $params, $page, $context );
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
				return $this->_onFieldsandfiltersContent( 'onContentBeforeDisplayCategory', $row, $params, $page, $context );
				break;
			case 'com_content.article':
				return $this->_onFieldsandfiltersContent( 'onContentBeforeDisplayArticle', $row, $params, $page, $context );
				break;
			case 'com_content.featured':
				return $this->_onFieldsandfiltersContent( 'onContentBeforeDisplayFeatured', $row, $params, $page, $context );
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
				return $this->_onFieldsandfiltersContent( 'onContentAfterDisplayCategory', $row, $params, $page, $context );
				break;
			case 'com_content.article':
				return $this->_onFieldsandfiltersContent( 'onContentAfterDisplayArticle', $row, $params, $page, $context );
				break;
			case 'com_content.featured':
				return $this->_onFieldsandfiltersContent( 'onContentAfterDisplayFeatured', $row, $params, $page, $context );
				break;
		}

		return;
	}

	/**
	 * @since       1.1.0
	 */
	protected function _onFieldsandfiltersContent( $location, &$row, &$params, $page, $context )
	{
		if( !( $extensionContent = FieldsandfiltersFactory::getExtensions()->getExtensionsByName( $this->_name )->get( $this->_name ) ) )
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
			$staticMode = FieldsandfiltersModes::getMode(FieldsandfiltersModes::MODE_STATIC);
			if( $fieldsStatic = $fieldsHelper->getFieldsByModeIDPivot( 'location', $extensionContent->content_type_id, $staticMode, 1, 2 )->get( $location ) )
			{
				$fieldsStatic	= is_array( $fieldsStatic ) ? $fieldsStatic : array( $fieldsStatic );
			}
			else
			{
				$isStaticFields = false;
			}
		}

		// Load elements Helper
		if( !( $element = FieldsandfiltersFactory::getElements()->getElementsByItemIDPivot( 'item_id', $extensionContent->content_type_id, $row->id, $row->state, 3 )->get( $row->id ) ) && !$isStaticFields )
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

			if( !( $fields = $fieldsHelper->getFieldsByIDPivot( 'location', $extensionContent->content_type_id, $fieldsID, 1, 1 )->get( $location ) ) && !$isStaticFields )
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

		$fields = new JObject( JArrayHelper::pivot( (array) $fields, 'type' ) );

		$layoutFields = new JObject;

		JPluginHelper::importPlugin( 'fieldsandfilterstypes' );
		// Trigger the onFieldsandfiltersPrepareFormField event.
		JFactory::getApplication()->triggerEvent('getFieldsandfiltersFieldsHTML', array( $layoutFields, $fields, $element, $context ) );

		$layoutFields = $layoutFields->getProperties( true );

		if( empty( $layoutFields ) )
		{
			return;
		}

		ksort( $layoutFields );

		return implode( "\n", $layoutFields );
	}

	/**
	 * @since       1.1.0
	 */
	public function onFieldsandfiltersPrepareFiltersHTML( $context, Jobject $filters, $fieldsID = null, $getAllextensions = true, JRegistry $params = null, $ordering = 'ordering' )
	{
		if( !($contextOptions = $this->getContextOptions($context) ))
		{
			return;
		}

		$app 	= JFactory::getApplication();
		$jinput = $app->input;
		$id 	= $jinput->get( 'id', 0, 'int' );

		// Load PluginExtensions Helper
		$extensionsHelper = FieldsandfiltersFactory::getExtensions();
		$extensionContent = $extensionsHelper->getExtensionsByName( $this->_name )->get( $this->_name );

		if( ($contextOptions->isCategory && !$id) || !$extensionContent )
		{
			return;
		}

		// add model path
		JModelLegacy::addIncludePath( ( JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/overrides' ), ( $contextOptions->prefix . 'Model' ) );

		if( !( $model = JModelLegacy::getInstance( $contextOptions->class, ( $contextOptions->prefix . 'Model' ), array( 'ignore_request' => false, 'table_path' => JPATH_ADMINISTRATOR . '/components/' . $jinput->get( 'option' ) . '/tables' ) ) ) )
		{
			return;
		}

		$itemsID = $model->getContentItemsID();

		if( empty( $itemsID ) )
		{
			return;
		}

		// Load Filters Helper
		$counts = FieldsandfiltersFiltersHelper::getFiltersValuesCount( $extensionContent->content_type_id, $fieldsID, $itemsID, $contextOptions->state );

		if( empty( $counts ) )
		{
			return;
		}

		$extensionsID = ( $getAllextensions ) ? $extensionsHelper->getExtensionsByNameColumn('content_type_id', array( FieldsandfiltersExtensions::EXTENSION_DEFAULT , $this->_name ) ) : $extensionContent->content_type_id;

		// Load Fields Helper
		$fieldsHelper = FieldsandfiltersFactory::getFields();

		if( is_null( $fieldsID ) )
		{
			// Load PluginTypes Helper
			if( $modes = FieldsandfiltersModes::getMode(FieldsandfiltersModes::MODE_FILTER));
			{
				// multi extensions array( $this->_name, 'allextensions' )
				// $fields = $fieldsHelper->getFieldsByModeIDPivot( 'type', $extensionsID, $modes, 1, true );
				// single extension $this->_name
				$fields = $fieldsHelper->getFieldsByModeIDPivot( 'type', $extensionsID, $modes, 1, FieldsandfiltersFields::VALUES_VALUES, true );
			}
		}
		else if( is_numeric( $fieldsID ) || is_array( $fieldsID ) )
		{
			// multi extensions array( $this->_name, 'allextensions' )
			// $fields = $fieldsHelper->getFieldsByIDPivot( 'type', $extensionContent->content_type_id, $fieldsID, 1, true );
			// single extension $this->_name
			$fields = $fieldsHelper->getFieldsByIDPivot( 'type', $extensionsID, $fieldsID, 1, FieldsandfiltersFields::VALUES_VALUES, true );
		}
		else
		{
			return;
		}

		$templateFields = new JObject;

		JPluginHelper::importPlugin( 'fieldsandfilterstypes' );
		// Trigger the onFieldsandfiltersPrepareFormField event.
		$app->triggerEvent( 'getFieldsandfiltersFiltersHTML', array( $templateFields, $fields, $context, $params, $ordering ) );

		$templateFields = $templateFields->getProperties( true );

		if( empty( $templateFields ) )
		{
			return;
		}

		ksort( $templateFields );

		$filters->set('layouts', $templateFields);

		$filters->set( 'request', array(
			'extensionID' => (int) $extensionContent->content_type_id,
			'id' => $app->input->get( 'id', 0, 'int' ),
			// 'limitstart' => (int) $model->getState( 'list.start', 0 )
		));
		$filters->set( 'counts', (array) $counts );

		if( $app->get( 'sef', 0 ) )
		{
			$filters->set( 'pagination', array( 'start' => 0 ) );
		}
		else
		{
			$filters->set( 'pagination', array( 'limitstart' => 0 ) );
		}

		if( $contextOptions->isArchive && $filters->get('selector_body') && ($selectorArchiveForm = trim($this->params->get('selector_content_archive_form', '#adminForm' ))) )
		{
			$script = array();
			$script[] = 'jQuery(document).ready(function($) {';
			$script[] = '	$("'.$filters->get('selector_body').'").on( "submit", "'.$selectorArchiveForm.'", function(event){';
			$script[] = '		event.preventDefault();';
			$script[] = '		$(this).fieldsandfilters("submit");';
			$script[] = '		return false;';
			$script[] = '	});';
			$script[] = '});';

			$filters->set('callback', $filters->get('callback') . implode("\n", $script));
		}

		// [TODO] przniesc do fieldsandfilters.js
		/* [TEST]
		if( $isArchive )
		{
			$selector = $this->params->get('selector_content_archive_form', '#adminForm' );
			$script = array( $jregistry->get( 'filters.script', '') );
			$script[] = 'jQuery(document).ready(function($){';
			$script[] = '	$.fieldsandfilters.onSerialize = function(data){';
			$script[] = '		$("' . $selector . '").each( function(){';
			$script[] = '			$.each($(this).serializeArray(), function(k, obj){';
			$script[] = '				if( $.inArray( obj.name, ["month", "year", "limit"] ) != -1 && obj.value ){';
			$script[] = '					data[obj.name] = obj.value;';
			$script[] = '				}';
			$script[] = '			});';
			$script[] = '		});';
			$script[] = '	};';
			$script[] = '});';

			$jregistry->set( 'filters.script', implode( "\n", $script ) );
		}
		@end [TEST] */

		return implode( "\n", $templateFields );
	}

	/**
	 * @since       1.1.0
	 */
	public function onFieldsandfiltersRequestJSON( $context, JObject $data )
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

		if (!($contextOptions = $this->getContextOptions($jinput->get('context'))))
		{
			return false;
		}

		// Load PluginExtensions Helper
		$extension = FieldsandfiltersFactory::getExtensions()->getExtensionsByName( $this->_name )->get( $this->_name );

		if( ($contextOptions->isCategory && !$id) || !$extension )
		{
			return false;
		}

		// set new jinput values
		$jinput->set( 'option', 'com_content' );
		$jinput->set( 'view', $contextOptions->class );
		// $jinput->set( 'layout', 'blog' );
		$jinput->set( 'id', $jinput->get( 'id', 0, 'int' ) );

		// Include dependancies
		JLoader::import( 'com_content.helpers.route', JPATH_SITE . '/components' );
		JLoader::import( 'com_content.helpers.query', JPATH_SITE . '/components' );

		// Get controller Instance
		if( !( $controller = KextensionsController::getInstance( null, 'contentController', array( 'base_path' => $basePath, 'view_path' => ( $basePath . '/views' ) ) ) ) )
		{
			return false;
		}

		// load view
		if( !( $view = $controller->getView( $contextOptions->class, 'html', '', array( 'base_path' => $basePath, 'layout' => $jinput->get( 'layout', 'default' ) ) ) ) )
		{
			return false;
		}

		// For joomla 2.5 && Key Reference
		if( !FieldsandfiltersFactory::isVersion() )
		{
			$view->addTemplatePath( JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_content/' . $contextOptions->class );
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

			$itemsID = FieldsandfiltersFiltersHelper::getItemsIDByFilters( $extension->content_type_id, $fieldsandfilters, $contextOptions->state, $betweenFilters, $betweenValues );
		}
		else
		{
			$itemsID = FieldsandfiltersFiltersHelper::getSimpleItemsID( false );
		}

		// add model path
		$controller->addModelPath( ( JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/overrides' ), ( $contextOptions->prefix . 'Model' ) );

		if( !( $model = $controller->getModel( $contextOptions->class, ( $contextOptions->prefix . 'Model' ), array( 'ignore_request' => false, 'table_path' => JPATH_ADMINISTRATOR . '/components/' . $jinput->get( 'option' ) . '/tables' ) ) ) )
		{
			return false;
		}

		// set module to view
		$view->setModel( $model, true );
		$view->document = $document;

		// add helpers
		JHtml::addIncludePath( JPATH_SITE . '/components/com_content/helpers' );
		JHtml::addIncludePath( JPATH_SITE . '/components/com_content/helpers/html' );

		KextensionsLanguage::load( $jinput->get('option') );

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
			$this->loadLanguage();

			$variables 		= new JObject;
			$variables->type	= $this->_type;
			$variables->name	= $this->_name;
			$variables->params	= $this->params;
			$variables->extension 	= $extension;

			$body = KextensionsPlugin::renderLayout( $variables, FieldsandfiltersPlugin::getLayout($this->params, 'empty_layout', 'empty'));
		}

		$itemsID 	= $model->getState( 'fieldsandfilters.itemsID', array() );
		$fieldsID 	= $jinput->get( 'fields', array(), 'array' );

		if( !empty( $itemsID ) && !empty( $fieldsID ) && !$emptyItemsID  )
		{
			// Load Filters Helper
			$counts = (array) FieldsandfiltersFiltersHelper::getFiltersValuesCount( $extension->content_type_id, $fieldsID, $itemsID, $contextOptions->state );

			$data->set( 'counts', $counts );
		}
		else if( $emptyItemsID )
		{
			// [TODO] when is empty display all fields with 0 counts or display special buttons to reset filters
			$data->set( 'empty', $emptyItemsID );
		}

		$document->setBuffer( $body, array( 'type' => 'component', 'name' => 'fieldsandfilters', 'title' => null ) );

		if( !$emptyItemsID )
		{
			// [TODO] move to another place because we need this only once
			$script[] = 'jQuery(document).ready(function($) {';
			$script[] = '	$("' . $this->params->get( 'selector_pagination_filters', '.pagination' ) . '").fieldsandfilters("pagination"'
				. ( $app->get( 'sef', 0 ) ? ',{pagination: "start"}' : '' )
				. ');';
			$script[] = '});';

			$document->addScriptDeclaration( implode( "\n", $script ) );
		}
	}

	protected function getContextOptions($context)
	{
		if ($context != 'com_content.category' && $context != 'com_content.archive')
		{
			return false;
		}

		$options = new stdClass();
		$options->prefix = get_class( $this );
		$options->isArchive = $options->isCategory = false;

		switch ($context)
		{
			case 'com_content.category':

				$options->isCategory = true;
				$options->class = 'category';
				$options->state = 1;
				break;
			case 'com_content.archive':

				$options->isArchive = true;
				$options->class = 'archive';
				$options->state = 2;
				break;
		}

		return $options;
	}
}