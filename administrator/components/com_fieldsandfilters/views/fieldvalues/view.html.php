<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kulka.tomek@gmail.com> -
 */

// No direct access
defined('_JEXEC') or die;

/**
 * View class for a list of Fieldsandfilters.
 *
 * @since    1.1.0
 */
class FieldsandfiltersViewFieldvalues extends JViewLegacy
{
	/**
	 * @since    1.0.0
	 */
	protected $items;

	/**
	 * @since    1.0.0
	 */
	protected $pagination;

	/**
	 * @since    1.0.0
	 */
	protected $state;

	/**
	 * @since    1.2.0
	 */
	public $filterForm;

	/**
	 * @since    1.2.0
	 */
	public $activeFilters;

	/**
	 * Display the view
	 *
	 * @since    1.1.0
	 */
	public function display($tpl = null)
	{
		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		if (FieldsandfiltersFactory::isVersion('>=', 3.2))
		{
			$this->filterForm    = $this->get('FilterForm');
			$this->activeFilters = $this->get('ActiveFilters');
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		FieldsandfiltersHelper::addSubmenu(JFactory::getApplication()->input->getCmd('view', ''));

		$this->addToolbar();

		if (FieldsandfiltersFactory::isVersion())
		{
			$this->sidebar = JHtmlSidebar::render();

			if (is_null($tpl) && FieldsandfiltersFactory::isVersion('<', 3.2))
			{
				$tpl = '3.1';
			}
		}
		else
		{
			if (is_null($tpl))
			{
				$tpl = '2.5';
			}
		}

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since    1.1.0
	 */
	protected function addToolbar()
	{
		$canDo = FieldsandfiltersHelper::getActions();

		JToolBarHelper::title(JText::_('COM_FIELDSANDFILTERS_TITLE_FIELDVALUES'), 'faf-field-values');

		if (FieldsandfiltersFactory::isVersion() && FieldsandfiltersFactory::isVersion('<', 3.2))
		{
			JHtmlSidebar::setAction('index.php?option=com_fieldsandfilters&view=fieldvalues');

			JHtmlSidebar::addFilter(
				null,
				'filter_field_id',
				JHtml::_('select.options', JHtml::_('FieldsandfiltersHtml.options.fields'), 'value', 'text', $this->state->get('filter.field_id'), false)
			);

			JHtmlSidebar::addFilter(
				JText::_('JOPTION_SELECT_PUBLISHED'),
				'filter_published',
				JHtml::_('select.options', JHtml::_('FieldsandfiltersHtml.options.states', array('adminonly' => false)), 'value', 'text', $this->state->get('filter.state'), false)
			);
		}

		if ($canDo->get('core.create'))
		{
			JToolBarHelper::addNew('fieldvalue.add', 'JTOOLBAR_NEW');
		}

		if ($canDo->get('core.edit'))
		{
			JToolBarHelper::editList('fieldvalue.edit', 'JTOOLBAR_EDIT');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolBarHelper::divider();
			JToolBarHelper::custom('fieldvalues.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom('fieldvalues.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::deleteList('', 'fieldvalues.delete', 'JTOOLBAR_DELETE');

		}

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_fieldsandfilters');
		}
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since    1.1.0
	 */
	protected function getSortFields()
	{
		return array(
			'fv.field_value'    => JText::_('COM_FIELDSANDFILTERS_FIELDVALUES_FIELD_VALUE'),
			'f.field_name'      => JText::_('COM_FIELDSANDFILTERS_FIELDVALUES_FIELD'),
			'fv.state'          => JText::_('JPUBLISHED'),
			'fv.ordering'       => JText::_('JGRID_HEADING_ORDERING'),
			'fv.field_value_id' => JText::_('COM_FIELDSANDFILTERS_FIELDVALUES_FIELD_VALUE_ID')
		);
	}
}
