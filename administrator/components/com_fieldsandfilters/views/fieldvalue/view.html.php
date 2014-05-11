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
 * View to edit
 *
 * @since    1.1.0
 */
class FieldsandfiltersViewFieldvalue extends JViewLegacy
{
	protected $state;
	protected $item;
	protected $form;

	/**
	 * Display the view
	 *
	 * @since    1.0.0
	 */
	public function display($tpl = null)
	{
		$tpl = is_null($tpl) && !FieldsandfiltersFactory::isVersion() ? '2.5' : $tpl;

		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		if (FieldsandfiltersFactory::isVersion('<', 3.2) && is_null($tpl))
		{
			$tpl = (FieldsandfiltersFactory::isVersion()) ? '3.1' : '2.5';
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since    1.1.0
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		JToolBarHelper::title(JText::_('COM_FIELDSANDFILTERS_TITLE_FIELDVALUE'), 'faf-field-value');

		$canDo = FieldsandfiltersHelper::getActions();

		// If not checked out, can save the item.
		if ($canDo->get('core.edit') || $canDo->get('core.create'))
		{
			JToolBarHelper::apply('fieldvalue.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('fieldvalue.save', 'JTOOLBAR_SAVE');
		}

		if ($canDo->get('core.create'))
		{
			JToolBarHelper::custom('fieldvalue.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}

		$isNew = empty($this->item->field_value_id);

		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create'))
		{
			JToolBarHelper::custom('fieldvalue.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}

		if ($isNew)
		{
			JToolBarHelper::cancel('fieldvalue.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			JToolBarHelper::cancel('fieldvalue.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
