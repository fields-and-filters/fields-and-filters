<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;
?>
<?php $fieldSets = $this->form->getFieldsets('values'); ?>
<?php foreach ($fieldSets AS $name => $fieldSet) : ?>
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', $name, JText::_('COM_FIELDSANDFILTERS_' . strtoupper($name) . '_FIELDSET_LABEL', true)); ?>

	<?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
		<p class="tip"><?php echo $this->escape(JText::_($fieldSet->description)); ?></p>
	<?php endif; ?>
	<?php foreach ($this->form->getFieldset($name) AS $field) : ?>
		<div class="control-group">
			<?php if (strpos((string) $field->labelClass, 'controls-disabled') !== false) : ?>
				<?php echo $field->label; ?>
				<?php echo $field->input; ?>
			<?php else : ?>
				<div class="control-label">
					<?php echo $field->label; ?>
				</div>
				<div class="controls">
					<?php echo $field->input; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
<?php endforeach; ?>