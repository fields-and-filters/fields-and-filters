<?php
/**
 * @version     1.0.0
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

// Load Extensions Helper
JLoader::import( 'helpers.fieldsandfilters.extensionshelper', JPATH_ADMINISTRATOR . '/components/com_fieldsandfilters' );

$document 	= JFactory::getDocument();
$app		= JFactory::getApplication();
// Checking if loaded via index.php or component.php
$recordId	= $app->input->get( 'recordId', 0, 'int' );
$tmpl 		= $app->input->get( 'tmpl', '', 'cmd' );

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
			FieldsandfiltersExtensionsHelper::loadLanguage( 'plg_' . $pluginType->type . '_' . $pluginType->name, JPATH_ADMINISTRATOR );
			$group = (array) $pluginType->group;
			echo JHtml::_( 'bootstrap.addSlide', 'pluginTypes', JText::_( JArrayHelper::getValue( $group, 'title', 'COM_FIELDSANDFILTERS_PLUGINSTYPES_OTHERS' ) ), 'type' . $nameGroup );
		?>
		<ul class="nav nav-tabs nav-stacked">
		<?php foreach( $pluginTypes AS &$type ): ?>
			<?php
				if( $pluginType != $type ):
					FieldsandfiltersExtensionsHelper::loadLanguage( 'plg_' . $type->type . '_' . $type->name, JPATH_ADMINISTRATOR );
				endif;
			?>
			<li>
				<a class="choose_type" href="#" title="<?php echo $this->escape( $type->description ); ?>"
					onclick="javascript:setType('<?php echo base64_encode( json_encode( array( 'id' => $recordId, 'title' => $type->title, 'type' => $type->type, 'name' => $type->name ) ) ); ?>')">
					<?php if ($document->direction != 'rtl') : ?>
						<?php echo $this->escape( JText::_( $type->title ) );?>
						<small class="muted">
							<?php echo $this->escape( JText::_( $type->description ) ); ?>
						</small>
					<?php else : ?>
						<small class="muted">
							<?php echo $this->escape( JText::_( $type->description ) ); ?>
						</small>
						<?php echo $this->escape( JText::_( $type->title ) );?>
					<?php endif?>
				</a>
			</li>
		<?php endforeach; ?>
		</ul>
		<?php echo JHtml::_( 'bootstrap.endSlide' ); ?>
	<?php endforeach; ?>
<?php echo JHtml::_( 'bootstrap.endAccordion' ); ?>