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

JHtml::_('behavior.tooltip');
JHTML::_('script', 'system/multiselect.js', false, true);

// Import CSS
JHtml::_('stylesheet', 'fieldsandfilters/administrator/fieldsandfilters.css', array(), true);

$app = JFactory::getApplication();
$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$canOrder = $user->authorise('core.edit.state', 'com_fieldsandfilters');
$saveOrder = $listOrder == 'e.ordering';
?>

<form action="<?php echo JRoute::_('index.php?option=com_fieldsandfilters&view=elements'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter[search]" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('Search'); ?>" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id( 'filter_search' ).value='';this.form.submit( );"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>

		<div class='filter-select fltrt'>
			<select name="filter[content_type_id]" id="filter_content_type_id" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('COM_FIELDSANDFILTERS_OPTION_SELECT_EXTENSION'); ?></option>
				<?php echo JHtml::_('select.options', JHtml::_('FieldsandfiltersHtml.options.extensions', array('allextensions')), 'value', 'text', $this->state->get('filter.content_type_id')); ?>
			</select>
			<select name="filter[state]" id="filter_state" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
				<?php echo JHtml::_('select.options', JHtml::_('FieldsandfiltersHtml.options.states'), 'value', 'text', $this->state->get('filter.state'), true); ?>
			</select>
			<select name="filter[empty]" id="filter_empty" class="inputbox" onchange="this.form.submit()">
				<?php echo JHtml::_('select.options', array(
					JHtml::_('select.option', 0, 'COM_FIELDSANDFILTERS_OPTION_SELECT_ELEMENT_NOT_EMPTY'),
					JHtml::_('select.option', 1, 'COM_FIELDSANDFILTERS_OPTION_SELECT_ELEMENT_EMPTY'),
				), 'value', 'text', $this->state->get('filter.empty', 0), true); ?>
			</select>

			<?php if (is_array($filtersOptions = $this->state->get('filters.options'))) : ?>
				<?php foreach ($filtersOptions AS $name => &$filter) : ?>
					<?php $filter = !is_array($filter) ? (array) $filter : $filter; ?>
					<select name="filter[<?php echo $name; ?>]" name="filter_<?php echo $name; ?>" class="inputbox" onchange="this.form.submit()">
						<option value=""><?php echo JArrayHelper::getValue($filter, 'label'); ?></option>
						<?php echo JHtml::_('select.options', JArrayHelper::getValue($filter, 'options', array(), 'array'), 'value', 'text', $this->state->get('filter.' . $name), true); ?>
					</select>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</fieldset>
	<div class="clr"></div>

	<table class="adminlist">
		<thead>
		<tr>
			<th>
				<?php echo JHtml::_('grid.sort', 'COM_FIELDSANDFILTERS_ELEMENTS_ITEM_ID', $this->state->get('query.item_id', 'e.item_id'), $listDirn, $listOrder); ?>
			</th>

			<?php if (isset($this->items[0]->item_name)) : ?>
				<th class="left">
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

			<th>
				<?php echo JHtml::_('grid.sort', 'COM_FIELDSANDFILTERS_ELEMENTS_EXTENSION_TYPE', 'e.content_type_id', $listDirn, $listOrder); ?>
			</th>

			<th>
				<?php echo JHtml::_('grid.sort', 'COM_FIELDSANDFILTERS_ELEMENTS_STATE', 'e.content_type_id', $listDirn, $listOrder); ?>
			</th>

			<th>
				<?php echo JHtml::_('grid.sort', 'COM_FIELDSANDFILTERS_ELEMENTS_ORDERING', 'e.content_type_id', $listDirn, $listOrder); ?>
			</th>

			<th>
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
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php if (!is_null($item->item_id)) : ?>
						<?php echo (int) $item->item_id; ?>
					<?php endif; ?>
				</td>
				<?php if (isset($this->items[0]->item_name)) : ?>
					<td>
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

						<?php if (isset($this->items[0]->item_alias)) : ?>
							<p class="smallsub">
								<?php echo JText::sprintf($this->state->get('text.alias', 'JGLOBAL_LIST_ALIAS'), $this->escape($item->item_alias)); ?>
							</p>
						<?php endif; ?>
					</td>
				<?php endif; ?>
				<?php if (isset($this->items[0]->item_category)) : ?>
					<td>
						<?php echo $this->escape($item->item_category); ?>
					</td>
				<?php endif; ?>
				<td>
					<?php if ($extension = $extensionsHelper->getExtensionsPivot('content_type_id', true)->get((int) $this->state->get('filter.content_type_id', 0))) : ?>
						<?php echo JText::_($extension->forms->extension->title); ?>
					<?php else: ?>
						<?php echo JText::_('JUNDEFINED'); ?>
					<?php endif; ?>
				</td>
				<td class="center">
					<?php if (!is_null($item->state)) : ?>
						<?php echo JHtml::_('jgrid.published', $item->state, $i, 'elements.', false, 'cb'); ?>
					<?php endif; ?>
				</td>
				<td class="order">
					<?php if (!is_null($item->ordering)) : ?>
						<?php if ($canChange) : ?>
							<?php if ($saveOrder) : ?>
								<?php if ($listDirn == 'asc') : ?>
									<span><?php echo $this->pagination->orderUpIcon($i, true, 'elements.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
									<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'elements.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
								<?php elseif ($listDirn == 'desc') : ?>
									<span><?php echo $this->pagination->orderUpIcon($i, true, 'elements.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
									<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'elements.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
								<?php endif; ?>
							<?php endif; ?>
							<?php $disabled = $saveOrder ? '' : 'disabled="disabled"'; ?>
							<input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>" <?php echo $disabled ?> class="text-area-order" />
						<?php else : ?>
							<?php echo $item->ordering; ?>
						<?php endif; ?>
					<?php endif; ?>

				</td>
				<td class="center">
					<?php if (!is_null($item->id)) : ?>
						<?php echo (int) $item->id; ?>
					<?php endif; ?>
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