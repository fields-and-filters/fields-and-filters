<?php

/**
 * @package     Kextensions
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

namespace Kextensions\Buffer;

use Kextensions\Rule\ManagerInterface;
use Kextensions\Rule\Locator;

defined('_JEXEC') or die;

/**
 * Buffer
 *
 * @package     Kextensions
 * @since       2.0
 *
 * [TODO] add PHPDock Blocks
 * [TODO] add Tests
 */
abstract class Buffer implements BufferInterface, ManagerInterface
{
    /**
     * The rules to be applied to a data object.
     *
     * @var array
     */
    protected $rules = array();

    public function get()
    {
        $this->prepare();

        if ($this->needLoad())
        {
            $data = $this->load();
            $this->bind($data);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return Buffer Current instance.
     */
    public function addRule($name, $field, $method = Buffer::IS)
    {
        $this->rules[] = array(
            'name' => $name,
            'field' => $field,
            'params' => array_slice(func_get_args(), 3),
            'method' => $method
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return Buffer Current instance.
     */
    public function clearRule()
    {
        $this->rules = array();

        return $this;
    }

    protected function call(array $info)
    {
        $rule = Locator::get($info['name']);

        return call_user_func_array(array($rule, $info['method']), $info['params']);
    }

    abstract protected function prepare();

    abstract protected function load();

    abstract protected function needLoad();

    abstract protected function bind($data);
}