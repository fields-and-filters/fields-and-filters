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
 * HTML View class for the Cpanel component
 */
class FieldsandfiltersViewCpanel extends JViewLegacy
{
	protected $buttons = array();

	/**
	 * Display the view
	 *
	 * @since    1.0.0
	 */
	public function display($tpl = null)
	{
		$this->buttons = FieldsandfiltersHelper::getButtons(true, array('cpanel'));

		$this->addToolbar();

		if (is_null($tpl) && !FieldsandfiltersFactory::isVersion())
		{
			$tpl = '2.5';
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

		JToolBarHelper::title(JText::_('COM_FIELDSANDFILTERS'), 'faf-fieldsandfilters');

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_fieldsandfilters');
		}
	}
}
