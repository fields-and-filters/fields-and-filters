<?php
/**
* @package     Fieldsandfilters
* @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
* @license     GNU General Public License version 3 or later; see License.txt
* @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
*/

namespace Fieldsandfilters\Field;

use Fieldsandfilters\Content\AbstractContent;

defined('_JEXEC') or die;

/**
 * Abstract Field
 *
 * @package     Fieldsandfilters
 * @since       2.0
 */
abstract class AbstractObject extends AbstractBase implements FieldInterface
{
    /**
     * {@inheritdoc}
     */
    const _CLASS_ = __CLASS__;

    /**
     * {@inheritdoc}
     */
    protected $isField = true;

    protected $content;

    /**
     * @param AbstractContent $content
     */
    public function setContent(AbstractContent $content)
    {
        $this->content = $content;
    }

    /**
     * @return AbstractContent
     */
    public function getContent()
    {
        return $this->content;
    }

    public function getData()
    {
        if (!$this->content instanceof AbstractContent::_CLASS_)
        {
            throw new \InvalidArgumentException(sprintf('Content property is not instance of "%s".', AbstractContent::_CLASS_));
        }

        return $this->content->getData($this->id);
    }

    /**
     * {@inheritdoc}
     */
    public function render(AbstractContent $content = null)
    {
        if ($content !== null)
        {
            $this->setContent($content);
        }

        return '';
    }
}