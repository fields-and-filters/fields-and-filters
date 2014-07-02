<?php
/**
* @package     Fieldsandfilters
* @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
* @license     GNU General Public License version 3 or later; see License.txt
* @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
*/

namespace Fieldsandfilters\Field;

use Kextensions\Object\ObjectInterface;
use Fieldsandfilters\Content\AbstractContentType;

defined('_JEXEC') or die;

/**
 * Base Interface
 *
 * @package     Fieldsandfilters
 * @since       2.0
 */
interface BaseInterface extends ObjectInterface
{
    public function isField();

    public function isFilter();

    public function isStatic();

    public function setContentType(AbstractContentType $contentType);

    public function getContentType();

    public function render();

    public function __toString();
}