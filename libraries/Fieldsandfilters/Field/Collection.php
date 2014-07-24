<?php
/**
 * @package     Fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Fieldsandfilters\Field;

use Kextensions\Object\Object;
use Kextensions\Object\Collection AS BaseCollection;
use Fieldsandfilters\Content\AbstractContent;

defined('_JEXEC') or die;

/**
 * Field List
 *
 * @package     Fieldsandfilters
 * @since       2.0
 */
class Collection extends BaseCollection implements CollectionInterface
{
    /**
     * {@inheritDoc}
     */
    public function set($property, $value)
    {
        if (!$value instanceof BaseInterface)
        {
            throw new InvalidArgumentException(sprintf('Value "%s" is not instance of "%s".', (is_object($value) ? get_class($value) : gettype($value)), __NAMESPACE__ . '\\BaseInterface'));
        }

        return Object::set($property, $value);
    }

    public function setContent(AbstractContent $content)
    {
        foreach ($this->data AS BaseInterface $field)
        {
            if ($field::IS_FIELD && $field::IS_FILTER)
            {
                $field->setContent();
            }
        }

        return $this;
    }

    public function render(AbstractContent $content)
    {
        return implode("\n", $this->data);
    }

    public function renderFilter()
    {
        return implode("\n", array_map($this->data, function(BaseInterface $field) {
            return $field::IS_FILTER ? $field->renderFilter() : false;
        }));
    }

    public function __toString()
    {
        return $this->render();
    }
}