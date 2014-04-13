<?php
/**
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_type.textarea
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

$field = $plugin->field;
$data = $plugin->element->data->get($field->id);
?>

<?php if (!empty($data)) : ?>
	<div id="faf-field-<?php echo $field->id; ?>" class="faf-field faf-field-textarea <?php echo htmlspecialchars($field->params->get('base.class', '')); ?>">
		<?php echo ($field->params->get('type.display_editor', 1) || $field->params->get('type.prepare_data', 0) ? $data : htmlspecialchars($data, ENT_QUOTES, 'UTF-8')); ?>
	</div>
<?php endif; ?>