<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.guidedtourstoolkit
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\GuidedToursToolkit\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
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
            'onBeforeCompileHead' => 'onBeforeCompileHead',
            'onBeforeRender'      => 'onBeforeRender',
        ];
    }

    /**
     * Listener for the `onBeforeCompileHead` event
     *
     * @return  void
     */
    public function onBeforeCompileHead()
    {
        $app    = $this->getApplication();
        $doc    = $app->getDocument();
        $user   = $app->getIdentity();
        $params = ComponentHelper::getParams('com_guidedtourstoolkit');

        if ($user != null && $user->id > 0 && $params->get('selectorTool', 1)) {
            // Load plugin language files.
            $this->loadLanguage();

            Text::script('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_SELECTORTOOL_COPIED');
            Text::script('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_SELECTORTOOL_COPY');
            Text::script('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_SELECTORTOOL_EXPLAIN_FANCYSELECT');
            Text::script('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_SELECTORTOOL_EXPLAIN_HREF');
            Text::script('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_SELECTORTOOL_EXPLAIN_NUMBERS');
            Text::script('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_SELECTORTOOL_EXPLAIN_ONLY_IN_MONOLINGUAL');
            Text::script('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_SELECTORTOOL_OFF');
            Text::script('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_SELECTORTOOL_ON');
            Text::script('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_SELECTORTOOL_ONOFF');
            Text::script('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_SELECTORTOOL_SELECT_ELEMENT');
            Text::script('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_SELECTORTOOL_UNUSABLE_SELECTOR');

            HTMLHelper::_(
                'stylesheet',
                'plg_system_guidedtourstoolkit/selectortool.min.css',
                ['version' => 'auto', 'relative' => true]
                );

            HTMLHelper::_(
                'script',
                'plg_system_guidedtourstoolkit/selectortool.min.js',
                ['version' => 'auto', 'relative' => true],
                ['type' => 'module'],
                ['core']
                );

            // Load required assets.
            // Cannot make this work
            //$doc->getWebAssetManager()
                //->usePreset('plg_system_guidedtourstoolkit.selectortool');
        }
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
                ->url('index.php?option=com_guidedtourstoolkit&view=import')
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

            $dropdown = $toolbar->dropdownButton('export-group')
                ->text('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_EXPORT')
                ->toggleSplit(false)
                ->icon('icon-upload')
                ->buttonClass('btn btn-action');

            $childBar = $dropdown->getChildToolbar();

            $button = (new LinkButton('exportsql'))
                ->text('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_EXPORTSQL')
                ->url('index.php?option=com_guidedtourstoolkit&task=tour.exportsql&id=' . $input->getInt('id', 0))
                ->icon('icon-upload');

            $childBar->appendButton($button);

            $button = (new LinkButton('exportsqlini'))
                ->text('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_EXPORTSQLINI')
                ->url('index.php?option=com_guidedtourstoolkit&task=tour.exportsqlini&id=' . $input->getInt('id', 0))
                ->icon('icon-upload');

            $childBar->appendButton($button);

            $button = (new LinkButton('exportjson'))
                ->text('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_EXPORTJSON')
                ->url('index.php?option=com_guidedtourstoolkit&task=tour.exportjson&id=' . $input->getInt('id', 0))
                ->icon('icon-upload');

            $childBar->appendButton($button);
        }
    }
}
