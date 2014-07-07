<?php
/**
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_field_type.textarea
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

if (JFactory::getConfig()->get('debug'))
{
	KextensionsLanguage::load('com_fieldsandfilters');
	echo JText::sprintf('COM_FIELDSANDFILTERS_ERROR_LAYOUT_PLUGIN_NOT_EXISTS', $plugin->field->params->get('type.field_layout'), $plugin->type . '/' . $plugin->name . '/' . $plugin->field->name);
}