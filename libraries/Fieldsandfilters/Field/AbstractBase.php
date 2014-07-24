<?php
/**
* @package     Fieldsandfilters
* @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
* @license     GNU General Public License version 3 or later; see License.txt
* @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
*/

namespace Fieldsandfilters\Field;

use Kextensions\Object\Object;
use Fieldsandfilters\Content\AbstractContentType;

defined('_JEXEC') or die;

/**
 * Abstract Base
 *
 * @package     Fieldsandfilters
 * @since       2.0
 */
abstract class AbstractBase extends Object implements BaseInterface
{
    const IS_FIELD = false;

    const IS_FILTER = false;

    const IS_STATIC = false;

    const PLUGIN_TYPE = 'FieldsandfiltersType';

    const RENDER_LAYOUT_TYPE = 'base';

    protected $contentType;

    /**
     * {@inheritdoc}
     */
    public function setContentType(AbstractContentType $contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    public function getLayout($layoutType)
    {
        return \JPluginHelper::getLayoutPath(static::PLUGIN_TYPE, $this->type,
            sprintf('%s/%s',
                $layoutType,
                $this->params->get(sprintf('type.%slayout', $layoutType), 'default')
            )
        );
    }

    public function render()
    {
        return $this->prepareRender(self::RENDER_LAYOUT_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->render();
    }

    protected function prepareRender($layoutType)
    {
        // Start capturing output into a buffer
        ob_start();

        // Include the requested template filename in the local scope
        // (this will execute the view logic).
        include $this->getLayout(self::RENDER_LAYOUT_TYPE);

        // Done with the requested template; get the buffer and
        // clear it.
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }
}