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

JHtml::addIncludePath( JPATH_COMPONENT_ADMINISTRATOR . '/helpers/html'  );

// Import CSS
JHtml::_( 'stylesheet', 'fieldsandfilters/component/fieldsandfilters_admin.css', array(), true );



$text = '
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse arcu nisl, sollicitudin ut nisl id, lobortis aliquet nisl. Vestibulum lacinia suscipit consectetur. 
#{12,{"params":1}}
Vivamus et tincidunt sapien, sed ultricies quam. Ut imperdiet nunc nec dolor aliquet fringilla. In consequat in tellus a condimentum. Quisque hendrerit non velit at vestibulum. Proin a ultricies orci, id porttitor lacus. Fusce at sapien nulla. Phasellus risus felis, aliquet id libero at, mattis hendrerit quam. Curabitur eu dolor id metus scelerisque imperdiet. Nulla et enim leo. 

Vivamus vitae neque libero. Nam varius quam et sodales consectetur. Duis in fringilla odio. Cras sed lacinia tellus. Quisque a justo eget nibh euismod rutrum.
#{12,23,{"params2":1}}
Suspendisse eget diam sodales, pellentesque massa vel, consectetur tellus. Pellentesque justo risus, vehicula ac condimentum et, dignissim in orci. Vestibulum vitae quam nisi. Cras et lorem ut libero convallis faucibus. 

Integer tristique elit id convallis ultricies. Integer congue nunc odio, a fringilla mi feugiat a. Donec sit amet consequat diam. Maecenas feugiat, leo et fringilla laoreet, tellus mauris molestie mi, ut ultricies augue arcu ut enim. Proin tempor nunc risus, eget vehicula ligula tincidunt quis.
#{12,23-com_content,{"params3":1}}
Nunc sollicitudin iaculis enim, et commodo enim convallis eu. Sed laoreet tempor felis, in adipiscing felis condimentum in. Nam fermentum pulvinar consequat. Etiam sed urna bibendum, sollicitudin tellus vel, commodo massa. Vivamus sed purus vehicula, tincidunt nisi eu, mollis mauris. Nunc id rutrum nibh. Vestibulum vel porttitor arcu. Mauris placerat eleifend nisi ut euismod.

#{12,23-com_content,context,{"params4":1}}

#{12,23-com_content,context}

#{12,23-com_content}

#{12,{"params5":1}}

#{}
';


$helper = FieldsandfiltersFactory::getFieldsSite();

$return = $helper::preparationContent($text, 'context', 'com_content', null);

echo '<pre>';
print_r($text);
echo '</pre>';
exit;


?>
<div class="span6">
<?php if ( !empty( $this->buttons['base'] ) ) : ?>
	<div class="cpanel-block">
		<h3><?php echo JText::_( 'COM_FIELDSANDFILTERS_HEADER_BASE' ); ?></h3>
		<div class="cpanel">
			<?php echo JHtml::_( 'fieldsandfilters.buttons', $this->buttons['base'] ); ?>
		</div>
	</div>
<?php endif; ?>


<?php if ( !empty( $this->buttons['plugins'] ) ) : ?>
	<div class="cpanel-block">
		<h3><?php echo JText::_( 'COM_FIELDSANDFILTERS_HEADER_PLUGINS' ); ?></h3>
		<div class="cpanel">
			<?php echo JHtml::_( 'fieldsandfilters.buttons', $this->buttons['plugins'] ); ?>
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