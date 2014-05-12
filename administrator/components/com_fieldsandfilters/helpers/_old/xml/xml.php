<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');

/**
 * @package           fieldsandfilters.administrator
 * @subpackage        com_fieldsandfilters
 *
 * @since             1.2.0
 */
class FieldsandfiltersXml
{
	/**
	 * @param       $element
	 * @param array $config
	 *
	 * @return bool
	 * @since    1.0.0
	 */
	public static function getPluginOptionsForms($element, $config = array())
	{
		if (!isset($element->name) || !isset($element->type))
		{
			return;
		}

		// Get the params file path plugin.
		if ($element->type == 'com_fieldsandfilters')
		{
			$formsDir = JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/models/forms/allextensions';
		}
		else
		{
			$formsDir = JPATH_PLUGINS . "/{$element->type}/{$element->name}/forms";
		}

		$formsDir = JPath::clean($formsDir);

		if (!(is_dir($formsDir) && ($formsPath = JFolder::files($formsDir, '^[^_]*\.xml$', false, true))))
		{
			return;
		}

		$element->forms = new JObject;
		while ($formPath = array_shift($formsPath))
		{
			$form = new JObject;

			// Attempt to load the xml file.
			if ($xml = simplexml_load_file($formPath))
			{
				// Look for the first view node off of the root node.
				if ($layout = $xml->xpath('layout[1] '))
				{
					$layout = $layout[0];

					// Get group if params have it
					if ($group = $xml->xpath('group[1]'))
					{
						$group = $group[0];
						if (!empty($group['title']))
						{
							$form->group              = new stdClass;
							$form->group->title       = trim((string) $group['title']);
							$form->group->description = !empty($group->message[0]) ? trim((string) $group->message[0]) : '';
						}
					}

					// If the view is hidden from the menu, discard it and move on to the next view.
					if (!isset($form->group) || (!empty($layout['hidden']) && $layout['hidden'] == 'true'))
					{
						unset($xml, $form);
						continue;
					}

					// Populate the title and description if they exist.
					$form->title       = !empty($layout['title']) ? trim((string) $layout['title']) : ucfirst($element->name);
					$form->description = !empty($layout->message[0]) ? trim((string) $layout->message[0]) : '';
				}
			}

			$form->path = $formsDir;
			$groupName  = $form->group->name = basename($formPath, '.xml');

			$element->forms->set($groupName, $form);

		}

		return true;
	}
}