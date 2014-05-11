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

$this->set('fieldset', 'fieldsandfilters');
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

<form action="<?php echo JRoute::_('index.php?option=com_fieldsandfilters&layout=edit&eid=' . (int) $this->item->get('element_id')); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="element-form" class="form-validate">

	<div class="form-horizontal">
		<legend>
			<?php if ($extensionTitle = $this->item->get('extension_name')) : ?>
				<?php echo JText::_($extensionTitle); ?>:
			<?php endif; ?>
			<?php echo $this->escape($this->item->get('item_name', '')); ?>
			(<?php echo (int) $this->item->get('item_id'); ?>)
		</legend>

		<?php echo JLayoutHelper::render('joomla.edit.fieldset', $this); ?>
	</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->form->getInput('id'); ?>
	<?php echo $this->form->getInput('content_type_id'); ?>
	<?php echo $this->form->getInput('item_id'); ?>
	<?php echo $this->form->getInput('state'); ?>
</form>