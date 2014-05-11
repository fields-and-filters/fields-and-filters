<?php
/**
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfiltersextensions.content
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

$script = array();
$script[] = 'jQuery(document).ready(function($) {';
$script[] = '   $(".faf-content-form-reset").fieldsandfilters("reset");';
$script[] = '});';

JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
?>
	<div class="alert alert-info">
		<?php echo JText::_('PLG_FAF_ES_CT_ERROR_NOT_MATCH_TO_FILTERS'); ?>
	</div>

<?php if (true || $plugin->params->get('show_reset', false)): ?>
	<a href="javascript:void(0)" class="btn btn-primary faf-content-form-reset" alt="<?php echo JText::_('PLG_FAF_ES_CT_RESET'); ?>"><?php echo JText::_('PLG_FAF_ES_CT_RESET'); ?></a>
<?php endif; ?>