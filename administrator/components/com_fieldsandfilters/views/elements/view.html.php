<?php
/**
 * @version     1.1.1
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
class FieldsandfiltersViewElements extends JViewLegacy
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
		
		FieldsandfiltersHelper::addSubmenu( JFactory::getApplication()->input->getCmd( 'view', '' ) );
		
		$this->addToolbar();
		
		if( FieldsandfiltersFactory::isVersion() )
		{
			$this->sidebar = JHtmlSidebar::render();
		}
		else if( is_null( $tpl ) )
		{
			$tpl = '2.5';
		}
		
		parent::display( $tpl );
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.1.0
	 */
	protected function addToolbar()
	{
		JHtml::addIncludePath( JPATH_COMPONENT_ADMINISTRATOR . '/helpers/html' );
		
		$canDo = FieldsandfiltersHelper::getActions();
		
		JToolBarHelper::title( JText::_( 'COM_FIELDSANDFILTERS_TITLE_ELEMENTS' ), 'elements.png' );
		
		if( FieldsandfiltersFactory::isVersion() )
		{
			JHtmlSidebar::setAction( 'index.php?option=com_fieldsandfilters&view=elements' );
			
			JHtmlSidebar::addFilter(
				JText::_( 'COM_FIELDSANDFILTERS_OPTION_SELECT_EXTENSION' ),
				'filter_extension_type_id',
				JHtml::_( 'select.options', JHtml::_( 'fieldsandfilters.extensionsOptions', array( 'allextensions' ) ), 'value', 'text', $this->state->get( 'filter.extension_type_id' ), false )
			);
			
			if( is_array( $filtersOptions = $this->state->get( 'filters.options' ) ) )
			{
				foreach( $filtersOptions AS $name => &$filter )
				{
					$filter = !is_array( $filter ) ? (array) $filter : $filter;
					
					JHtmlSidebar::addFilter(
						JArrayHelper::getValue( $filter, 'label' ),
						( 'filter_' . $name ),
						JHtml::_( 'select.options', JArrayHelper::getValue( $filter, 'options', array(), 'array' ), 'value', 'text', $this->state->get( 'filter.' . $name ), false )
					);
				}
			}
		}
		
		if( $canDo->get( 'core.admin' ) )
		{
			JToolBarHelper::preferences( 'com_fieldsandfilters' );
		}
	}
	
	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since	1.0.0
	 */
	protected function getSortFields()
	{
		return array(
			'e.ordering' 						=> JText::_( 'JGRID_HEADING_ORDERING' ),
			'e.state' 						=> JText::_( 'COM_FIELDSANDFILTERS_FIELDS_STATUS' ),
			$this->state->get( 'query.item_id', 'e.item_id' ) 	=> JText::_( 'COM_FIELDSANDFILTERS_ELEMENTS_ITEM_ID' ),
			'e.extension_type_id' 					=> JText::_( 'COM_FIELDSANDFILTERS_ELEMENTS_EXTENSION_TYPE' ),
			'e.element_id'						=> JText::_( 'COM_FIELDSANDFILTERS_ELEMENTS_ELEMENT_ID' )
		);
	}
}
