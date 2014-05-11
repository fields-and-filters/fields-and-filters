<?php
/**
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_field_type.image
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

$document = JFactory::getDocument();
$field    = $plugin->field;
$image    = $field->data;

$createThumb = $field->params->get('type.create_thumb');

$src = false;

if ($field->params->get('type.scale') && ($src = $image->get('src')) && file_exists(JPath::clean(JPATH_ROOT.'/'.$src)))
{
	$src = JPath::clean($src, '/');
}
elseif (($src = $image->get('image')) && file_exists(JPath::clean(JPATH_ROOT.'/'.$src)))
{
	$src = JPath::clean($src, '/');
}

$src_thumb = false;
if ($src && $createThumb && ($src_thumb = $image->get('src_thumb')) && file_exists(JPath::clean(JPATH_ROOT.'/'.$src_thumb)))
{
	$src_thumb = JPath::clean($src_thumb, '/');
}
elseif ($src && $createThumb)
{
	$src_thumb = $src;
}

if ($src)
{
	$title   = htmlspecialchars($image->get('alt', $document->getTitle()));
	$attribs = array(
		'id'    => 'faf-field-'.$field->id,
		'class' => 'faf-field faf-field-image faf-image'.($src_thumb ? 'faf-modal ' : 'faf-image ').htmlspecialchars($field->params->get('base.class', '')),
		'alt'   => $title
	);

	if ($caption = $image->get('caption'))
	{
		$attribs['class'] = $attribs['class'].' caption';
		$attribs['title'] = htmlspecialchars($caption);
	}

	$imageHTML = JHtml::image(htmlspecialchars($src), $title, $attribs);

	if ($src_thumb)
	{
		JHtml::_('behavior.modal', 'a.faf-modal');
		echo JHtml::link(htmlspecialchars($src), JHtml::image(htmlspecialchars($src_thumb), $title, array('class' => 'faf-image')), $attribs);
	}
	elseif ($link = $image->get('link'))
	{
		switch ($image->def('target', $field->params->get('base.target')))
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
				$attribs['class'] = $attribs['class'].' faf-modal';
				$attribs['rel']   = "{handler: 'iframe', size: {x:600, y:600}}";
				break;
			case 4:
			default:
				// open in parent window
				$attribs['rel'] = 'nofollow';
		}

		echo JHtml::link(htmlspecialchars($link), $imageHTML, $attribs);
	}
	else
	{
		echo $imageHTML;
	}
}