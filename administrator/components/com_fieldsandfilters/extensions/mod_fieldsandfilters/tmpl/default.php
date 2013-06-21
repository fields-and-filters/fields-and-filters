<?php
/**
 * @version     1.0.0
 * @package     com_fieldsandfilters
 * @subpackage  mod_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined( '_JEXEC' ) or die;

// Import CSS
JHtml::_( 'stylesheet', 'fieldsandfilters/component/fieldsandfilters.css', array(), true );

?>
	
<div id="faf-mod-<?php echo $module->id; ?>" class="faf-mod">
        <form action="#" method="get" name="faf-form-<?php echo $module->id; ?>"
                id="faf-form-<?php echo $module->id; ?>" class="faf-filters-form faf-form faf-filters-loading form-inline" autocomplete="off">
                
                <?php echo $templateFilters; ?>
                
                <?php if( $params->get( 'show_submit', 1 ) ) : ?>
                <input type="submit" id="faf-form-submit-<?php echo $module->id; ?>" name="faf-form-submit-<?php echo $module->id; ?>" class="btn btn-primary faf-form-submit" value="<?php echo JText::_( 'MOD_FILEDSANDFILTERS_FORM_SUBMIT' );?>" />
                <?php endif; ?>
        </form>
</div>