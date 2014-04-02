<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kulka.tomek@gmail.com> -
 */


// no direct access
defined('_JEXEC') or die;

$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
?>
<th width="10%" class="hidden-phone">
	<?php echo JHtml::_('searchtools.sort', 'PLG_FAF_ES_CT_ELEMENTS_ITEM_CATEGORY_NAME', 'a.catid', $listDirn, $listOrder); ?>
</th>