<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtourstoolkit
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtourstoolkit\Administrator\Field;

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Guidedtours\Administrator\Helper\GuidedtoursHelper;
use Joomla\Database\Exception\ExecutionFailureException;

class ToursField extends ListField
{
    public $type = 'Tours';

    public function getOptions()
    {
        $options = array();

        $db = Factory::getDBO();
        $query = $db->getQuery(true);

        $user = Factory::getApplication()->getIdentity();

        $query->select($db->quoteName('id'));
        if (version_compare(JVERSION, '5.0', '>=')) {
            $query->select($db->quoteName('uid'));
        }
        $query->select($db->quoteName('title'));
        $query->from($db->quoteName('#__guidedtours'));
        $query->where($db->quoteName('published') . ' IN (0,1)');
        $query->where($db->quoteName('created_by') . ' = ' . $user->id);
        if (version_compare(JVERSION, '5.0', '>=')) {
            $query->where($db->quoteName('uid') . ' NOT LIKE ' . $db->quote('joomla-%'));
        }
        $query->order($db->quoteName('ordering'));

        $db->setQuery($query);

        try {
            $tours = $db->loadObjectList();
            foreach ($tours as $tour) {
                if (version_compare(JVERSION, '5.0', '>=') && !empty($tour->uid)) {
                    GuidedtoursHelper::loadTranslationFiles($tour->uid, false);
                }
                $options[] = HTMLHelper::_('select.option', $tour->id, Text::_($tour->title));
            }
        } catch (ExecutionFailureException $e) {
            //return false;
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }

}
