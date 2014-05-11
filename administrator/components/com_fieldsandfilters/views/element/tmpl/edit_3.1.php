<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kulka.tomek@gmail.com> -
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

// Import CSS
JHtml::_('stylesheet', 'fieldsandfilters/administrator/fieldsandfilters.css', array(), true);
?>

<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'element.cancel' || document.formvalidator.isValid(document.id('element-form'))) {
			Joomla.submitform(task, document.getElementById('element-form'));
		}
		else {
			alert('<?php echo $this->escape( JText::_( 'JGLOBAL_VALIDATION_FORM_FAILED' ) ); ?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_fieldsandfilters&layout=edit&eid=' . (int) $this->item->get('element_id')); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="element-form" class="form-validate form-horizontal">

	<fieldset>
		<legend>
			<?php if ($extensionTitle = $this->state->get('element.extension_title')) : ?>
				<?php echo JText::_($extensionTitle); ?>:
			<?php endif; ?>
			<?php echo $this->escape($this->item->get('item_name', '')); ?>
			(<?php echo (int) $this->item->get('item_id'); ?>)
		</legend>
		<div class="row-fluid">
			<?php foreach ($this->form->getFieldset('fields') AS $name => $field) : ?>
				<?php if (strtolower($field->type) == 'hidden') : ?>
					<?php echo $field->input; ?>
				<?php else : ?>
					<div class="control-group">
						<?php echo $field->label; ?>
						<div class="controls">
							<?php echo $field->input; ?>
						</div>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</fieldset>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->form->getInput('id'); ?>
	<?php echo $this->form->getInput('content_type_id'); ?>
	<?php echo $this->form->getInput('item_id'); ?>
	<?php echo $this->form->getInput('state'); ?>
</form>