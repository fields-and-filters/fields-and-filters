<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// no direct access
defined('_JEXEC') or die;

// Load PluginTypes Helper
$typesHelper = FieldsandfiltersFactory::getTypes();
$valuesMode = (array) FieldsandfiltersModes::getMode(FieldsandfiltersModes::MODE_FILTER);

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
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$canOrder = $user->authorise('core.edit.state', 'com_fieldsandfilters');
$saveOrder = $listOrder == 'f.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_fieldsandfilters&task=fields.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'fieldList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
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

<form action="<?php echo JRoute::_('index.php?option=com_fieldsandfilters&view=fields'); ?>" method="post" name="adminForm" id="adminForm">
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
	<table class="table table-striped" id="fieldList">
		<thead>
		<tr>
			<th width="1%" class="nowrap center hidden-phone">
				<?php echo JHtml::_('searchtools.sort', '', 'f.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
			</th>
			<th width="1%" class="hidden-phone">
				<?php echo JHtml::_('grid.checkall'); ?>
			</th>
			<th width="1%" style="min-width:55px" class="nowrap center">
				<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'f.state', $listDirn, $listOrder); ?>
			</th>
			<th>
				<?php echo JHtml::_('searchtools.sort', 'COM_FIELDSANDFILTERS_FIELDS_FIELD_NAME', 'f.name', $listDirn, $listOrder); ?>
			</th>

			<th class="center">
				<?php echo JHtml::_('searchtools.sort', 'COM_FIELDSANDFILTERS_FIELDS_FIELD_TYPE', 'f.type', $listDirn, $listOrder); ?>
			</th>

			<th class="center" class="hidden-phone">
				<?php echo JHtml::_('searchtools.sort', 'COM_FIELDSANDFILTERS_FIELDS_EXTENSION_TYPE', 'f.content_type_id', $listDirn, $listOrder); ?>
			</th>

			<?php /*
						<th width="5%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_FIELDSANDFILTERS_FIELDS_LANGUAGE', 'f.language', $listDirn, $listOrder); ?>
						</th>
						*/
			?>

			<th width="1%" class="nowrap hidden-phone">
				<?php echo JHtml::_('searchtools.sort', 'COM_FIELDSANDFILTERS_FIELDS_FIELD_ID', 'f.id', $listDirn, $listOrder); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($this->items as $i => $item) :
			$ordering   = ($listOrder == 'f.ordering');
			$canCreate  = $user->authorise('core.create', 'com_fieldsandfilters');
			$canEdit    = $user->authorise('core.edit', 'com_fieldsandfilters');
			$canCheckin = $user->authorise('core.manage', 'com_fieldsandfilters');
			$canChange  = $user->authorise('core.edit.state', 'com_fieldsandfilters');
			?>
			<tr class="row<?php echo $i % 2; ?>">
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
						<?php echo JHtml::_('FieldsandfiltersHtml.grid.published', $item->state, $i, 'fields.', $canChange, 'cb'); ?>
						<?php echo JHtml::_('FieldsandfiltersHtml.grid.required', $item->required, $i, 'fields.', $canChange); ?>

						<?php
						// Create dropdown items
						if ($item->state != -1) :
							$action = $item->state ? 'unpublish' : 'publish';
							JHtml::_('actionsdropdown.' . $action, 'cb' . $i, 'fields');

							JHtml::_('FieldsandfiltersHtml.dropdown.onlyAdmin', 'cb' . $i, 'fields.');
						else :
							JHtml::_('actionsdropdown.publish', 'cb' . $i, 'fields');
							JHtml::_('actionsdropdown.unpublish', 'cb' . $i, 'fields');
						endif;

						JHtml::_('actionsdropdown.divider');

						$action = $item->required ? 'unrequired' : 'required';
						JHtml::_('FieldsandfiltersHtml.dropdown.' . $action, 'cb' . $i, 'fields.');

						// Render dropdown list
						echo JHtml::_('actionsdropdown.render');
						?>
					</div>
				</td>

				<td class="nowrap">
					<div class="pull-left">
						<?php if ($canEdit) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_fieldsandfilters&task=field.edit&id=' . (int) $item->id); ?>">
								<?php echo $this->escape($item->name); ?>
							</a>
						<?php else : ?>
							<?php echo $this->escape($item->name); ?>
						<?php endif; ?>

						<?php if (in_array($item->mode, $valuesMode)) : ?>
							<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
							<div class="btn-toolbar">
								<div class="btn-group">
									<a href="<?php echo JRoute::_('index.php?option=com_fieldsandfilters&view=fieldvalues&field_id=' . (int) $item->id); ?>" class="btn btn-micro">
										<i class="icon-faf-field-values"></i>
										<?php echo JText::_('COM_FIELDSANDFILTERS_FIELDS_FIELD_VALUES'); ?>
									</a>
									<button class="btn btn-micro dropdown-toggle" data-toggle="dropdown">
										<span class="caret"></span>
									</button>
									<ul class="dropdown-menu">
										<li>
											<a tabindex="-1" href="<?php echo JRoute::_('index.php?option=com_fieldsandfilters&view=fieldvalue&layout=edit&field_id=' . (int) $item->id); ?>">
												<i class="icon-faf-field-value"></i>
												<?php echo JText::_('COM_FIELDSANDFILTERS_QUICKICON_ADD_NEW_FIELD'); ?>
											</a>
										</li>
										<li>
											<a tabindex="-1" href="<?php echo JRoute::_('index.php?option=com_fieldsandfilters&view=fieldvalues&field_id=' . (int) $item->id); ?>">
												<i class="icon-faf-field-values"></i>
												<?php echo JText::_('COM_FIELDSANDFILTERS_FIELDS_FIELD_VALUES'); ?>
											</a>
										</li>
									</ul>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</td>

				<td class="center">
					<?php if ($type = $typesHelper->getTypes(true)->get($item->type)) : ?>
						<?php
						KextensionsLanguage::load('plg_' . $type->type . '_' . $type->name, JPATH_ADMINISTRATOR);
						$typeName = FieldsandfiltersModes::getModeName($item->mode, FieldsandfiltersModes::MODE_NAME_TYPE);
						$typeForm = $type->forms->get($typeName, new JObject);

						if (isset($typeForm->group->title))
						{
							$titleType = JText::_($typeForm->title) . ' [' . JText::_($typeForm->group->title) . ']';
						}
						else
						{
							$titleType = JText::_($typeForm->title);
						}
						?>
						<?php echo $titleType; ?>
					<?php else : ?>
						<?php echo JText::_('JUNDEFINED'); ?>
					<?php endif; ?>
				</td>

				<td class="center" class="hidden-phone">
					<?php if ($extension = $extensionsHelper->getExtensionsPivot('content_type_id', true)->get((int) $item->content_type_id)) : ?>
						<?php
						// load plugin language
						if ($extension->name != FieldsandfiltersExtensions::EXTENSION_DEFAULT)
						{
							KextensionsLanguage::load('plg_' . $extension->type . '_' . $extension->name, JPATH_ADMINISTRATOR);
						}
						$extensionForm = $extension->forms->get('extension', new JObject);
						?>
						<?php echo JText::_($extensionForm->get('title')); ?>
					<?php else : ?>
						<?php echo JText::_('JUNDEFINED'); ?>
					<?php endif; ?>
				</td>

				<?php /*
						<td class="center">
							<?php if ($item->language == '*') : ?>
								<?php echo JText::alt('JALL', 'language'); ?>
							<?php else : ?>
								<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
							<?php endif;?>
						</td>
						*/
				?>

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