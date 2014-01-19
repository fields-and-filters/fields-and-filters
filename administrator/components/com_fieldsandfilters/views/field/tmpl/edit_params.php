<?php
/**
 * @version     1.1.1
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

defined('_JEXEC') or die;

$fieldSets = $this->form->getFieldsets('params');

foreach ($fieldSets AS $name => $fieldSet)
{
    if (isset($fieldSet->parent) && isset($fieldSets[$fieldSet->parent]))
    {
        $fieldSets[$fieldSet->parent]->children[$name] = $fieldSet;
        unset($fieldSets[$name]);
    }
}

foreach ($fieldSets AS $name => $fieldSet)
{
    echo JHtml::_('bootstrap.addTab', 'myTab', 'params_'.$name, JText::_('COM_FIELDSANDFILTERS_' . strtoupper($name) . '_FIELDSET_LABEL', true));

    if (isset($fieldSet->children))
    {
        echo JHtml::_('bootstrap.startAccordion', 'menuParmasType');

        foreach ($fieldSet->children AS $nameChild => $fieldSetChild)
        {
            $this->set('fieldset', $nameChild);

            echo JHtml::_( 'bootstrap.addSlide', 'menuOptions', JText::_( 'COM_FIELDSANDFILTERS_' . strtoupper($name) . '_' . strtoupper($nameChild) . '_FIELDSET_LABEL', true ), 'params_'.$name.'_'.$nameChild );
            echo JLayoutHelper::render('joomla.edit.fieldset', $this);
            echo JHtml::_( 'bootstrap.endSlide' );
        }

        echo JHtml::_( 'bootstrap.endAccordion' );
    }

    $this->set('fieldset', $name);
    echo JLayoutHelper::render('joomla.edit.fieldset', $this);

    echo JHtml::_('bootstrap.endTab');
}