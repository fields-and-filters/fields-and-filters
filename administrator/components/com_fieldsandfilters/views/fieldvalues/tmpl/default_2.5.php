<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kulka.tomek@gmail.com> -
 */


// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHTML::_('script', 'system/multiselect.js', false, true);

// Import CSS
JHtml::_('stylesheet', 'fieldsandfilters/administrator/fieldsandfilters.css', array(), true);

$app = JFactory::getApplication();
$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$canOrder = $user->authorise('core.edit.state', 'com_fieldsandfilters');
$saveOrder = $listOrder == 'fv.ordering';
?>
<form action="<?php echo JRoute::_('index.php?option=com_fieldsandfilters&view=fieldvalues'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('Search'); ?>" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>

		<div class='filter-select fltrt'>
			<select name="filter[field_id]" class="inputbox" onchange="this.form.submit()">
				<?php echo JHtml::_('select.options', JHtml::_('FieldsandfiltersHtml.options.fields'), 'value', 'text', $this->state->get('filter.field_id'), false); ?>
			</select>
			<select name="filter[state]" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
				<?php echo JHtml::_('select.options', JHtml::_('FieldsandfiltersHtml.options.states', array('adminonly' => false)), "value", "text", $this->state->get('filter.state'), true); ?>
			</select>
		</div>
	</fieldset>
	<div class="clr"></div>

	<table class="adminlist">
		<thead>
		<tr>
			<th width="1%">
				<input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
			</th>

			<th class='left'>
				<?php echo JHtml::_('grid.sort', 'COM_FIELDSANDFILTERS_FIELDVALUES_FIELD_VALUE', 'fv.value', $listDirn, $listOrder); ?>
			</th>

			<th class='left'>
				<?php echo JHtml::_('grid.sort', 'COM_FIELDSANDFILTERS_FIELDVALUES_FIELD', 'f.field_name', $listDirn, $listOrder); ?>
			</th>

			<th width="5%">
				<?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'fv.state', $listDirn, $listOrder); ?>
			</th>

			<th width="10%">
				<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', 'fv.ordering', $listDirn, $listOrder); ?>
				<?php if ($canOrder && $saveOrder) : ?>
					<?php echo JHtml::_('grid.order', $this->items, 'filesave.png', 'fieldvalues.saveorder'); ?>
				<?php endif; ?>
			</th>

			<th width="1%" class="nowrap">
				<?php echo JHtml::_('grid.sort', 'COM_FIELDSANDFILTERS_FIELDVALUES_FIELD_VALUE_ID', 'fv.id', $listDirn, $listOrder); ?>
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
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>

				<td>
					<?php if ($canEdit) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_fieldsandfilters&task=fieldvalue.edit&id=' . (int) $item->id); ?>">
							<?php echo $this->escape($item->value); ?>
						</a>
					<?php else : ?>
						<?php echo $this->escape($item->value); ?>
					<?php endif; ?>
					<p class="smallsub">
						<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
					</p>
				</td>

				<td>
					<?php echo $this->escape($item->field_name); ?>
				</td>

				<td class="center">
					<?php echo JHtml::_('jgrid.published', $item->state, $i, 'fieldvalues.', $canChange, 'cb'); ?>
				</td>

				<td class="order">
					<?php if ($canChange) : ?>
						<?php if ($saveOrder) : ?>
							<?php if ($listDirn == 'asc') : ?>
								<span><?php echo $this->pagination->orderUpIcon($i, true, 'fieldvalues.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
								<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'fieldvalues.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
							<?php elseif ($listDirn == 'desc') : ?>
								<span><?php echo $this->pagination->orderUpIcon($i, true, 'fieldvalues.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
								<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'fieldvalues.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
							<?php endif; ?>
						<?php endif; ?>
						<?php $disabled = $saveOrder ? '' : 'disabled="disabled"'; ?>
						<input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>" <?php echo $disabled ?> class="text-area-order" />
					<?php else : ?>
						<?php echo $item->ordering; ?>
					<?php endif; ?>
				</td>
				<td class="center">
					<?php echo (int) $item->id; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
		<tfoot>
		<tr>
			<td colspan="10">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
		</tfoot>
	</table>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>