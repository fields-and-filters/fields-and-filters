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

// Import CSS
JHtml::_('stylesheet', 'fieldsandfilters/administrator/fieldsandfilters.css', array(), true);
?>
<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'fieldvalue.cancel' || document.formvalidator.isValid(document.id('fieldvalue-form'))) {
			Joomla.submitform(task, document.getElementById('fieldvalue-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_fieldsandfilters&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="fieldvalue-form" class="form-validate form-horizontal">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_FIELDSANDFILTERS_LEGEND_FIELDVALUE'); ?></legend>
			<ul class="adminformlist">
				<li>
					<?php echo $this->form->getLabel('value'); ?>
					<?php echo $this->form->getInput('value'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('alias'); ?>
					<?php echo $this->form->getInput('alias'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('field_id'); ?>
					<?php echo $this->form->getInput('field_id'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('state'); ?>
					<?php echo $this->form->getInput('state'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('id'); ?>
					<?php echo $this->form->getInput('id'); ?>
				</li>
			</ul>
		</fieldset>
	</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	<div class="clr"></div>
</form>