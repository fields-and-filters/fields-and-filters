<?php
/**
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_type.date
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

$field = $plugin->field;
$data = $plugin->element->data->get($field->id);
?>

<?php if (!empty($data)) : ?>
	<div id="faf-field-<?php echo $field->id; ?>" class="faf-field faf-field-date <?php echo htmlspecialchars($field->params->get('base.class', '')); ?>">
		<?php
		$format = $field->params->get('type.format', 'l, d F Y');
		echo JHtml::_('date', $data, ($format != 'custom' ? $format : $field->params->get('type.format_custom', 'l, d F Y')));
		?>
	</div>
<?php endif; ?>