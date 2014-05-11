<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kulka.tomek@gmail.com> -
 */

// No direct access
defined('_JEXEC') or die;

// Import CSS
JHtml::_('stylesheet', 'fieldsandfilters/administrator/fieldsandfilters.css', array(), true);
$html = JHtml::_('links.linksgroups', $this->buttons);
?>
<div class="row-fluid faf">
	<div class="span3">
		<div class="cpanel-links">
			<?php if (!empty($html)) : ?>
				<div class="sidebar-nav quick-icons">
					<?php echo $html; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<div class="span9">
		<h3><?php echo JText::_('COM_FIELDSANDFILTERS_HEADER_INFORMATION'); ?></h3>

		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_s-xclick" />
			<input type="hidden" name="hosted_button_id" value="4H27YCMTRWZV8" />
			<?php /* <a href="#" id="btnchangelog" class="btn btn-info">CHANGELOG</a>- */ ?>
			<input type="submit" class="btn btn-inverse" value="Donate via PayPal" />
			<?php /* <input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal � The safer, easier way to pay online."> */ ?>
			<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</form>
	</div>
</div>