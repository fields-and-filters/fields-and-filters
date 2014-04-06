<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package           fieldsandfilters.administrator
 * @subpackage        com_fieldsandfilters
 *
 * @since             1.2.0
 */
abstract class FieldsandfiltersHtmlGrid
{
	/**
	 * @param   int $value The state value
	 * @param   int $i
	 *
	 * @since       1.0.0
	 *
	 * (task, text, title,html active class, HTML inactive class)
	 */
	public static function published($value = 0, $i, $prefix = '', $enabled = true, $checkbox = 'cb')
	{
		JHtml::_('bootstrap.tooltip');

		// Array of image, task, title, action
		$states = array(
			0  => array(
				'publish',
				'JUNPUBLISHED',
				'JLIB_HTML_PUBLISH_ITEM',
				'JUNPUBLISHED',
				true,
				'unpublish',
				'unpublish',
			),
			1  => array(
				'unpublish',
				'JPUBLISHED',
				'JLIB_HTML_UNPUBLISH_ITEM',
				'JPUBLISHED',
				true,
				'publish',
				'publish'
			),
			-1 => array(
				'publish',
				'COM_FIELDSANDFILTERS_HTML_ONLYADMIN',
				'JLIB_HTML_PUBLISH_ITEM',
				'COM_FIELDSANDFILTERS_HTML_ONLYADMIN',
				true,
				'dashboard',
				'dashboard'
			)
		);

		return JHtml::_('jgrid.state', $states, $value, $i, $prefix, $enabled, true, $checkbox);
	}

	/**
	 * @param   int $value The state value
	 * @param   int $i
	 *
	 * @since       1.0.0
	 */
	public static function required($value = 0, $i, $prefix = '', $enabled = true, $checkbox = 'cb')
	{
		JHtml::_('bootstrap.tooltip');

		// Array of image, task, title, action
		$states = array(
			0 => array(
				'star-empty',
				'required',
				'COM_FIELDSANDFILTERS_HTML_UNREQUIRED_ITEM',
				'COM_FIELDSANDFILTERS_HTML_TOGGLE_TO_REQUIRED_ITEM'
			),
			1 => array(
				'star',
				'unrequired',
				'COM_FIELDSANDFILTERS_HTML_REQUIRED_ITEM',
				'COM_FIELDSANDFILTERS_HTML_TOGGLE_TO_UNREQUIRED_ITEM'
			)
		);

		$state = JArrayHelper::getValue($states, (int) $value, $states[1]);

		if ($enabled)
		{
			$html[] = '<a href="#" class="btn btn-micro hasTooltip' . ($value == 1 ? ' active' : '') . '" title="' . JText::_($state[3]) . '"';
			$html[] = ' onclick="return listItemTask(\'' . $checkbox . $i . '\',\'' . $prefix . $state[1] . '\')">';
			$html[] = '	<i class="icon-' . $state[0] . '"> </i>';
			$html[] = '</a>';
		}
		else
		{
			$html[] = '<a class="btn btn-micro hasTooltip disabled' . ($value == 1 ? ' active' : '') . '" title="' . JText::_($state[2]) . '">';
			$html[] = '	<i class="icon-' . $state[0] . '"></i>';
			$html[] = '</a>';
		}

		return implode("\n", $html);
	}

	/**
	 * Method to generate html code for a list of buttons
	 *
	 * @param   array $buttons Array of buttons
	 *
	 * @return  string
	 *
	 * @since       1.1.0
	 */
	public static function buttons($buttons)
	{
		$html = array();
		foreach ($buttons as $button)
		{
			$html[] = self::button($button);
		}

		return implode($html);
	}

	/**
	 * Method to generate html code for a list of buttons
	 *
	 * @param   array|object $button Button properties
	 *
	 * @return  string
	 *
	 * @since       1.0.0
	 */
	public static function button($button)
	{
		$user = JFactory::getUser();
		if (!empty($button['access']))
		{
			if (is_bool($button['access']))
			{
				if ($button['access'] == false)
				{
					return '';
				}
			}
			else
			{
				// Take each pair of permission, context values.
				for ($i = 0, $n = count($button['access']); $i < $n; $i += 2)
				{
					if (!$user->authorise($button['access'][$i], $button['access'][$i + 1]))
					{
						return '';
					}
				}
			}
		}

		$html[] = '<div class="icon"' . (empty($button['id']) ? '' : (' id="' . $button['id'] . '"')) . '>';
		$html[] = '<a href="' . $button['link'] . '"';
		$html[] = (empty($button['target']) ? '' : (' target="' . $button['target'] . '"'));
		$html[] = (empty($button['onclick']) ? '' : (' onclick="' . $button['onclick'] . '"'));
		$html[] = (empty($button['title']) ? '' : (' title="' . htmlspecialchars($button['title']) . '"'));
		$html[] = '>';

        if (!empty($button['image']))
        {
            $html[] = '<i class="icon-'.$button['image'].(FieldsandfiltersFactory::isVersion('<') ? ' faf-j25' : '' ).'"></i>';
        }
        else
        {
		    $html[] = JHtml::_('image', empty($button['icon']) ? '' : $button['icon'], empty($button['alt']) ? null : htmlspecialchars($button['alt']), null, empty($button['relative']) ? false : (boolean) $button['relative']);
        }

		$html[] = (empty($button['text'])) ? '' : ('<span>' . $button['text'] . '</span>');
		$html[] = '</a>';
		$html[] = '</div>';

		return implode($html);
	}
}
