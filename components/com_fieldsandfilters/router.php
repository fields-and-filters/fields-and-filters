<?php
/**
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

// No direct access
defined('_JEXEC') or die;

/**
 * @param    array    A named array
 *
 * @return    array
 */
function FieldsandfiltersBuildRoute(&$query)
{
	$segments = array();

	if (isset($query['task']))
	{
		$segments[] = implode('/', explode('.', $query['task']));
		unset($query['task']);
	}
	if (isset($query['id']))
	{
		$segments[] = $query['id'];
		unset($query['id']);
	}

	return $segments;
}

/**
 * @param    array    A named array
 * @param    array
 *
 * Formats:
 *
 * index.php?/fieldsandfilters/task/id/Itemid
 *
 * index.php?/fieldsandfilters/id/Itemid
 */
function FieldsandfiltersParseRoute($segments)
{
	$vars = array();

	// view is always the first element of the array
	$count = count($segments);

	if ($count)
	{
		$count--;
		$segment = array_pop($segments);
		if (is_numeric($segment))
		{
			$vars['id'] = $segment;
		}
		else
		{
			$count--;
			$vars['task'] = array_pop($segments) . '.' . $segment;
		}
	}

	if ($count)
	{
		$vars['task'] = implode('.', $segments);
	}

	return $vars;
}
