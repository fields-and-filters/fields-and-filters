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

$options = array();
$options[] = JHtml::_('select.option', '', JText::_('JGLOBAL_SELECT_AN_OPTION'), array(
	'option.attr' => 'option.attr',
	'attr'        => array(
		'class'        => 'faf-filters-input inputbox',
		'data-default' => 1
	)
));
foreach ($field->values AS $value)
{
	$options[] = JHtml::_('select.option', (string) $value->id, (string) $value->value . '(0)', array(
		'option.attr' => 'option.attr',
		'attr'        => array(
			'class'      => 'faf-filters-input inputbox',
			'data-alias' => htmlspecialchars($value->alias)
		)
	));
}

$id = 'faf-filters-' . $field->id;
?>

<fieldset id="<?php echo $id; ?>" data-alias="<?php echo $field->alias; ?>" class="faf-filters faf-filters-selectlist <?php echo htmlspecialchars($field->params->get('base.class', '')); ?>">
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

	<div class="control-group faf-control-group">
		<?php
		echo JHtml::_('select.genericlist', $options, 'fieldsandfilters[' . $field->id . ']', array(
			'id'          => $id . '-select',
			'list.attr'   => array(
				'class' => 'faf-filters-select inputbox chzn-done'
			),
			'option.attr' => 'option.attr'
		));
		?>
	</div>

	<?php if ($isDescriptionAfter) : ?>
		<div class="faf-description">
			<?php echo $field->description; ?>
		</div>
	<?php endif; ?>
</fieldset>