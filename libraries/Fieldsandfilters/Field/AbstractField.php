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
abstract class AbstractField extends AbstractBase implements FieldInterface
{
    /**
     * {@inheritdoc}
     */
    const IS_FIELD = true;

    const RENDER_LAYOUT_TYPE = 'field';

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
        if (!$this->content instanceof AbstractContent)
        {
            throw new \InvalidArgumentException(sprintf('Content property is not instance of "%s".', 'Fieldsandfilters\Content\AbstractContent'));
        }

        return $this->content->getData($this->id);
    }

    public function setData($data)
    {
        if (!$this->content instanceof AbstractContent)
        {
            throw new \InvalidArgumentException(sprintf('Content property is not instance of "%s".', 'Fieldsandfilters\Content\AbstractContent'));
        }

        return $this->content->setData($this->id, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        if (!$this->content instanceof AbstractContent)
        {
            throw new \InvalidArgumentException(sprintf('Content property is not instance of "%s".', 'Fieldsandfilters\Content\AbstractContent'));
        }

        return parent::render();
    }
}