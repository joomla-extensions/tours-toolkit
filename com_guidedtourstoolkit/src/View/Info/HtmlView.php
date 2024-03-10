<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtourstoolkit
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtourstoolkit\Administrator\View\Info;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Button\LinkButton;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for the guidedtourstoolkit dashboard
 */
class HtmlView extends BaseHtmlView
{
    protected $form;

    /**
     * display method of view
     * @return void
     */
    function display($tpl = null)
    {
        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Setting the toolbar
     */
    protected function addToolbar()
    {
        // Get the toolbar object instance
        $toolbar = Toolbar::getInstance('toolbar');

        ToolbarHelper::title(Text::_('COM_GUIDEDTOURSTOOLKIT'), 'map-signs');

        $canDo = ContentHelper::getActions('com_guidedtours');
        $user  = Factory::getApplication()->getIdentity();

        $button = (new LinkButton('guidedtours'))
            ->text('COM_GUIDEDTOURSTOOLKIT_LINKBUTTON_GUIDEDTOURS')
            ->url('index.php?option=com_guidedtours&view=tours')
            ->icon('icon-cog');

        $toolbar->appendButton($button);
    }

}
