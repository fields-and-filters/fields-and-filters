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
 * Field Interface
 *
 * @package     Fieldsandfilters
 * @since       2.0
 */
interface FieldInterface extends BaseInterface
{
    public function setContent(AbstractContent $content);

    public function getContent();

    public function getData();

    public function setData();
}