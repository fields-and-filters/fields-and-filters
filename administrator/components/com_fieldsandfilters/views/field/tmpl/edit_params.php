<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

$fieldSets = $this->form->getFieldsets('params');

foreach ($fieldSets AS $name => $fieldSet)
{
	if (isset($fieldSet->parent) && isset($fieldSets[$fieldSet->parent]))
	{
		$position         = (isset($fieldSet->position) ? $fieldSet->position : 'before');
		$fieldSet->parent = $fieldSets[$fieldSet->parent]->name;

		$fieldSets[$fieldSet->parent]->children->{$position}[$name] = $fieldSet;
		unset($fieldSets[$name]);
	}
}

foreach ($fieldSets AS $name => $fieldSet)
{
	echo JHtml::_('bootstrap.addTab', 'myTab', 'params_' . $name, JText::_('COM_FIELDSANDFILTERS_' . strtoupper($name) . '_FIELDSET_LABEL', true));

	if (isset($fieldSet->children->before))
	{
		$this->set('fieldset_children', $fieldSet->children->before);
		echo JLayoutHelper::render('joomla.edit.fieldset_children', $this);
	}

	$this->set('fieldset', $name);
	echo JLayoutHelper::render('joomla.edit.fieldset', $this);

	if (isset($fieldSet->children->after))
	{
		$this->set('fieldset_children', $fieldSet->children->after);
		echo JLayoutHelper::render('joomla.edit.fieldset_children', $this);
	}

	echo JHtml::_('bootstrap.endTab');
}