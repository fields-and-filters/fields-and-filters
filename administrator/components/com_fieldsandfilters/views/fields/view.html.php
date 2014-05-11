<?php
/**
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

		JToolBarHelper::title(JText::_('COM_FIELDSANDFILTERS_TITLE_FIELDS'), 'faf-fields');

		if (FieldsandfiltersFactory::isVersion() && FieldsandfiltersFactory::isVersion('<', 3.2))
		{
			JHtmlSidebar::setAction('index.php?option=com_fieldsandfilters&view=fields');

			JHtmlSidebar::addFilter(
				JText::_('JOPTION_SELECT_PUBLISHED'),
				'filter_published',
				JHtml::_('select.options', JHtml::_('FieldsandfiltersHtml.options.states'), 'value', 'text', $this->state->get('filter.state'), false)
			);

			JHtmlSidebar::addFilter(
				JText::_('COM_FIELDSANDFILTERS_OPTION_SELECT_EXTENSION'),
				'filter_content_type_id',
				JHtml::_('select.options', JHtml::_('FieldsandfiltersHtml.options.extensions'), 'value', 'text', $this->state->get('filter.content_type_id'), false)
			);

			JHtmlSidebar::addFilter(
				JText::_('COM_FIELDSANDFILTERS_OPTION_SELECT_TYPE'),
				'filter_type',
				JHtml::_('select.options', JHtml::_('FieldsandfiltersHtml.options.types'), 'value', 'text', $this->state->get('filter.type'), false)
			);

			/*
			JHtmlSidebar::addFilter(
				JText::_('JOPTION_SELECT_ACCESS'),
				'filter_access',
				JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'), false)
			);
			
			JHtmlSidebar::addFilter(
				JText::_('JOPTION_SELECT_LANGUAGE'),
				'filter_language',
				JHtml::_('select.options', JHtml::_('contentlanguage.existing'), 'value', 'text', $this->state->get('filter.language'), false)
			);
			*/
		}

		if ($canDo->get('core.create'))
		{
			JToolBarHelper::addNew('field.add', 'JTOOLBAR_NEW');
		}

		if ($canDo->get('core.edit') && isset($this->items[0]))
		{
			JToolBarHelper::editList('field.edit', 'JTOOLBAR_EDIT');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolBarHelper::divider();
			JToolBarHelper::custom('fields.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom('fields.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::custom('fields.onlyadmin', 'dashboard.png', 'dashboard_f2.png', 'COM_FIELDSANDFILTERS_TOOLBAR_ONLYADMIN', true);
			JToolBarHelper::deleteList('', 'fields.delete', 'JTOOLBAR_DELETE');

			JToolBarHelper::divider();
			JToolBarHelper::custom('fields.required', 'star.png', 'star_f2.png', 'COM_FIELDSANDFILTERS_TOOLBAR_REQUIRED', true);
			JToolBarHelper::custom('fields.unrequired', 'star-empty.png', 'star-empty_f2.png', 'COM_FIELDSANDFILTERS_TOOLBAR_UNREQUIRED', true);
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
	 * @since   1.0.0
	 */
	protected function getSortFields()
	{
		return array(
			'f.name'            => JText::_('COM_FIELDSANDFILTERS_FIELDS_FIELD_NAME'),
			'f.state'           => JText::_('COM_FIELDSANDFILTERS_FIELDS_STATUS'),
			'f.type'            => JText::_('COM_FIELDSANDFILTERS_FIELDS_FIELD_TYPE'),
			'f.content_type_id' => JText::_('COM_FIELDSANDFILTERS_FIELDS_EXTENSION_TYPE'),
			'f.required'        => JText::_('COM_FIELDSANDFILTERS_FIELDS_REQUIRED'),
			// 'f.language' 	=> JText::_('COM_FIELDSANDFILTERS_FIELDS_LANGUAGE'),
			'f.ordering'        => JText::_('JGRID_HEADING_ORDERING'),
			'f.id'              => JText::_('COM_FIELDSANDFILTERS_FIELDS_FIELD_ID')
		);
	}
}