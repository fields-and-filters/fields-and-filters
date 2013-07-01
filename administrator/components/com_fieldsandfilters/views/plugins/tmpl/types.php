<?php
/**
 * @version     1.0.0
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

$document 		= JFactory::getDocument();
$app			= JFactory::getApplication();
// Checking if loaded via index.php or component.php
$recordId		= $app->input->get( 'recordId', 0, 'int' );
$tmpl 			= $app->input->get( 'tmpl', '', 'cmd' );

// Load Extensions Helper
$extensionsHelper 	= FieldsandfiltersFactory::getExtensions();

// Import CSS
JHtml::_( 'stylesheet', 'fieldsandfilters/component/fieldsandfilters_admin.css', array(), true );
?>

<script type="text/javascript">
	setType = function( type )
	{
		<?php if( $tmpl ) : ?>
			window.parent.Joomla.submitbutton( 'field.setType', type );
			window.parent.SqueezeBox.close();
		<?php else : ?>
			window.location="index.php?option=com_fieldsandfilters&view=field&task=field.setType&layout=edit&type=" + ( 'field.setType', type );
		<?php endif; ?>
	}
</script>

<!-- Header -->
<header class="header">
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span10">
				<?php if( isset( $app->JComponentTitle ) ) : ?>
					<h1 class="page-title"><?php echo JHtml::_( 'string.truncate', $app->JComponentTitle, 0, false, false );?></h1>
				<?php else : ?>
					<h1 class="page-title"><?php echo JHtml::_( 'string.truncate', '', 0, false, false );?></h1>
				<?php endif; ?>
			</div>
		</div>
	</div>
</header>

<?php echo JHtml::_( 'bootstrap.startAccordion', 'pluginTypes', array( 'active' => 'collapse0' ) ); ?>
	<?php foreach( $this->_plugins->toObject() AS $nameGroup => $pluginTypes ) : ?>
		<?php
			$pluginType = current( $pluginTypes );
			$extensionsHelper->loadLanguage( 'plg_' . $pluginType->type . '_' . $pluginType->name, JPATH_ADMINISTRATOR );
			$group = $pluginType->forms->get( $nameGroup )->group;
			echo JHtml::_( 'bootstrap.addSlide', 'pluginTypes', JText::_( $group->title ), 'type' . $nameGroup );
		?>
		<ul class="nav nav-tabs nav-stacked">
		<?php foreach( $pluginTypes AS &$type ): ?>
			<?php
				if( $pluginType != $type ):
					$extensionsHelper->loadLanguage( 'plg_' . $type->type . '_' . $type->name, JPATH_ADMINISTRATOR );
				endif;
				
				$form = $type->forms->get( $nameGroup );
			?>
			<li>
				<a class="choose_type" href="#" title="<?php echo $this->escape( $type->description ); ?>"
					onclick="javascript:setType('<?php echo base64_encode( json_encode( array( 'id' => $recordId, 'mode' => $nameGroup, 'type' => $type->type, 'name' => $type->name ) ) ); ?>')">
					<?php if ($document->direction != 'rtl') : ?>
						<?php echo $this->escape( JText::_( $form->title ) );?>
						<small class="muted">
							<?php echo $this->escape( JText::_( $form->description ) ); ?>
						</small>
					<?php else : ?>
						<small class="muted">
							<?php echo $this->escape( JText::_( $form->description ) ); ?>
						</small>
						<?php echo $this->escape( JText::_( $form->title ) );?>
					<?php endif?>
				</a>
			</li>
		<?php endforeach; ?>
		</ul>
		<?php echo JHtml::_( 'bootstrap.endSlide' ); ?>
	<?php endforeach; ?>
<?php echo JHtml::_( 'bootstrap.endAccordion' ); ?>