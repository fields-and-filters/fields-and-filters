<?php
/**
 * @version     1.1.0
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kulka.tomek@gmail.com> - 
 */

// No direct access
defined('_JEXEC') or die;

/**
 * View to edit
 * @since	1.1.0
 */
class FieldsandfiltersViewElement extends JViewLegacy
{
	protected $state;
	protected $item;
	protected $form;

	/**
	 * Display the view
	 * 
	 * @since	1.0.0
	 */
	public function display( $tpl = null )
	{
		$tpl 			= is_null( $tpl ) && !FieldsandfiltersFactory::isVersion() ? '2.5' : $tpl;
		
		$this->state		= $this->get( 'State' );
		$this->item		= $this->get( 'Item' );
		$this->form		= $this->get( 'Form' );
		
		// Check for errors.
		if( count( $errors = $this->get( 'Errors' ) ) )
		{
			throw new Exception( implode( "\n", $errors ) );
		}
		
		$this->addToolbar();
		parent::display( $tpl );
	}
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.1.0
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set( 'hidemainmenu', true );
		
		JToolBarHelper::title( JText::_( 'COM_FIELDSANDFILTERS_TITLE_ELEMENT' ), 'element.png' );
		
		$canDo = FieldsandfiltersFactory::getHelper()->getActions();
		
		// If not checked out, can save the item.
		if( $canDo->get('core.edit') || $canDo->get('core.create' ) )
		{
			JToolBarHelper::apply( 'element.apply', 'JTOOLBAR_APPLY' );
			JToolBarHelper::save( 'element.save', 'JTOOLBAR_SAVE' );
		}
		
		$isNew = empty( $this->item->field_value_id );
		
		if( $isNew )
		{
			JToolBarHelper::cancel( 'element.cancel', 'JTOOLBAR_CANCEL' );
		}
		else
		{
			JToolBarHelper::cancel( 'element.cancel', 'JTOOLBAR_CLOSE' );
		}
	}
}
