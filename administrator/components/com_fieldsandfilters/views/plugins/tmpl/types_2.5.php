<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

$document = JFactory::getDocument();
$app = JFactory::getApplication();
// Checking if loaded via index.php or component.php
$recordId = $app->input->get('recordId', 0, 'int');
$tmpl = $app->input->get('tmpl', '', 'cmd');
$option = $app->input->get('option');

// Import CSS
JHtml::_('stylesheet', 'fieldsandfilters/administrator/fieldsandfilters.css', array(), true);
?>

<script type="text/javascript">
	setType = function (type) {
		<?php if ($tmpl) : ?>
		window.parent.Joomla.submitbutton('field.setType', type);
		window.parent.SqueezeBox.close();
		<?php else : ?>
		window.location = "index.php?option=com_fieldsandfilters&view=field&task=field.setType&layout=edit&type=" + ('field.setType', type);
		<?php endif; ?>
	}
</script>

<!-- Header -->
<header class="header <?php echo $option; ?>">
	<?php echo(isset($app->JComponentTitle) ? $app->JComponentTitle : ''); ?>
</header>

<?php echo JHtml::_('sliders.start'); ?>
<?php foreach ($this->plugins->toObject() AS $nameGroup => $pluginTypes) : ?>
	<?php
	$pluginType = current($pluginTypes);
	KextensionsLanguage::load('plg_' . $pluginType->type . '_' . $pluginType->name, JPATH_ADMINISTRATOR);
	$group = $pluginType->forms->get($nameGroup)->group;
	echo JHtml::_('sliders.panel', JText::_($group->title), 'type' . $nameGroup);
	?>
	<ul class="nav-tabs <?php echo $option; ?> j25">
		<?php foreach ($pluginTypes AS &$type) : ?>
			<?php
			if ($pluginType != $type) :
				KextensionsLanguage::load('plg_' . $type->type . '_' . $type->name, JPATH_ADMINISTRATOR);
			endif;

			$form = $type->forms->get($nameGroup);

			$options = array(
				'id'        => $recordId,
				'type'      => $type->type,
				'name'      => $type->name,
				'type_mode' => $nameGroup
			);
			?>
			<li>
				<a class="choose_type" href="#" title="<?php echo $this->escape($form->description); ?>"
					onclick="javascript:setType('<?php echo base64_encode(json_encode($options)); ?>')">
					<?php if ($document->direction != 'rtl') : ?>
						<?php echo $this->escape(JText::_($form->title)); ?>
						<small class="muted">
							<?php echo $this->escape(JText::_($form->description)); ?>
						</small>
					<?php else : ?>
						<small class="muted">
							<?php echo $this->escape(JText::_($form->description)); ?>
						</small>
						<?php echo $this->escape(JText::_($form->title)); ?>
					<?php endif ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endforeach; ?>
<?php echo JHtml::_('sliders.end'); ?>