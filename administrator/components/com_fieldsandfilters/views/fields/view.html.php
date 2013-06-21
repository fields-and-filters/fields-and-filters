<?php
/**
 * @version     1.0.0
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access
defined('_JEXEC') or die;

/**
 * View class for a list of Fieldsandfilters.
 */
class FieldsandfiltersViewFields extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display( $tpl = null )
	{
		$this->state		= $this->get( 'State' );
		$this->items		= $this->get( 'Items' );
		$this->pagination	= $this->get( 'Pagination' );

		// Check for errors.
		if( count( $errors = $this->get( 'Errors' ) ) )
		{
			throw new Exception( implode( "\n", $errors ) );
		}
		
		FieldsandfiltersHelper::addSubmenu( JFactory::getApplication()->input->getCmd( 'view', '' ) );
		
		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();
		
		parent::display( $tpl );
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		JLoader::import( 'helpers.fieldsandfilters', JPATH_COMPONENT_ADMINISTRATOR );
		JHtml::addIncludePath( JPATH_COMPONENT_ADMINISTRATOR . '/helpers/html' );
		
		$canDo	= FieldsandfiltersHelper::getActions();
		
		JToolBarHelper::title( JText::_( 'COM_FIELDSANDFILTERS_TITLE_FIELDS' ), 'fields.png' );
		
		JHtmlSidebar::setAction( 'index.php?option=com_fieldsandfilters&view=fields' );
		
		JHtmlSidebar::addFilter(
			JText::_( 'JOPTION_SELECT_PUBLISHED' ),
			'filter_published',
			JHtml::_( 'select.options', JHtml::_( 'fieldsandfilters.publishedOptions' ), 'value', 'text', $this->state->get( 'filter.state' ), true )
		);
		
		JHtmlSidebar::addFilter(
			JText::_( 'COM_FIELDSANDFILTERS_OPTION_SELECT_TYPE' ),
			'filter_extension_type_id',
			JHtml::_( 'select.options', JHtml::_( 'fieldsandfilters.pluginExtensionsOptions' ), 'value', 'text', $this->state->get( 'filter.extension_type_id' ), true )
		);
		
		JHtmlSidebar::addFilter(
			JText::_( 'COM_FIELDSANDFILTERS_OPTION_SELECT_TYPE' ),
			'filter_type',
			JHtml::_( 'select.options', JHtml::_( 'fieldsandfilters.pluginTypesOptions' ), 'value', 'text', $this->state->get( 'filter.type' ), true )
		);
		
		JHtmlSidebar::addFilter(
			JText::_( 'JOPTION_SELECT_ACCESS' ),
			'filter_access',
			JHtml::_( 'select.options', JHtml::_( 'access.assetgroups' ), 'value', 'text', $this->state->get( 'filter.access' ), true )
		);
		
		JHtmlSidebar::addFilter(
			JText::_( 'JOPTION_SELECT_LANGUAGE' ),
			'filter_language',
			JHtml::_( 'select.options', JHtml::_( 'contentlanguage.existing' ), 'value', 'text', $this->state->get( 'filter.language' ), true )
		);
		
		if( $canDo->get( 'core.create' ) )
		{
			JToolBarHelper::addNew( 'field.add','JTOOLBAR_NEW' );
		}
	    
		if( $canDo->get( 'core.edit' ) && isset( $this->items[0] ) )
		{
			JToolBarHelper::editList( 'field.edit','JTOOLBAR_EDIT' );
		}
		
		if( $canDo->get( 'core.edit.state' ) )
		{
			JToolBarHelper::divider();
			JToolBarHelper::custom( 'fields.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true );
			JToolBarHelper::custom( 'fields.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true );
			JToolBarHelper::custom( 'fields.onlyadmin', 'dashboard.png', 'dashboard_f2.png', 'COM_FIELDSANDFILTERS_TOOLBAR_ONLYADMIN', true );
			JToolBarHelper::deleteList( '', 'fields.delete','JTOOLBAR_DELETE' );
			
			JToolBarHelper::divider();
			JToolBarHelper::custom( 'fields.required', 'star.png', 'star_f2.png','COM_FIELDSANDFILTERS_TOOLBAR_REQUIRED', true );
			JToolBarHelper::custom( 'fields.unrequired', 'star-empty.png', 'star-empty_f2.png', 'COM_FIELDSANDFILTERS_TOOLBAR_UNREQUIRED', true );
		}
		
		if( $canDo->get( 'core.admin' ) )
		{
			JToolBarHelper::divider();
			JToolBarHelper::preferences( 'com_fieldsandfilters' );
		}
	}
	
	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'f.field_name' 		=> JText::_( 'COM_FIELDSANDFILTERS_FIELDS_FIELD_NAME' ),
			'f.state' 		=> JText::_( 'COM_FIELDSANDFILTERS_FIELDS_STATUS' ),
			'f.field_type' 		=> JText::_( 'COM_FIELDSANDFILTERS_FIELDS_FIELD_TYPE' ),
			'f.extension_type_id' 	=> JText::_( 'COM_FIELDSANDFILTERS_FIELDS_EXTENSION_TYPE' ),
			'f.required' 		=> JText::_( 'COM_FIELDSANDFILTERS_FIELDS_REQUIRED' ),
			// 'f.language' 		=> JText::_( 'COM_FIELDSANDFILTERS_FIELDS_LANGUAGE' ),
			'f.ordering' 		=> JText::_( 'JGRID_HEADING_ORDERING' ),
			'f.field_id' 		=> JText::_( 'COM_FIELDSANDFILTERS_FIELDS_FIELD_ID' )
		);
	}
}