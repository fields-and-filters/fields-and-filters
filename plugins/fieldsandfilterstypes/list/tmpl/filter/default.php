<?php
/**
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_type.list
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

$field = $plugin->field;
$values = $field->values;

$isDescriptionTip = $isDescriptionBefore = $isDescriptionAfter = false;
if ($field->params->get('base.site_enabled_description', 0) && !empty($field->description))
{
	switch ($field->params->get('base.site_description_type', 0))
	{
		case 'description':
			switch ($field->params->get('base.site_description_position', 0))
			{
				case 'before':
					$isDescriptionBefore = true;
					break;
				case 'after':
					$isDescriptionAfter = true;
					break;
			}
			break;
		case 'tip':
			$isDescriptionTip = true;
			break;
	}
}

$id = 'faf-filters-' . $field->id;
?>

<fieldset id="<?php echo $id; ?>" data-alias="<?php echo $field->alias; ?>" class="faf-filters faf-filters-list <?php echo htmlspecialchars($field->params->get('base.class', '')); ?>">
	<?php if ($field->params->get('base.show_name', 1)) :

		$attribsDiv = array('class' => 'faf-name');

		if ($isDescriptionTip)
		{
			JHtml::_('behavior.tooltip', '.faf-hasTip');
			$attribsDiv['class'] = $attribsDiv['class'] . ' faf-hasTip';
			$attribsDiv['title'] = htmlspecialchars(trim($field->name, ':') . '::' . $field->description, ENT_COMPAT, 'UTF-8');

		}
		?>

		<legend <?php echo JArrayHelper::toString($attribsDiv); ?>>
			<?php echo($field->params->get('base.prepare_name', 0) ? $field->name : htmlspecialchars($field->name, ENT_QUOTES, 'UTF-8')); ?>
		</legend>
	<?php endif; ?>

	<?php if ($isDescriptionBefore) : ?>
		<div class="faf-description">
			<?php echo $field->description; ?>
		</div>
	<?php endif; ?>

	<?php foreach ($values AS &$value) : ?>
		<div class="control-group faf-control-group">
			<input type="checkbox" name="fieldsandfilters[<?php echo $field->id; ?>][]" id="<?php echo($id . '-' . $value->id); ?>"
				class="faf-filters-input inputbox" value="<?php echo $value->id; ?>" data-ordering="<?php echo $value->ordering; ?>"
				data-alias="<?php echo htmlspecialchars($value->alias); ?>" />

			<label for="<?php echo($id . '-' . $value->id); ?>" class="checkbox">
				<?php echo($field->params->get('type.prepare_values', 0) ? $value->value : htmlspecialchars($value->value, ENT_QUOTES, 'UTF-8')); ?>
				<span class="faf-filters-count badge"></span>
			</label>
		</div>
	<?php endforeach; ?>


	<?php if ($isDescriptionAfter) : ?>
		<div class="faf-description">
			<?php echo $field->description; ?>
		</div>
	<?php endif; ?>
</fieldset>