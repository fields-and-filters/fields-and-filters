<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

$fieldSets = $this->form->getFieldsets('values');

foreach ($fieldSets AS $name => $fieldSet)
{
	echo JHtml::_('bootstrap.addTab', 'myTab', $name, JText::_('COM_FIELDSANDFILTERS_' . strtoupper($name) . '_FIELDSET_LABEL', true));

	if (isset($fieldSet->description) && trim($fieldSet->description))
	{
		echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
	}

	foreach ($this->form->getFieldset($name) AS $field)
	{
		if (strpos((string) $field->labelclass, 'controls-disabled') !== false)
		{
			echo '<div class="control-group">' . $field->label . $field->input . '</div>';
		}
		else
		{
			echo $field->getControlGroup();
		}

	}

	echo JHtml::_('bootstrap.endTab');
}