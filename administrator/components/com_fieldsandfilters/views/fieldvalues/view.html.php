<?php
/**
 * @version     1.0.0
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kulka.tomek@gmail.com> - 
 */

// No direct access
defined('_JEXEC') or die;

/**
 * View class for a list of Fieldsandfilters.
 * @since	1.1.0
 */
class FieldsandfiltersViewFieldvalues extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 * 
	 * @since	1.1.0
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
		
		FieldsandfiltersFactory::getHelper()->addSubmenu( JFactory::getApplication()->input->getCmd( 'view', '' ) );
		
		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();
		
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.1.0
	 */
	protected function addToolbar()
	{
		JHtml::addIncludePath( JPATH_COMPONENT_ADMINISTRATOR . '/helpers/html' );
		
		$canDo = FieldsandfiltersFactory::getHelper()->getActions();
		
		JToolBarHelper::title( JText::_( 'COM_FIELDSANDFILTERS_TITLE_FIELDVALUES' ), 'field-values.png' );
		
		JHtmlSidebar::setAction( 'index.php?option=com_fieldsandfilters&view=fieldvalues' );
		
		JHtmlSidebar::addFilter(
			null,
			'filter_field_id',
			JHtml::_( 'select.options', JHtml::_( 'fieldsandfilters.fieldsOptions' ), 'value', 'text', $this->state->get( 'filter.field_id' ), false )
		);
		
		JHtmlSidebar::addFilter(
			JText::_( 'JOPTION_SELECT_PUBLISHED' ),
			'filter_published',
			JHtml::_( 'select.options', JHtml::_( 'fieldsandfilters.publishedOptions', array( 'adminonly' => false ) ), 'value', 'text', $this->state->get( 'filter.state' ), true )
		);
		
		if( $canDo->get( 'core.create' ) )
		{
			JToolBarHelper::addNew( 'fieldvalue.add','JTOOLBAR_NEW' );
		}
	    
		if( $canDo->get( 'core.edit' ) )
		{
			JToolBarHelper::editList( 'fieldvalue.edit','JTOOLBAR_EDIT' );
		}
		
		if( $canDo->get( 'core.edit.state' ) )
		{
			JToolBarHelper::divider();
			JToolBarHelper::custom( 'fieldvalues.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true );
			JToolBarHelper::custom( 'fieldvalues.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true );
			JToolBarHelper::deleteList( '', 'fieldvalues.delete','JTOOLBAR_DELETE' );
			
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
	 * @since	1.1.0
	 */
	protected function getSortFields()
	{
		return array(
			'fv.field_value' 	=> JText::_( 'COM_FIELDSANDFILTERS_FIELDVALUES_FIELD_VALUE' ),
			'f.field_name' 		=> JText::_( 'COM_FIELDSANDFILTERS_FIELDVALUES_FIELD' ),
			'fv.state' 		=> JText::_( 'JPUBLISHED' ),
			'fv.ordering' 		=> JText::_( 'JGRID_HEADING_ORDERING' ),
			'fv.field_value_id'	=> JText::_( 'COM_FIELDSANDFILTERS_FIELDVALUES_FIELD_VALUE_ID' )
		);
	}
}
