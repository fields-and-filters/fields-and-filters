<?php
/**
 * @version     1.1.0
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access.
defined('_JEXEC') or die;

if( !FieldsandfiltersFactory::isVersion() )
{
	jimport( 'joomla.application.component.controlleradmin' );
}

/**
 * Fields list controller class.
 * @since       1.0.0
 */
class FieldsandfiltersControllerFields extends JControllerAdmin
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since       1.0.0
	 */
	public function __construct( $config = array() )
	{
		parent::__construct($config);
		
		// Define standard task mappings.
		
		// State Value = -1
		$this->registerTask( 'onlyadmin', 'publish' );
		
		// Required Value = 0
		$this->registerTask( 'unrequired', 'required' );
	}
	
	/**
	 * Proxy for getModel.
	 * @since       1.0.0
	 */
	public function getModel( $name = 'field', $prefix = 'FieldsandfiltersModel', $config = array() )
	{
		$model = parent::getModel( $name, $prefix, array_merge( array( 'ignore_request' => true ), $config ) );
		return $model;
	}
	
	/**
	 * Method to publish a list of items
	 *
	 * @return  void
	 * @since       1.0.0
	 */
	public function publish()
	{
		// Check for request forgeries
		JSession::checkToken() or die( JText::_( 'JINVALID_TOKEN' ) );

		// Get items to publish from the request.
		$jinput 	= JFactory::getApplication()->input;
		$cid 		= $jinput->get( 'cid', array(), 'array' );
		$data = array( 'publish' => 1, 'unpublish' => 0, 'onlyadmin' => -1 );
		$task = $this->getTask();
		$value = JArrayHelper::getValue( $data, $task, 0, 'int' );

		if( empty( $cid ) )
		{
			JLog::add( JText::_( $this->text_prefix . '_NO_ITEM_SELECTED' ), JLog::WARNING, 'jerror' );
		}
		else
		{
			// Get the model.
			$model = $this->getModel();
			
			// Make sure the item ids are integers
			JArrayHelper::toInteger( $cid );
			
			// Publish the items.
			if( !$model->publish( $cid, $value ) )
			{
				JLog::add( $model->getError(), JLog::WARNING, 'jerror' );
			}
			else
			{
				if( $value == 1 )
				{
					$ntext = $this->text_prefix . '_N_ITEMS_PUBLISHED';
				}
				elseif( $value == -1 )
				{
					$ntext = $this->text_prefix . '_N_ITEMS_ONLYADMIN';
				}
				else
				{
					$ntext = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';
				}
				
				$this->setMessage( JText::plural( $ntext, count( $cid ) ) );
			}
		}
		
		$extension = $jinput->get('extension');
		$extensionURL = ( $extension ) ? '&extension=' . $extension : '';
		$this->setRedirect( JRoute::_( 'index.php?option=' . $this->option . '&view=' . $this->view_list . $extensionURL, false ) );
	}
	
	/**
	 * Method to required a list of items
	 *
	 * @return  void
	 * @since       1.0.0
	 */
	public function required()
	{
		// Check for request forgeries
		JSession::checkToken() or die( JText::_( 'JINVALID_TOKEN' ) );
		
		// Get items to publish from the request.
		$jinput 	= JFactory::getApplication()->input;
		$cid 		= $jinput->get( 'cid', array(), 'array' );
		$data 		= array( 'required' => 1, 'unrequired' => 0 );
		$task 		= $this->getTask();
		$value 		= JArrayHelper::getValue( $data, $task, 0, 'int' );
		
		if( empty( $cid ) )
		{
			JLog::add( JText::_( $this->text_prefix . '_NO_ITEM_SELECTED' ), JLog::WARNING, 'jerror' );
		}
		else
		{
			// Get the model.
			$model = $this->getModel();
			
			// Make sure the item ids are integers
			JArrayHelper::toInteger( $cid );
			
			// Publish the items.
			if( !$model->required( $cid, $value ) )
			{
				JLog::add($model->getError(), JLog::WARNING, 'jerror');
			}
			else
			{
				if( $value == 1 )
				{
					$ntext = $this->text_prefix . '_N_ITEMS_REQUIRED';
				}
				else
				{
					$ntext = $this->text_prefix . '_N_ITEMS_UNREQUIRED';
				}
				
				$this->setMessage( JText::plural( $ntext, count( $cid ) ) );
			}
		}
		
		$extension = $jinput->get('extension');
		$extensionURL = ( $extension ) ? '&extension=' . $extension : '';
		$this->setRedirect( JRoute::_( 'index.php?option=' . $this->option . '&view=' . $this->view_list . $extensionURL, false ) );
	}
	
	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 * @since       1.0.0
	 */
	public function saveOrderAjax()
	{
		$pks = $this->input->post->get( 'cid', array(), 'array' );
		$order = $this->input->post->get( 'order', array(), 'array' );
		
		// Sanitize the input
		JArrayHelper::toInteger( $pks );
		JArrayHelper::toInteger( $order );
		
		// Get the model
		$model = $this->getModel();
		
		// Save the ordering
		$return = $model->saveorder( $pks, $order );
		
		if( $return )
		{
			echo "1";
		}
		
		// Close the application
		JFactory::getApplication()->close();
	}
}