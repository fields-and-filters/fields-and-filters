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
class FieldsandfiltersHtmlFilters
{
	/**
	 * @var    array  Array containing information for loaded files
	 * @since  1.2.0
	 */
	protected static $loaded = array();

	/**
	 * Method to load the jQuery Fieldsandfilters filters JavaScript framework into the document head
	 *
	 * If debugging mode is on an uncompressed version of jQuery is included for easier debugging.
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	public static function framework($debug = null)
	{
		// Only load once
		if (!empty(static::$loaded[__METHOD__]))
		{
			return;
		}

		// If no debugging value is set, use the configuration setting
		if ($debug === null)
		{
			$config = JFactory::getConfig();
			$debug  = (boolean) $config->get('debug');
		}

		$params = JComponentHelper::getParams('com_fieldsandfilters');

		// Import CSS
		JHtml::_('stylesheet', 'fieldsandfilters/filters/filters.css', array(), true, false, false, false, false);

		$loadingBackground = $params->get('loading_background_filters', 'loading-balls');

		if ($loadingBackground != -1)
		{
			JHtml::_('stylesheet', 'fieldsandfilters/filters/' . $loadingBackground . '.css', array(), true, false, false, false);
		}

		// Import JS
		if (FieldsandfiltersFactory::isVersion())
		{
			JHtml::_('jquery.framework', (boolean) $params->get('load_noconflict_javascript', 1));
		}
        elseif ($params->get('load_jquery_javascript', 1))
		{
			JHtml::_('FieldsandfiltersHtml.joomla.jquery', (boolean) $params->get('load_noconflict_javascript', 1));
		}

		JHtml::_('script', 'fieldsandfilters/core/jquery.fieldsandfilters.js', false, true, false, false, $debug);

		static::$loaded[__METHOD__] = true;

		return;
	}
}
