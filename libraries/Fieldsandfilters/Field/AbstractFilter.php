<?php
/**
* @package     Fieldsandfilters
* @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
* @license     GNU General Public License version 3 or later; see License.txt
* @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
*/

namespace Fieldsandfilters\Field;

defined('_JEXEC') or die;

/**
 * Abstract Filter
 *
 * @package     Fieldsandfilters
 * @since       2.0
 */
abstract class AbstractFilter extends AbstractField implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    const isFilter = true;

    public function getData()
    {
        if (!$this->content instanceof AbstractContent)
        {
            throw new \InvalidArgumentException(sprintf('Content property is not instance of "%s".', 'Fieldsandfilters\Content\AbstractContent'));
        }

        return $this->content->getConnection($this->id);
    }
}