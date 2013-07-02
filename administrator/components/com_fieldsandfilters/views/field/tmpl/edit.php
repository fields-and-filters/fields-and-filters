<?php
/**
 * @version     1.0.0
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// no direct access
defined( '_JEXEC' ) or die;

JHtml::_( 'behavior.tooltip' );
JHtml::_( 'behavior.formvalidation' );
JHtml::_( 'behavior.keepalive' );
JHtml::_( 'formbehavior.chosen', 'select' );

// Import CSS
JHtml::_( 'stylesheet', 'fieldsandfilters/component/fieldsandfilters_admin.css', array(), true );
?>
<script type="text/javascript">
	Joomla.submitbutton = function( task, type )
	{
		if( task == 'field.setType' )
		{
			document.id( 'field-form' ).elements['jform[temp_type]'].value = type;
			Joomla.submitform( 'field.setType', document.id( 'field-form' ) );
		}
		else if( task == 'field.setExtension' )
		{
			document.id( 'field-form' ).elements['jform[temp_extension]'].value = type;
			Joomla.submitform( 'field.setExtension', document.id( 'field-form' ) );
		}
		else if( task == 'field.cancel' || document.formvalidator.isValid( document.id( 'field-form' ) ) )
		{
			Joomla.submitform( task, document.getElementById( 'field-form' ) );
		}
		else
		{
			alert( '<?php echo $this->escape( JText::_( 'JGLOBAL_VALIDATION_FORM_FAILED' ) );?>' );
		}
	}
</script>

<form action="<?php echo JRoute::_( 'index.php?option=com_fieldsandfilters&layout=edit&id=' . (int) $this->item->field_id ); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="field-form" class="form-validate form-horizontal">
	<fieldset>
		<?php echo JHtml::_( 'bootstrap.startTabSet', 'myTab', array( 'active' => 'details' ) ); ?>
			<?php echo JHtml::_( 'bootstrap.addTab', 'myTab', 'details', JText::_( 'COM_FIELDSANDFILTERS_DETAILS_FIELDSET_LABEL', true ) ); ?>
				<div class="row-fluid">
					<div class="span6">
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel( 'field_name' ); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput( 'field_name' ); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel( 'field_alias' ); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput( 'field_alias' ); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel( 'field_type' ); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput( 'field_type' ); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel( 'extension_type_id' ); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput( 'extension_type_id' ); ?>
							</div>
						</div>
					</div>
					<div class="span6">
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel( 'state' ); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput( 'state' ); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel( 'required' ); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput( 'required' ); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel( 'access' ); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput( 'access' ); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel( 'language' ); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput( 'language' ); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel( 'field_id' ); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput( 'field_id' ); ?>
							</div>
						</div>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel( 'description' ); ?>
					<?php echo $this->form->getInput( 'description' ); ?>
				</div>
			<?php echo JHtml::_( 'bootstrap.endTab' ); ?>
			
			<?php echo $this->loadTemplate( 'values' ); ?>
			<?php echo $this->loadTemplate( 'params' ); ?>
			
		<?php echo JHtml::_( 'bootstrap.endTabSet' ); ?>
	</fieldset>
    
	<input type="hidden" id="jform_temp_type" name="jform[temp_type]" value="" />
	<input type="hidden" id="jform_temp_extension" name="jform[temp_extension]" value="" />
	<input type="hidden" name="task" value="" />
	<?php echo $this->form->getInput( 'mode' ); ?>
	<?php echo JHtml::_( 'form.token' ); ?>
</form>