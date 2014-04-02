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
 */
class FieldsandfiltersViewElements extends JViewLegacy
{
	/**
	 * List items
	 *
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
	 * @since    1.2.0
	 */
	public $extensionDir;

	/**
	 * Single item
	 *
	 * @since    1.2.0
	 */
	public $item;

	/**
	 * Display the view
	 *
	 * @since    1.1.0
	 */
	public function display($tpl = null)
	{
		$this->state        = $this->get('State');
		$this->items        = $this->get('Items');
		$this->pagination   = $this->get('Pagination');
		$this->extensionDir = $this->get('ExtensionDir');

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

		if ($this->extensionDir)
		{
			$this->addTemplatePath($this->extensionDir);
		}

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

		JToolBarHelper::title(JText::_('COM_FIELDSANDFILTERS_TITLE_ELEMENTS'), 'faf-contents');

		if (FieldsandfiltersFactory::isVersion() && FieldsandfiltersFactory::isVersion('<', 3.2))
		{
			JHtmlSidebar::setAction('index.php?option=com_fieldsandfilters&view=elements');

			JHtmlSidebar::addFilter(
				JText::_('COM_FIELDSANDFILTERS_OPTION_SELECT_EXTENSION'),
				'filter_extension_type_id',
				JHtml::_('select.options', JHtml::_('FieldsandfiltersHtml.options.extensions', array('allextensions')), 'value', 'text', $this->state->get('filter.extension_type_id'), false)
			);

			if (is_array($filtersOptions = $this->state->get('filters.options')))
			{
				foreach ($filtersOptions AS $name => &$filter)
				{
					$filter = !is_array($filter) ? (array) $filter : $filter;

					JHtmlSidebar::addFilter(
						JArrayHelper::getValue($filter, 'label'),
						('filter_' . $name),
						JHtml::_('select.options', JArrayHelper::getValue($filter, 'options', array(), 'array'), 'value', 'text', $this->state->get('filter.' . $name), false)
					);
				}
			}
		}

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_fieldsandfilters');
		}
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since    1.0.0
	 */
	protected function getSortFields()
	{
		return array(
			'e.ordering'        => JText::_('JGRID_HEADING_ORDERING'),
			'e.state'           => JText::_('COM_FIELDSANDFILTERS_FIELDS_STATUS'),
			'e.item_id'         => JText::_('COM_FIELDSANDFILTERS_ELEMENTS_ITEM_ID'),
			'e.content_type_id' => JText::_('COM_FIELDSANDFILTERS_ELEMENTS_EXTENSION_TYPE'),
			'e.id'              => JText::_('COM_FIELDSANDFILTERS_ELEMENTS_ELEMENT_ID')
		);
	}
}
