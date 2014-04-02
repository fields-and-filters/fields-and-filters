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
?>

<?php if (!empty($connections)) : ?>
	<ul id="faf-field-<?php echo $field->id; ?>" class="faf-field faf-field-list faf-list <?php echo htmlspecialchars($field->params->get('base.class', '')); ?>">
		<?php foreach ($connections AS &$connection) : ?>
			<?php if ($value = $values->get($connection)) : ?>
				<li>
					<?php echo($field->params->get('type.prepare_values', 0) ? $value->value : htmlspecialchars($value->value, ENT_QUOTES, 'UTF-8')); ?>
				</li>
			<?php endif; ?>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>