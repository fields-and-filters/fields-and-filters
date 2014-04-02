<?php
/**
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_field_type.list
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

$field = $plugin->field;
$values = $field->values;
$connections = $plugin->element->connections->get($field->id, array());

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
?>

<?php if (!empty($connections)) : ?>
	<div id="faf-field-<?php echo $field->id; ?>" class="faf-field faf-field-list <?php echo htmlspecialchars($field->params->get('base.class', '')); ?>">
		<?php if ($field->params->get('base.show_name', 1)) :

			$attribsDiv = array('class' => 'faf-name');

			if ($isDescriptionTip)
			{
				JHtml::_('behavior.tooltip', '.faf-hasTip');
				$attribsDiv['class'] = $attribsDiv['class'] . ' faf-hasTip';
				$attribsDiv['title'] = htmlspecialchars(trim($field->name, ':') . '::' . $field->description, ENT_COMPAT, 'UTF-8');

			}
			?>

			<div <?php echo JArrayHelper::toString($attribsDiv); ?>>
				<?php echo($field->params->get('base.prepare_name', 0) ? $field->name : htmlspecialchars($field->name, ENT_QUOTES, 'UTF-8')); ?>
			</div>
		<?php endif; ?>

		<?php if ($isDescriptionBefore) : ?>
			<div class="faf-description">
				<?php echo $field->description; ?>
			</div>
		<?php endif; ?>

		<ul class="faf-list">
			<?php foreach ($connections AS &$connection) : ?>
				<?php if ($value = $values->get($connection)) : ?>
					<li>
						<?php echo($field->params->get('type.prepare_values', 0) ? $value->value : htmlspecialchars($value->value, ENT_QUOTES, 'UTF-8')); ?>
					</li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>

		<?php if ($isDescriptionAfter) : ?>
			<div class="faf-description">
				<?php echo $field->description; ?>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>