<?php
/**
 * @version     1.1.1
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
//$info = FieldsandfiltersExtensions::getInformation();
//
//echo '<pre>';
//print_r(KextensionsArray::getColumn($info, 'type_alias'));
//echo '</pre>';

require_once JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters/fieldsandfilters.script.php';

// com_fieldsandfiltersInstallerScript::changePlugins('fieldsandfiltersTypes');
//
//$contentType = new FieldsandfiltersInstallerContenttype();
//$contentType->set('type_title', 'Fieldsandfilters Allextensions')
//->set('type_alias', 'com_fieldsandfilters.allextensions')
//->set('table.special', array(
//	'dbtable' => '#__content_types',
//	'key'     => 'type_id',
//	'type'    => 'JTable',
//	'prefix'  => 'Contenttype',
//))
//->set('table.common', new stdClass)
//->set('field_mappings.common', array(
//	'core_content_item_id'	=> 'type_id',
//	'core_title'		=> 'type_title',
//	'core_alias'		=> 'type_alias',
//))
//->set('field_mappings.special', array(
//	'table'				=> 'table',
//	'rules'				=> 'rules',
//	'field_mappings'		=> 'field_mappings',
//	'router'			=> 'router',
//	'content_history_options' 	=> 'content_history_options'
//))
//->set('content_history_options.formFile', 'administrator/components/com_fieldsandfilters/models/forms/allextensions/extension.xml');
//
//FieldsandfiltersInstallerScript::checkContentType($contentType);



//echo '<pre>';
//print_r(FieldsandfiltersFactory::getExtensions()->getExtensions());
//echo '</pre>';
//
//exit;

?>

<div class="span3">
	<div class="cpanel-links">
		<?php if (!empty($html)) : ?>
			<div class="sidebar-nav quick-icons">
				<?php echo $html;?>
			</div>
		<?php endif;?>
	</div>
</div>
<div class="span9">
	<h3><?php echo JText::_('COM_FIELDSANDFILTERS_HEADER_INFORMATION'); ?></h3>
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<input type="hidden" name="cmd" value="_s-xclick" />
		<input type="hidden" name="hosted_button_id" value="4H27YCMTRWZV8" />
			<?php /* <a href="#" id="btnchangelog" class="btn btn-info">CHANGELOG</a>- */ ?>
		<input type="submit" class="btn btn-inverse" value="Donate via PayPal" />
			<?php /* <input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online."> */ ?>
		<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>
</div>