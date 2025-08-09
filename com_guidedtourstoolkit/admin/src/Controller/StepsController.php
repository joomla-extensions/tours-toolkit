<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtourstoolkit
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtourstoolkit\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 *
 */
class StepsController extends AdminController
{
    /**
     * Method to import steps from a .csv file
     *
     * @return  void
     *
     * @throws  \Exception
     */
    public function importcsv()
    {
        // Access checks.
        if (!$this->app->getIdentity()->authorise('core.create', 'com_guidedtours')) {
            throw new \Exception(Text::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
        }

        // Check for request forgeries.
        $this->checkToken();

        $data      = $this->input->post->get('jform', [], 'array');
        $formfiles = $this->input->files->get('jform', [], 'array');
        $file      = $formfiles['importcsvfile'];
        $redirect  = Route::_('index.php?option=com_guidedtourstoolkit&view=import' . $this->getRedirectToListAppend(), false);

        // Check if the file exists.
        if (!isset($file['name'])) {
            $this->setRedirect($redirect, Text::_('COM_GUIDEDTOURSTOOLKIT_IMPORT_INVALID_REQUEST'), 'error');
            return;
        }

        // Check if there was a problem uploading the file.
        if ($file['error']) {
            $this->setRedirect($redirect, Text::_('COM_GUIDEDTOURSTOOLKIT_IMPORT_FILE_ERROR'), 'error');
            return;
        }

        // Check if the file has the right file type.
        if ($file['type'] !== 'text/csv') {
            $this->setRedirect($redirect, Text::_('COM_GUIDEDTOURSTOOLKIT_IMPORT_WRONG_FILE_TYPE_ERROR'), 'error');
            return;
        }

        if (!is_file($file['tmp_name'])) {
            $this->setRedirect($redirect, Text::_('COM_GUIDEDTOURSTOOLKIT_IMPORT_MISSING_FILE_ERROR'), 'error');
            return;
        }

        // Load the file data.
//         $data = file_get_contents($file['tmp_name']);

//         if ($data === false) {
//             $this->setRedirect($redirect, Text::_('COM_GUIDEDTOURSTOOLKIT_IMPORT_PARSING_FILE_ERROR'), 'error');
//             return;
//         }

        $model = $this->getModel();

        try {
            // Set default message on error - overwrite if successful
            $this->setMessage(Text::_('COM_GUIDEDTOURSTOOLKIT_STEPS_IMPORT_NO_STEP_IMPORTED'), 'error');

            if ($count = $model->importcsv($file['tmp_name'], $data['tourid'])) {
                $this->setMessage(Text::plural('COM_GUIDEDTOURSTOOLKIT_STEPS_IMPORTED', $count));
            }
        } catch (\Exception $e) {
            $this->app->enqueueMessage($e->getMessage(), 'warning');
        }

        $this->setRedirect($redirect);
    }

}
