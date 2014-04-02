<?php

defined('_JEXEC') or die;

$app  = JFactory::getApplication();
$form = $displayData->getForm();

$fieldSets = $displayData->get('fieldset_children');

if (empty($fieldSets))
{
	return;
}

echo JHtml::_('bootstrap.startAccordion', 'menuParmasType');

foreach ($fieldSets AS $name => $fieldSet)
{
	$displayData->set('fieldset', $name);
	$id = (isset($fieldSet->parent) ? $fieldSet->parent . '_' : '') . $name;

	echo JHtml::_('bootstrap.addSlide', 'menuOptions', JText::_('COM_FIELDSANDFILTERS_' . strtoupper($id) . '_FIELDSET_LABEL', true), $id);
	echo JLayoutHelper::render('joomla.edit.fieldset', $displayData);
	echo JHtml::_('bootstrap.endSlide');
}

echo JHtml::_('bootstrap.endAccordion');