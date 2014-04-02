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

$sortFields = $this->getSortFields();
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
			<div id="filter-bar" class="btn-toolbar">
				<div class="filter-search btn-group pull-left">
					<label for="filter_search" class="element-invisible"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
					<input type="text" name="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_FIELDSANDFILTERS_FILTER_SEARCH_DESC'); ?>" />
				</div>
				<div class="btn-group pull-left hidden-phone">
					<button class="btn tip hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
						<i class="icon-search"></i></button>
					<button class="btn tip hasTooltip" type="button" onclick="document.id( 'filter_search' ).value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>">
						<i class="icon-remove"></i></button>
				</div>
				<div class="btn-group pull-right hidden-phone">
					<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>
				<div class="btn-group pull-right hidden-phone">
					<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></label>
					<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
						<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
						<option value="asc" <?php if ($listDirn == 'asc')
						{
							echo 'selected="selected"';
						} ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING'); ?></option>
						<option value="desc" <?php if ($listDirn == 'desc')
						{
							echo 'selected="selected"';
						} ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING'); ?></option>
					</select>
				</div>
				<div class="btn-group pull-right">
					<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY'); ?></label>
					<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
						<option value=""><?php echo JText::_('JGLOBAL_SORT_BY'); ?></option>
						<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
					</select>
				</div>
			</div>
			<div class="clearfix"></div>

			<table class="table table-striped" id="elementList">
				<thead>
				<tr>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'e.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>

					</th>
					<th width="1%" class="hidden-phone">
					</th>
					<th width="1%" style="min-width:55px" class="nowrap center">
						<?php echo JHtml::_('grid.sort', 'COM_FIELDSANDFILTERS_FIELDS_STATUS', 'e.state', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" style="min-width:55px" class="nowrap center">
						<?php echo JHtml::_('grid.sort', 'COM_FIELDSANDFILTERS_ELEMENTS_ITEM_ID', $this->state->get('query.item_id', 'e.item_id'), $listDirn, $listOrder); ?>
					</th>

					<?php if (isset($this->items[0]->item_name)) : ?>
						<th>
							<?php if ($queryItemName = $this->state->get('query.item_name')) : ?>
								<?php echo JHtml::_('grid.sort', 'COM_FIELDSANDFILTERS_ELEMENTS_ITEM_NAME', $queryItemName, $listDirn, $listOrder); ?>
							<?php else : ?>
								<?php echo JText::_('COM_FIELDSANDFILTERS_ELEMENTS_ITEM_NAME'); ?>
							<?php endif; ?>
						</th>
					<?php endif; ?>

					<?php if (isset($this->items[0]->item_category)) : ?>
						<th>
							<?php if ($queryItemCategory = $this->state->get('query.item_category')) : ?>
								<?php echo JHtml::_('grid.sort', 'COM_FIELDSANDFILTERS_ELEMENTS_ITEM_CATEGORY', $queryItemCategory, $listDirn, $listOrder); ?>
							<?php else : ?>
								<?php echo JText::_('COM_FIELDSANDFILTERS_ELEMENTS_ITEM_CATEGORY'); ?>
							<?php endif; ?>
						</th>
					<?php endif; ?>

					<th class="center hidden-phone">
						<?php echo JHtml::_('grid.sort', 'COM_FIELDSANDFILTERS_ELEMENTS_EXTENSION_TYPE', 'e.content_type_id', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', 'COM_FIELDSANDFILTERS_ELEMENTS_ELEMENT_ID', 'e.id', $listDirn, $listOrder); ?>
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
					?>
					<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo(!empty($item->content_type_id) ? $item->content_type_id : 0); ?>">
						<td class="order nowrap center hidden-phone">
							<?php
							if ($canChange && !empty($item->id)) :
								$disableClassName = '';
								$disabledLabel    = '';

								if (!$saveOrder) :
									$disabledLabel    = JText::_('JORDERINGDISABLED');
									$disableClassName = 'inactive tip-top';
								endif; ?>
								<span class="sortable-handler hasTooltip <?php echo $disableClassName; ?>" title="<?php echo $disabledLabel; ?>">
							<i class="icon-menu"></i>
						</span>
								<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
							<?php else : ?>
								<span class="sortable-handler inactive">
							<i class="icon-menu"></i>
						</span>
							<?php endif; ?>
						</td>
						<td class="order nowrap center hidden-phone">
							<?php echo JHtml::_('grid.id', $i, $item->id, empty($item->id)); ?>
						</td>
						<td class="center">
							<?php if (!empty($item->id)) : ?>
								<div class="btn-group">
									<?php echo JHtml::_('jgrid.published', $item->state, $i, 'elements.', false, 'cb'); ?>
								</div>
							<?php endif; ?>
						</td>
						<td class="center hidden-phone">
							<?php if (!is_null($item->item_id)) : ?>
								<?php echo (int) $item->item_id; ?>
							<?php endif; ?>
						</td>
						<?php if (isset($this->items[0]->item_name)) : ?>
							<td class="nowrap has-context">
								<div class="pull-left">
									<?php if ($canEdit) : ?>
										<?php if (!empty($item->id)) : ?>
											<a href="<?php echo JRoute::_('index.php?option=com_fieldsandfilters&task=element.edit&id=' . (int) $item->id); ?>">
												<?php echo $this->escape($item->item_name); ?>
											</a>
										<?php else: ?>
											<a href="<?php echo JRoute::_('index.php?option=com_fieldsandfilters&task=element.edit&etid=' . (int) $this->state->get('filter.content_type_id') . '&itid=' . (int) $item->item_id); ?>">
												<?php echo $this->escape($item->item_name); ?>
											</a>
										<?php endif; ?>
									<?php else : ?>
										<?php echo $this->escape($item->item_name); ?>
									<?php endif; ?>

									<?php if (isset($this->items[0]->item_alias)) : ?>
										<p class="smallsub">
											<?php echo JText::sprintf($this->state->get('text.alias', 'JGLOBAL_LIST_ALIAS'), $this->escape($item->item_alias)); ?>
										</p>
									<?php endif; ?>
								</div>
								<div class="pull-left">
									<?php
									if (!empty($item->id)) :
										// Create dropdown items
										JHtml::_('dropdown.edit', $item->id, 'element.');
									endif;

									// Render dropdown list
									echo JHtml::_('dropdown.render');
									?>
								</div>
							</td>
						<?php endif; ?>
						<?php if (isset($this->items[0]->item_category)) : ?>
							<td>
								<?php echo $this->escape($item->item_category); ?>
							</td>
						<?php endif; ?>
						<td class="center hidden-phone">
							<?php if ($extension = $extensionsHelper->getExtensionsPivot('content_type_id', true)->get((int) $this->state->get('filter.content_type_id', 0))) : ?>
								<?php echo JText::_($extension->forms->extension->title); ?>
							<?php else: ?>
								<?php echo JText::_('JUNDEFINED'); ?>
							<?php endif; ?>
						</td>
						<td class="center hidden-phone">
							<?php if (!is_null($item->id)) : ?>
								<?php echo (int) $item->id; ?>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<?php echo $this->pagination->getListFooter(); ?>

			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
</form>