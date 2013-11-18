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

$db = JFactory::getDbo();
$query = $db->getQuery(true);
$query->select('*');
$query->from('#__content_types');

$result = $db->setQuery( $query )->loadAssocList();

//foreach( $result AS $item )
//{
//	echo '<pre>';
//	
//	foreach( array_keys( $item ) AS $key )
//	{
//		echo '<strong>' . $key . "</strong>:\n";
//		
//		if( in_array( $key, array( 'table', 'field_mappings', 'content_history_options' ) ) )
//		{
//			print_r( json_decode( $item[$key] ) ) . "\n";
//		}
//		else
//		{
//			echo $item[$key] . "\n";
//		}
//	}
//	
//	echo '</pre>--------------------------------';
//}
//
//exit;

$contentType = new stdClass();
$contentType->type_title = 'Fieldsandfilters Field';
$contentType->type_alias = 'com_fieldsandfilters.field';
$contentType->table = json_encode(
	array(
		'special' => array(
			'dbtable' => '#__fieldsandfilters_fields',
			'key'     => 'field_id',
			'type'    => 'Field',
			'prefix'  => 'FieldsandfiltersTable',
			'config'  => 'array()'
		),
		'common' => array()
	)
);

$contentType->rules = '';

$contentType->field_mappings = json_encode(
	array(
		'common' => array(
			'core_content_item_id'	=> 'field_id',
			'core_title'		=> 'field_name',
			'core_state'		=> 'state',
			'core_alias'		=> 'field_alias',
			'core_created_time'	=> 'null', // null
			'core_modified_time'	=> 'null', // null
			'core_body'		=> 'description',
			'core_hits'		=> 'null', // null
			'core_publish_up'	=> 'null', // null
			'core_publish_down'	=> 'null', // null
			'core_access'		=> 'access',
			'core_params'		=> 'params',
			'core_featured'		=> 'null', // null
			'core_metadata'		=> 'null', // null
			'core_language'		=> 'language',
			'core_images'		=> 'null', // null
			'core_urls'		=> 'null', // null
			'core_version'		=> 'null', // null
			'core_ordering'		=> 'ordering',
			'core_metakey'		=> 'null', // null
			'core_metadesc'		=> 'null', // null
			'core_catid'		=> 'null', // null
			'core_xreference'	=> 'null', // null
			'asset_id'		=> 'null' // null
		),
		'special' => array(
			'field_type'		=> 'field_type',
			'content_type_id'	=> 'content_type_id',
			'mode'			=> 'mode',
			'required'		=> 'required'
		)
	)
);

$contentType->router = '';

$contentType->content_history_options = json_encode(
	array(
		'formFile' 		=> 'administrator/components/com_fieldsandfilters/models/forms/field.xml',
		'hideFields' 		=> array( 'mode' ),
		'ignoreChanges' 	=> array(),
		'convertToInt'		=> array( 'content_type_id', 'mode', 'ordering', 'state', 'required' ),
		'displayLookup'		=> array(
			array(
				'sourceColumn'		=> 'content_type_id',
				'targetTable'		=> '#__content_types',
				'targetColumn'		=> 'type_id',
				'displayColumn'		=> 'type_title'
			)
		)
		
		
		
	)
);

$contentType = (array) $contentType;



// Get a db connection.
$db = JFactory::getDbo();

// Create a new query object.
$queryInsert = $db->getQuery(true);

$contentTypeKey = array_keys( $contentType );

$contentTypeValues = array_map( array( $db, 'quote'), array_values( $contentType ) );

// Prepare the insert query.

    // ->columns( $db->quoteName( $contentTypeKey ) )
    // ->values( implode( ',', $contentTypeValues ) );


    
$queryCheck = $db->getQuery(true)
	->select( '*' )
	->from( $db->quoteName( '#__content_types' ) )
	->where( $db->quoteName( 'type_alias' ) . ' = ' . $db->quote( 'com_fieldsandfilters.field' ) )
	;

$queryValues = $db->getQuery(true)
	->select( $contentTypeValues );

$subquery = $db->getQuery(true)
	->select('*')
	->from( (string) $queryValues )
	->where( 'NOT EXISTS (' . (string) $queryCheck . ' )');

$queryInsert
	->insert( $db->quoteName( '#__content_types' ) )
	->columns( $db->quoteName( $contentTypeKey ) )
	->columns( (string) $subquery );
	
echo $subquery->dump();


//exit;
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