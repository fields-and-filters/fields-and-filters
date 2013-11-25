<?php
/**
 * @version     1.1.1
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kulka.tomek@gmail.com> - 
 */

// No direct access
defined( '_JEXEC' ) or die;

// Import CSS
JHtml::_( 'stylesheet', 'fieldsandfilters/administrator/fieldsandfilters.css', array(), true );

require_once JPATH_COMPONENT_ADMINISTRATOR . '/fieldsandfilters.script.php';

com_fieldsandfiltersInstallerScript::_checkContentType();


exit;
?>
<div class="span6">
<?php if ( !empty( $this->buttons['base'] ) ) : ?>
	<div class="cpanel-block">
		<h3><?php echo JText::_( 'COM_FIELDSANDFILTERS_HEADER_BASE' ); ?></h3>
		<div class="cpanel">
			<?php echo JHtml::_( 'FieldsandfiltersHtml.grid.buttons', $this->buttons['base'] ); ?>
		</div>
	</div>
<?php endif; ?>

<?php if ( !empty( $this->buttons['modules'] ) ) : ?>
	<div class="cpanel-block">
		<h3><?php echo JText::_( 'COM_FIELDSANDFILTERS_HEADER_MODULES' ); ?></h3>
		<div class="cpanel">
			<?php echo JHtml::_( 'FieldsandfiltersHtml.grid.buttons', $this->buttons['modules'] ); ?>
		</div>
	</div>
<?php endif; ?>

<?php if ( !empty( $this->buttons['plugins'] ) ) : ?>
	<div class="cpanel-block">
		<h3><?php echo JText::_( 'COM_FIELDSANDFILTERS_HEADER_PLUGINS' ); ?></h3>
		<div class="cpanel">
			<?php echo JHtml::_( 'FieldsandfiltersHtml.grid.buttons', $this->buttons['plugins'] ); ?>
		</div>
	</div>
<?php endif; ?>
</div>
<div class="span4">
	<h3><?php echo JText::_( 'COM_FIELDSANDFILTERS_HEADER_INFORMATION' ); ?></h3>
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<input type="hidden" name="cmd" value="_s-xclick" />
		<input type="hidden" name="hosted_button_id" value="4H27YCMTRWZV8" />
			<?php /* <a href="#" id="btnchangelog" class="btn btn-info">CHANGELOG</a>- */ ?>
		<input type="submit" class="btn btn-inverse" value="Donate via PayPal" />
			<?php /* <input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online."> */ ?>
		<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>
</div>