<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kulka.tomek@gmail.com> -
 */


// no direct access
defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');

// Import CSS
JHtml::_('stylesheet', 'fieldsandfilters/administrator/fieldsandfilters.css', array(), true);

$app = JFactory::getApplication();
$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$canOrder = $user->authorise('core.edit.state', 'com_fieldsandfilters');
$saveOrder = $listOrder == 'fv.ordering';
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_fieldsandfilters&task=fieldvalues.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'fieldvalueList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
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

<form action="<?php echo JRoute::_('index.php?option=com_fieldsandfilters&view=fieldvalues'); ?>" method="post" name="adminForm" id="adminForm">
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
				<table class="table table-striped" id="fieldvalueList">
					<thead>
					<tr>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('searchtools.sort', '', 'fv.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
						</th>
						<th width="1%" class="hidden-phone">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th width="1%" style="min-width:55px" class="nowrap center">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'fv.state', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JHtml::_('searchtools.sort', 'COM_FIELDSANDFILTERS_FIELDVALUES_FIELD_VALUE', 'fv.value', $listDirn, $listOrder); ?>
						</th>
						<th class="hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_FIELDSANDFILTERS_FIELDVALUES_FIELD', 'f.field_name', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_FIELDSANDFILTERS_FIELDVALUES_FIELD_VALUE_ID', 'fv.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($this->items as $i => $item) :
						$ordering   = ($listOrder == 'fv.ordering');
						$canCreate  = $user->authorise('core.create', 'com_fieldsandfilters');
						$canEdit    = $user->authorise('core.edit', 'com_fieldsandfilters');
						$canCheckin = $user->authorise('core.manage', 'com_fieldsandfilters');
						$canChange  = $user->authorise('core.edit.state', 'com_fieldsandfilters');
						?>
						<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->field_id; ?>">
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
							<td class="center hidden-phone">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="center">
								<div class="btn-group">
									<?php echo JHtml::_('jgrid.published', $item->state, $i, 'fieldvalues.', $canChange, 'cb'); ?>

									<?php
									$action = $item->state ? 'unpublish' : 'publish';
									JHtml::_('actionsdropdown.' . $action, 'cb' . $i, 'fieldvalues');

									// Render dropdown list
									echo JHtml::_('actionsdropdown.render');
									?>
								</div>
							</td>
							<td class="nowrap has-context">
								<div class="pull-left">
									<?php if ($canEdit) : ?>
										<a href="<?php echo JRoute::_('index.php?option=com_fieldsandfilters&task=fieldvalue.edit&id=' . (int) $item->id); ?>">
											<?php echo $this->escape($item->value); ?>
										</a>
									<?php else : ?>
										<?php echo $this->escape($item->value); ?>
									<?php endif; ?>
									<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
								</div>
							</td>

							<td class="hidden-phone">
								<?php echo $this->escape($item->field_name); ?>
							</td>

							<td class="center hidden-phone">
								<?php echo (int) $item->id; ?>
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