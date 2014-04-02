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

// Import CSS
JHtml::_('stylesheet', 'fieldsandfilters/administrator/fieldsandfilters.css', array(), true);
?>

<script type="text/javascript">
	setType = function (type) {
		<?php if ($tmpl) : ?>
		window.parent.Joomla.submitbutton('field.setExtension', type);
		window.parent.SqueezeBox.close();
		<?php else : ?>
		window.location = "index.php?option=com_fieldsandfilters&view=field&task=field.setExtension&layout=edit&type=" + ('field.setExtension', type);
		<?php endif; ?>
	}
</script>

<!-- Header -->
<header class="header">
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span10">
				<?php if (isset($app->JComponentTitle)) : ?>
					<h1 class="page-title"><?php echo JHtml::_('string.truncate', $app->JComponentTitle, 0, false, false); ?></h1>
				<?php else : ?>
					<h1 class="page-title"><?php echo JHtml::_('string.truncate', '', 0, false, false); ?></h1>
				<?php endif; ?>
			</div>
		</div>
	</div>
</header>

<?php foreach ($this->plugins->toObject() AS $nameGroup => $pluginExtensions) : ?>
	<ul class="nav nav-tabs nav-stacked">
		<?php foreach ($pluginExtensions AS &$extension) : ?>
			<?php
			KextensionsLanguage::load('plg_' . $extension->type . '_' . $extension->name, JPATH_ADMINISTRATOR);
			$form = $extension->forms->get($nameGroup);

			$options = array(
				'id'                 => $recordId,
				'form'               => $nameGroup,
				'content_type_id'    => $extension->content_type_id,
				'content_type_alias' => $extension->content_type_alias
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
