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
JHtml::_('formbehavior.chosen', 'select');

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

<form action="<?php echo JRoute::_('index.php?option=com_fieldsandfilters&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="field-form" class="form-validate">
	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_FIELDSANDFILTERS_DETAILS_FIELDSET_LABEL', true)); ?>
		<div class="row-fluid">
			<div class="span4">
				<?php
				// Set main fields.
				$this->fields = array(
					'type',
					'content_type_id',
					'state',
					'required',
					'access',
					// 'language',
					'id'
				);

				echo JLayoutHelper::render('joomla.edit.global', $this);
				?>

			</div>
			<div class="span8">
				<div class="control-group">
					<?php echo $this->form->getLabel('description'); ?>
					<?php echo $this->form->getInput('description'); ?>
				</div>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo $this->loadTemplate('values'); ?>
		<?php echo $this->loadTemplate('params'); ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</div>

	<input type="hidden" id="jform_temp_type" name="jform[temp_type]" value="" />
	<input type="hidden" id="jform_temp_extension" name="jform[temp_extension]" value="" />
	<input type="hidden" name="task" value="" />
	<?php echo $this->form->getInput('mode'); ?>
	<?php echo JHtml::_('form.token'); ?>
</form>