<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    pkg_guidedtourstoolkit
 *
 * @copyright     (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\Database\Exception\ExecutionFailureException;

class Pkg_GuidedtourstoolkitInstallerScript extends InstallerScript
{
    public function __construct($installer)
    {
        $this->minimumJoomla = '4.3.0';
    }

    /**
     * Called after any type of action
     *
     * @param string $action Which action is happening (install|uninstall|discover_install|update)
     * @param InstallerAdapter $installer The object responsible for running this script
     *
     * @return boolean True on success
     */
    public function postflight($action, $installer)
    {
        if ($action === 'uninstall') {
            return true;
        }

        // enable the plugin
        $this->enableExtension('plugin', 'guidedtourstoolkit', 'system');
    }

    private function enableExtension($type, $element, $folder = '', $enable = true)
    {
        $db = Factory::getDBO();

        $query = $db->getQuery(true);

        $query->update($db->quoteName('#__extensions'));
        if ($enable) {
            $query->set($db->quoteName('enabled').' = 1');
        } else {
            $query->set($db->quoteName('enabled').' = 0');
        }
        $query->where($db->quoteName('type').' = '.$db->quote($type));
        $query->where($db->quoteName('element').' = '.$db->quote($element));
        if ($folder) {
            $query->where($db->quoteName('folder').' = '.$db->quote($folder));
        }

        $db->setQuery($query);

        try {
            $db->execute();
        } catch (ExecutionFailureException $e) {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
            return false;
        }

        return true;
    }
}
