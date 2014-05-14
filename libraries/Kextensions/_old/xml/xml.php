<?php
/**
 * @package     lib_kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('JPATH_PLATFORM') or die;

/**
 * KextensionsXML
 *
 * @since       1.0.0
 */
class KextensionsXML
{
	/**
	 * Adds a new child SimpleXMLElement node to the source.
	 *
	 * @param   SimpleXMLElement $source The source element on which to append.
	 * @param   SimpleXMLElement $new    The new element to append.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 * @throws  Exception if an error occurs.
	 */
	protected static function addNode(SimpleXMLElement $source, SimpleXMLElement $new)
	{
		// Add the new child node.
		$node = $source->addChild($new->getName(), trim($new));

		// Add the attributes of the child node.
		foreach ($new->attributes() as $name => $value)
		{
			$node->addAttribute($name, $value);
		}

		// Add any children of the new node.
		foreach ($new->children() as $child)
		{
			self::addNode($node, $child);
		}
	}

	/**
	 * Method to set some field XML elements to the source xml.
	 *
	 * @param   SimpleXMLElement $source The source element on which to append.
	 * @param                    array   /SimpleXMLElement      &$elements      The array of XML element object representations of the form fields Or single xml element.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0.0
	 */
	public static function setFields(&$sorce, &$elements)
	{
		if (is_array($elements))
		{
			foreach ($elements AS $element)
			{
				if ($element instanceof SimpleXMLElement)
				{
					self::addNode($sorce, $element);
				}
			}

			return true;
		}
		elseif ($elements instanceof SimpleXMLElement)
		{
			self::addNode($sorce, $elements);

			return true;
		}

		return false;
	}

	/**
	 * Adds a new options SimpleXMLElement node to the source.
	 *
	 * @param   SimpleXMLElement $source  The source element on which to append.
	 * @param   array            $options The associative array of options.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public static function addOptionsNode(SimpleXMLElement $source, $options = array(), $flip = false)
	{
		if (!is_array($options) || empty($options))
		{
			return false;
		}

		if ($flip)
		{
			$options = array_flip($options);
		}

		foreach ($options AS $key => $value)
		{
			$option = $source->addChild('option', $key);
			$option->addAttribute('value', $value);
		}

		return true;
	}
}
