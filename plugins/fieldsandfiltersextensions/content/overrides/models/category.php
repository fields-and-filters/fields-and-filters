<?php
/**
 * @package     fieldsandfilters.plugin
 * @subpackage  fieldsandfilters_extension.content
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

JLoader::import('com_content.models.category', JPATH_SITE . '/components');

/**
 * @since       1.0.0
 */
class plgFieldsandfiltersExtensionsContentModelCategory extends ContentModelCategory
{
	protected $_model_articles;

	/**
	 * Context string for the model type.  This is used to handle uniqueness
	 * when dealing with the getStoreId() method and caching data structures.
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $context = 'com_content.category';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * return    void
	 *
	 * @since       1.0.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initiliase variables.
		$app = JFactory::getApplication();
		$pk  = $app->input->getInt('id');

		$this->setState('category.id', $pk);

		// Load the parameters. Merge Global and Menu Item params into new object
		$params = $app->getParams('com_content');

		if ($menu = $app->getMenu()->getActive())
		{
			$mergedParams = ($menu->params instanceof JRegistry) ? clone $menu->params : new JRegistry($menu->params);
		}
		else
		{
			$mergedParams = new JRegistry();
		}

		$mergedParams->merge($params);

		$this->setState('params', $mergedParams);
		$user = JFactory::getUser();

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		if ((!$user->authorise('core.edit.state', 'com_content')) && (!$user->authorise('core.edit', 'com_content')))
		{
			// limit to published for people who can't edit or edit.state.
			$this->setState('filter.published', 1);
			// Filter by start and end dates.
			$nullDate = $db->Quote($db->getNullDate());
			$nowDate  = $db->Quote(JFactory::getDate()->toSQL());

			$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
				->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
		}
		else
		{
			$this->setState('filter.published', array(0, 1, 2));
		}

		// process show_noauth parameter
		if (!$params->get('show_noauth'))
		{
			$this->setState('filter.access', true);
		}
		else
		{
			$this->setState('filter.access', false);
		}

		// Optional filter text
		$this->setState('list.filter', $app->input->getString('filter-search'));

		// filter.order
		$itemid   = $app->input->get('id', 0, 'int') . ':' . $app->input->get('Itemid', 0, 'int');
		$orderCol = $app->getUserStateFromRequest('com_content.category.list.' . $itemid . '.filter_order', 'filter_order', '', 'string');
		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'a.ordering';
		}
		$this->setState('list.ordering', $orderCol);

		$listOrder = $app->getUserStateFromRequest('com_content.category.list.' . $itemid . '.filter_order_Dir',
			'filter_order_Dir', '', 'cmd');
		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}
		$this->setState('list.direction', $listOrder);

		$this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));

		// set limit for query. If list, use parameter. If blog, add blog parameters for limit.
		if (($app->input->get('layout') == 'blog') || $params->get('layout_type') == 'blog')
		{
			$limit = $params->get('num_leading_articles') + $params->get('num_intro_articles') + $params->get('num_links');
			$this->setState('list.links', $params->get('num_links'));
		}
		else
		{
			$limit = $app->getUserStateFromRequest('com_content.category.list.' . $itemid . '.limit', 'limit', $params->get('display_num'), 'uint');
		}

		$this->setState('list.limit', $limit);

		// set the depth of the category query based on parameter
		$showSubcategories = $params->get('show_subcategory_content', '0');

		if ($showSubcategories)
		{
			$this->setState('filter.max_category_levels', $params->get('show_subcategory_content', '1'));
			$this->setState('filter.subcategories', true);
		}

		$this->setState('filter.language', JLanguageMultilang::isEnabled());

		$this->setState('layout', $app->input->get('layout'));
	}

	/**
	 * Get the articles in the category
	 *
	 * @return    mixed    An array of articles or false if an error occurs.
	 * @since       1.0.0
	 */
	public function getItems()
	{
		$limit = $this->getState('list.limit');

		if ($this->_articles === null && $category = $this->getCategory())
		{
			$model = $this->_getModelArticles();

			if ($limit >= 0)
			{
				$this->_articles = $model->getItems();
				$model->getItemsID();

				if ($this->_articles === false)
				{
					$this->setError($model->getError());
				}
			}
			else
			{
				$this->_articles = array();
			}
			$this->setState('fieldsandfilters.itemsID', $model->getState('fieldsandfilters.itemsID', array()));
			$this->setState('fieldsandfilters.emptyItemsID', $this->setState('fieldsandfilters.emptyItemsID', false));

			$this->_pagination = $model->getPagination();
		}

		return $this->_articles;
	}

	/**
	 * @since       1.0.0
	 */
	public function getContentItemsID()
	{
		$limit   = $this->getState('list.limit');
		$itemsID = array();

		if ($limit >= 0)
		{
			$model   = $this->_getModelArticles();
			$itemsID = $model->getItemsID();
		}

		return $itemsID;
	}

	/**
	 * @since       1.0.0
	 */
	protected function _getModelArticles()
	{
		if ($this->_model_articles === null && $category = $this->getCategory())
		{
			$model = JModelLegacy::getInstance('Articles', 'plgFieldsandfiltersExtensionsContentModel', array('ignore_request' => true));

			$model->setState('params', JFactory::getApplication()->getParams('com_content'));
			// [TODO] when 'filter.category_id' is comment all products form all categories is display
			$model->setState('filter.category_id', $category->id);
			$model->setState('filter.published', $this->getState('filter.published'));
			$model->setState('filter.access', $this->getState('filter.access'));
			$model->setState('filter.language', $this->getState('filter.language'));
			$model->setState('list.ordering', $this->_buildContentOrderBy());
			$model->setState('list.start', $this->getState('list.start'));
			$model->setState('list.limit', $this->getState('list.limit'));
			$model->setState('list.direction', $this->getState('list.direction'));
			$model->setState('list.filter', $this->getState('list.filter'));
			// filter.subcategories indicates whether to include articles from subcategories in the list or blog
			$model->setState('filter.subcategories', $this->getState('filter.subcategories'));
			$model->setState('filter.max_category_levels', $this->setState('filter.max_category_levels'));
			$model->setState('list.links', $this->getState('list.links'));

			$model->setState('fieldsandfilters.itemsID', $this->getState('fieldsandfilters.itemsID'));
			$model->setState('fieldsandfilters.emptyItemsID', $this->getState('fieldsandfilters.emptyItemsID', false));
            $model->setState('fieldsandfilters.random.selected', $this->getState('fieldsandfilters.random.selected', false));
            $model->setState('fieldsandfilters.random.limit', $this->getState('fieldsandfilters.random.limit', 0));

			$this->_model_articles = $model;
		}

		return $this->_model_articles;
	}
}
