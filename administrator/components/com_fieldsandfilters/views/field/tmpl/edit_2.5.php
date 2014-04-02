<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
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
	Joomla.submitbutton = function (task, type) {
		if (task == 'field.setType') {
			document.id('field-form').elements['jform[temp_type]'].value = type;
			Joomla.submitform('field.setType', document.id('field-form'));
		}
		else if (task == 'field.setExtension') {
			document.id('field-form').elements['jform[temp_extension]'].value = type;
			Joomla.submitform('field.setExtension', document.id('field-form'));
		}
		else if (task == 'field.cancel' || document.formvalidator.isValid(document.id('field-form'))) {
			Joomla.submitform(task, document.getElementById('field-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_fieldsandfilters&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="field-form" class="form-validate form-horizontal">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend>
				<?php echo JText::_('COM_FIELDSANDFILTERS_LEGEND_FIELD'); ?>
			</legend>
			<ul class="adminformlist">
				<li>
					<?php echo $this->form->getLabel('name'); ?>
					<?php echo $this->form->getInput('name'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('alias'); ?>
					<?php echo $this->form->getInput('alias'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('type'); ?>
					<?php echo $this->form->getInput('type'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('content_type_id'); ?>
					<?php echo $this->form->getInput('content_type_id'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('state'); ?>
					<?php echo $this->form->getInput('state'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('required'); ?>
					<?php echo $this->form->getInput('required'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('access'); ?>
					<?php echo $this->form->getInput('access'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('language'); ?>
					<?php echo $this->form->getInput('language'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('id'); ?>
					<?php echo $this->form->getInput('id'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('description'); ?>
					<div class="clr"></div>
					<?php echo $this->form->getInput('description'); ?>
				</li>
			</ul>
		</fieldset>
	</div>

	<div class="width-40 fltlft">
		<?php echo JHtml::_('sliders.start', 'permissions-sliders-' . $this->item->id, array('useCookie' => 1)); ?>
		<?php echo $this->loadTemplate('values_2.5'); ?>
		<?php echo $this->loadTemplate('params_2.5'); ?>
		<?php echo JHtml::_('sliders.end'); ?>
	</div>

	<input type="hidden" id="jform_temp_type" name="jform[temp_type]" value="" />
	<input type="hidden" id="jform_temp_extension" name="jform[temp_extension]" value="" />
	<input type="hidden" name="task" value="" />
	<?php echo $this->form->getInput('mode'); ?>
	<?php echo JHtml::_('form.token'); ?>
</form>