<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kulka.tomek@gmail.com> -
 */


// no direct access
defined('_JEXEC') or die;

// Load PluginExtensions Helper
$extensionsHelper = FieldsandfiltersFactory::getExtensions();

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');

// Import CSS
JHtml::_('stylesheet', 'fieldsandfilters/administrator/fieldsandfilters.css', array(), true);

$app = JFactory::getApplication();
$user = JFactory::getUser();
$filter = JFilterInput::getInstance();
$userId = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$canOrder = $user->authorise('core.edit.state', 'com_fieldsandfilters');
$saveOrder = $listOrder == 'e.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_fieldsandfilters&task=elements.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'elementList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>
<script type="text/javascript">
	Joomla.orderTable = function () {
		table = document.getElementById('sortTable');
		direction = document.getElementById('directionTable');
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		}
		else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_fieldsandfilters&view=elements'); ?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty($this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php else : ?>
		<div id="j-main-container">
			<?php endif; ?>
			<?php
			// Search tools bar
			echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
			?>
			<?php if (empty($this->items)) : ?>
				<div class="alert alert-no-items">
					<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
				</div>
			<?php else : ?>
				<table class="table table-striped" id="elementList">
					<thead>
					<tr>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('searchtools.sort', '', 'e.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
						</th>
						<th width="1%" style="min-width:55px" class="nowrap center">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', $this->state->get('list.query.item_state', 'e.state'), $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('searchtools.sort', 'COM_FIELDSANDFILTERS_ELEMENTS_ITEM_NAME', $this->state->get('list.query.item_name', ''), $listDirn, $listOrder); ?>
						</th>
						<?php if ($this->extensionDir) : ?>
							<?php echo $this->loadTemplate('thead'); ?>
						<?php endif; ?>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_FIELDSANDFILTERS_ELEMENTS_ITEM_ID', $this->state->get('list.query.item_id', 'e.item_id'), $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_FIELDSANDFILTERS_ELEMENTS_ELEMENT_ID', 'e.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($this->items as $i => $item) :
						$ordering   = ($listOrder == 'a.ordering');
						$canCreate  = $user->authorise('core.create', 'com_fieldsandfilters');
						$canEdit    = $user->authorise('core.edit', 'com_fieldsandfilters');
						$canCheckin = $user->authorise('core.manage', 'com_fieldsandfilters');
						$canChange  = $user->authorise('core.edit.state', 'com_fieldsandfilters');
						$this->item = $item;
						?>
						<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo(!empty($item->content_type_id) ? $item->content_type_id : 0); ?>">
							<td class="order nowrap center hidden-phone">
								<?php
								$iconClass = '';
								if (!$canChange)
								{
									$iconClass = ' inactive';
								}
								elseif (!$saveOrder)
								{
									$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
								}
								?>
								<span class="sortable-handler<?php echo $iconClass ?>">
								<i class="icon-menu"></i>
							</span>
								<?php if ($canChange && $saveOrder) : ?>
									<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
								<?php endif; ?>
							</td>
							<td class="center">
								<?php if (isset($item->id)) : ?>
									<div class="btn-group">
										<?php echo JHtml::_('jgrid.published', $item->state, $i, 'elements.', false, 'cb'); ?>
									</div>
								<?php elseif (isset($item->item_state)) : ?>
									<div class="btn-group">
										<?php echo JHtml::_('jgrid.published', $item->item_state, $i, 'elements.', false, 'cb'); ?>
									</div>
								<?php endif; ?>
							</td>
							<td class="has-context">
								<?php if (isset($item->item_name)) : ?>
									<div class="pull-left">
										<?php if ($canEdit) : ?>
											<?php if (!empty($item->id)) : ?>
												<a href="<?php echo JRoute::_('index.php?option=com_fieldsandfilters&task=element.edit&id=' . (int) $item->id); ?>">
													<?php echo $this->escape($item->item_name); ?>
												</a>
											<?php else: ?>
												<a href="<?php echo JRoute::_('index.php?option=com_fieldsandfilters&task=element.edit&ctid=' . (int) $this->state->get('filter.content_type_id') . '&itid=' . (int) $item->item_id); ?>">
													<?php echo $this->escape($item->item_name); ?>
												</a>
											<?php endif; ?>
										<?php else : ?>
											<?php echo $this->escape($item->item_name); ?>
										<?php endif; ?>

										<?php if (isset($item->item_alias)) : ?>
											<?php echo JText::sprintf($this->state->get('text.alias', 'JGLOBAL_LIST_ALIAS'), $this->escape($item->item_alias)); ?>
										<?php endif; ?>
										<?php if ($extension = $extensionsHelper->getExtensionsPivot('content_type_id', true)->get((int) $this->state->get('filter.content_type_id', 0))) : ?>
											<p class="smallsub">
												<?php echo JText::_('COM_FIELDSANDFILTERS_ELEMENTS_EXTENSION_TYPE') ?>: <?php echo JText::_($extension->forms->extension->title); ?>
											</p>
										<?php endif; ?>
									</div>
								<?php endif; ?>
							</td>

							<?php if ($this->extensionDir) : ?>
								<?php echo $this->loadTemplate('tbody'); ?>
							<?php endif; ?>
							<td class="center hidden-phone">
								<?php if (isset($item->item_id)) : ?>
									<?php echo (int) $item->item_id; ?>
								<?php endif; ?>
							</td>
							<td class="center hidden-phone">
								<?php if (isset($item->id)) : ?>
									<?php echo (int) $item->id; ?>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
			<?php echo $this->pagination->getListFooter(); ?>

			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
</form>