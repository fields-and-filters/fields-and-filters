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
 * View class for a list of plugins types.
 */
class FieldsandfiltersViewPlugins extends JViewLegacy
{
	// Array of plugin types or groups plugin types
	protected $plugins = null;

	/**
	 * Display the view
	 *
	 * @since    1.1.0
	 */
	public function display($tpl = null)
	{
		$tpl = is_null($tpl) && !FieldsandfiltersFactory::isVersion() ? '2.5' : $tpl;

		switch ($this->getLayout())
		{
			case 'types':
				// Load PluginTypes Helper - getTypesGroup
				$this->plugins = FieldsandfiltersFactory::getTypes()->getTypesGroup();
				break;
			case 'extensions':
				// Load PluginExtensions Helper - 
				$this->plugins = FieldsandfiltersFactory::getExtensions()->getExtensionsGroup();
				break;
		}

		if (is_null($this->plugins))
		{
			echo JText::sprintf('COM_FIELDSANDFILTERS_ERROR_NOT_PLUGINS_TPL', $this->getLayout());

			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since    1.0.0
	 */
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_FIELDSANDFILTERS_HEADER_PLUGIN_' . strtoupper($this->getLayout())), 'faf-plugin-' . strtolower($this->getLayout()));
	}
}