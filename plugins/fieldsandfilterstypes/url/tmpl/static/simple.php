<?php
/**
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_field_type.url
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

$field = $plugin->field;
$data  = $field->data;

if ($data->get('url'))
{
	$attribs = array(
		'id'    => 'faf-field-' . $field->id,
		'class' => 'faf-field faf-field-url ' . htmlspecialchars($field->params->get('base.class', '')),
		'alt'   => $data->get('alt')

	);

	switch ($data->def('target', $field->params->get('base.target')))
	{
		case 1:
			// open in a new window
			$attribs['target'] = '_blank';
			$attribs['rel']    = 'nofollow';
			break;
		case 2:
			// open in a popup window
			$attribs['onclick'] = "window.open(this.href, 'targetWindow', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=600,height=600'); return false;";
			break;
		case 3:
			// open in a modal window
			JHtml::_('behavior.modal', 'a.faf-modal');
			$attribs['class'] = $attribs['class'] . ' faf-modal';
			$attribs['rel']   = "{handler: 'iframe', size: {x:600, y:600}}";
			break;
		case 4:
		default:
			// open in parent window
			$attribs['rel'] = 'nofollow';
	}
	echo JHtml::link(htmlspecialchars($data->get('url')), htmlspecialchars($data->get('title', JFactory::getDocument()->getTitle())), $attribs);
}