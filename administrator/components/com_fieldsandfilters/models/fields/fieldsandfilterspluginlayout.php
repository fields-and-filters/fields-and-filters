<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.folder');

/**
 * Form Field class for the Joomla Platform.
 *
 * @package     fieldsandfilters
 * @subpackage  Form
 * @see         JFormFieldPluginsType for a select list of type plugins.
 * @since       1.1.0
 */
class JFormFieldFieldsandfiltersPluginLayout extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'FieldsandfiltersPluginLayout';

	/**
	 * The parent field of the form field
	 *
	 * @var    mixed
	 * @since  1.2.0
	 */
	protected $parentField = 'type';

	/**
	 * The plugin type of the form field
	 *
	 * @var    mixed
	 * @since  1.2.0
	 */
	protected $pluginType = 'fieldsandfilterstypes';

	/**
	 * The plugin name of the form field
	 *
	 * @var    mixed
	 * @since  1.2.0
	 */
	protected $pluginName;

	/**
	 * The mode type of the form field
	 *
	 * @var    mixed
	 * @since  1.2.0
	 */
	protected $layoutFolder;

	/**
	 * The size of the form field.
	 *
	 * @var    integer
	 * @since      1.2.0
	 * @deprecated >= J3.2
	 */
	protected $size;

	/**
	 * The class of the form field
	 *
	 * @var    mixed
	 * @since      1.2.0
	 * @deprecated >= J3.2
	 */
	protected $class;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string $name The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   1.2.0
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'parentField':
			case 'pluginType':
			case 'layoutFolder':
				return $this->$name;

			/* @deprecated >= J3.2 */
			case 'size':
			case 'class':
				return $this->$name;
			/* @enddeprecated >= J3.2 */
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string $name  The property name for which to the the value.
	 * @param   mixed  $value The value of the property.
	 *
	 * @return  void
	 *
	 * @since   1.2.0 && J3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'parentField':
				$this->$name = (string) $value;
				break;

			case 'pluginType':
			case 'pluginName':
			case 'layoutFolder':
				$value       = (string) $value;
				$this->$name = preg_replace('#\W#', '', $value);
				break;

			/* @deprecated >= J3.2 */
			case 'class':
				// Removes spaces from left & right and extra spaces from middle
				$value       = preg_replace('/\s+/', ' ', trim((string) $value));
				$this->$name = (string) $value;
				break;

			case 'size':
				$this->$name = (int) $value;
				break;

			default:
				if (FieldsandfiltersFactory::isVersion('>=', 3.2))
				{
					parent::__set($name, $value);
				}
				break;
			/* @end deprecated >= J3.2 */
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement $element   The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed            $value     The form field value to validate.
	 * @param   string           $group     The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   1.2.0
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$attributes = array('parentField', 'pluginType', 'layoutFolder', 'size', 'class'); /* class is @deprecated >= J3.2 */
			foreach ($attributes as $attributeName)
			{
				if (isset($element[$attributeName]))
				{
					$this->__set($attributeName, $element[$attributeName]);
				}
			}

			if ($this->form instanceof JForm && !$this->pluginName)
			{
				$this->__set('pluginName', $this->form->getValue($this->parentField));
			}
		}

		return $return;
	}

	/**
	 * Method to get the field input for module layouts.
	 *
	 * @return  string  The field input.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		if ($this->pluginName)
		{
			$lang      = JFactory::getLanguage();
			$extension = 'plg_' . $this->pluginType . '_' . $this->pluginName;

			// Load language file
			KextensionsLanguage::load($extension, JPATH_ADMINISTRATOR);

			// Get the database object and a new query object.
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Build the query.
			$query->select(array(
				$db->quoteName('element'),
				$db->quoteName('name')
			))
				->from($db->quoteName('#__extensions', 'e'))
				->where($db->quoteName('e.client_id') . ' = ' . 0)
				->where($db->quoteName('e.type') . ' = ' . $db->quote('template'))
				->where($db->quoteName('e.enabled') . ' = 1');

			// Set the query and load the templates.
			$templates = $db->setQuery($query)->loadObjectList('element');

			// Build the search paths for module layouts.
			$plugin_path = JPath::clean(JPATH_PLUGINS . "/{$this->pluginType}/{$this->pluginName}/tmpl/{$this->layoutFolder}");

			// Prepare array of component layouts
			$plugin_layouts = array();

			// Prepare the grouped list
			$groups = array();

			// Add the layout options from the module path.
			if (is_dir($plugin_path) && ($plugin_layouts = JFolder::files($plugin_path, '^[^_]*\.php$')))
			{
				// Create the group for the plgin
				$group        = JHtml::_('select.option', $this->id, JText::_('COM_FIELDSANDFILTERS_OPTION_FROM_PLUGIN'));
				$group->items = array();

				foreach ($plugin_layouts as $file)
				{
					// Add an option to the module group
					$value          = basename($file, '.php');
					$text           = $lang->hasKey($key = strtoupper($extension . '_LAYOUT_' . $value)) ? JText::_($key) : $value;
					$group->items[] = JHtml::_('select.option', $value, $text);
				}

				$groups['_'] = $group;
			}

			// Loop on all templates
			if ($templates)
			{
				foreach ($templates as $template)
				{
					// Load language file
					KextensionsLanguage::load('tpl_' . $template->element, JPATH_SITE);

					$template_path = JPath::clean(JPATH_SITE . "/templates/{$template->element}/html/$extension/{$this->layoutFolder}");

					// Add the layout options from the template path.
					if (is_dir($template_path) && ($files = JFolder::files($template_path, '^[^_]*\.php$')))
					{
						foreach ($files as $i => $file)
						{
							// Remove layout that already exist in component ones
							if (in_array($file, $plugin_layouts))
							{
								unset($files[$i]);
							}
						}

						if (count($files))
						{
							// Create the group for the template
							$group        = JHtml::_('select.option', $this->id . '_' . $template->element, JText::sprintf('JOPTION_FROM_TEMPLATE', $template->name));
							$group->items = array();

							foreach ($files as $file)
							{
								// Add an option to the template group
								$value          = basename($file, '.php');
								$text           = $lang->hasKey($key = strtoupper('TPL_' . $template->element . '_' . $extension . '_LAYOUT_' . $value))
									? JText::_($key) : $value;
								$group->items[] = JHtml::_('select.option', $template->element . ':' . $value, $text);
							}

							$groups[$template->element] = $group;
						}
					}
				}
			}

			// Compute attributes for the grouped list
			$attr = $this->size ? ' size="' . $this->size . '"' : '';

			// Add a grouped list
			return JHtml::_(
				'select.groupedlist', $groups, $this->name,
				array('id' => $this->id, 'group.id' => 'id', 'list.attr' => $attr, 'list.select' => (array) $this->value)
			);

			return implode($html);
		}

		return '';
	}
}
