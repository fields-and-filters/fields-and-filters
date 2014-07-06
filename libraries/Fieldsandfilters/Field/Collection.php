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
     *
     * @param BaseInterface $value The object with instance of Object.
     *
     * @return Collection Current instance.
     *
     * @throws InvalidArgumentException
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
        foreach ($this->data AS $field)
        {
            if ($field::isField && $field::isFilter)
            {
                $field->setContent();
            }
        }

        return $this;
    }

    public function render()
    {
        return implode("\n", $this->data);
    }

    function __toString()
    {
        return $this->render();
    }
}