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
		if (task == 'fieldvalue.cancel' || document.formvalidator.isValid(document.id('fieldvalue-form'))) {
			Joomla.submitform(task, document.getElementById('fieldvalue-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_fieldsandfilters&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="fieldvalue-form" class="form-validate">

	<div class="form-horizontal">
		<?php echo JLayoutHelper::render('joomla.edit.fieldset', $this); ?>
	</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>