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
 * Abstract Static
 *
 * @package     Fieldsandfilters
 * @since       2.0
 */
abstract class AbstractStatic extends AbstractBase implements StaticInterface
{
    /**
     * {@inheritdoc}
     */
    const _CLASS_ = __CLASS__;

    /**
     * {@inheritdoc}
     */
    protected $isStatic = true;

    protected $data;

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return '';
    }
}