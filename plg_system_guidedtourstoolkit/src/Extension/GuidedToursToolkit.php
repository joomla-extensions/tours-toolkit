<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.guidedtourstoolkit
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\GuidedToursToolkit\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\Button\LinkButton;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * System plugin to add guided tours actions to the administrator interface.
 */
final class GuidedToursToolkit extends CMSPlugin implements SubscriberInterface
{
   /**
    * Returns an array of CMS events this plugin will listen to and the respective handlers.
    *
    * @return array
    */
    public static function getSubscribedEvents(): array
    {
        return  [
            'onBeforeRender' => 'onBeforeRender',
        ];
    }

    /**
     *
     */
    public function onBeforeRender()
    {
        if (!$this->getApplication()->isClient('administrator')) {
            return;
        }

        // Get the input object
        $input = $this->getApplication()->getInput();

        $this->loadLanguage();

        // Append button just on Tours
        if ($input->getCmd('option') === 'com_guidedtours' && $input->getCmd('view', 'tours') === 'tours') {

            $user = $this->getApplication()->getIdentity();
            if (!$user->authorise('core.admin', 'com_guidedtourstoolkit')) {
                return;
            }

            // Get the toolbar object instance
            $toolbar = Toolbar::getInstance('toolbar');

            $button = (new LinkButton('toolkit'))
                ->text('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_TOOLKIT')
                ->url('index.php?option=com_guidedtourstoolkit&view=info')
                ->icon('icon-cog');

            $toolbar->appendButton($button);
        }

        // Append button on individual tours
        if ($input->getCmd('option') === 'com_guidedtours' && $input->getCmd('view', 'tours') === 'tour') {

            $user = $this->getApplication()->getIdentity();
            if (!$user->authorise('core.export', 'com_guidedtourstoolkit')) {
                return;
            }

            // Get the toolbar object instance
            $toolbar = Toolbar::getInstance('toolbar');

            $button = (new LinkButton('export'))
            ->text('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_EXPORT')
            ->url('index.php?option=com_guidedtourstoolkit&task=tour.export&id=' . $input->getInt('id', 0))
            ->icon('icon-upload');

            $toolbar->appendButton($button);
        }
    }
}
