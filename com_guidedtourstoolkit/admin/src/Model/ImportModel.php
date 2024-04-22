<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtourstoolkit
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtourstoolkit\Administrator\Model;

use Joomla\CMS\MVC\Model\AdminModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Model class for Import
 */
class ImportModel extends AdminModel
{
    /**
     *
     * {@inheritDoc}
     * @see \Joomla\CMS\MVC\Model\FormModelInterface::getForm()
     */
    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_guidedtourstoolkit.import', 'import', ['control' => 'jform', 'load_data' => $loadData]);
        
        if (empty($form)) {
            return false;
        }
        
        return $form;
    }
}
