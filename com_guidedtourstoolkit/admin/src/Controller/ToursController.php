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
class ToursController extends AdminController
{
    public function export()
    {
        $this->checkToken();
        $pks = (array) $this->input->post->get('cid', [], 'int');
        $pks = array_filter($pks);
        try {
            if (empty($pks)) {
                throw new \Exception(Text::_('COM_GUIDEDTOURSTOOLKIT_ERROR_NO_GUIDEDTOURS_SELECTED'));
            }

            $factory = $this->app->bootComponent('com_guidedtours')->getMVCFactory();
            $tourModel = $factory->createModel('Tour', 'Administrator', ['ignore_request' => true]);
            $stepsModel = $factory->createModel('Steps', 'Administrator', ['ignore_request' => true]);

            $data       = [];
            $tours_data = [];

            foreach ($pks as $pk) {
                // Get the tour data.
                $tour = $tourModel->getItem($pk);

                $tour_data = [
                    'title'       => $tour->title,
                    'description' => $tour->description,
                    'extensions'  => $tour->extensions,
                    'url'         => $tour->url,
                    'published'   => $tour->published,
                    'language'    => $tour->language,
                    'note'        => $tour->note,
                    'access'      => $tour->access,
                ];

                // Get the steps data.
                $stepsModel->setState('filter.tour_id', $pk);
                $steps = $stepsModel->getItems($pk);

                $steps_data = [];

                foreach ($steps as $step) {
                    $step_data = [
                        'title'            => $step->title,
                        'description'      => $step->description,
                        'position'         => $step->position,
                        'target'           => $step->target,
                        'type'             => $step->type,
                        'interactive_type' => $step->interactive_type,
                        'url'              => $step->url,
                        'published'        => $step->published,
                        'language'         => $step->language,
                        'note'             => $step->note,
                    ];

                    $steps_data[] = $step_data;
                }

                $tour_data['steps'] = $steps_data;

                $tours_data[] = $tour_data;
            }

            $data['tours'] = $tours_data;

            $this->setMessage(Text::plural('COM_GUIDEDTOURSTOOLKIT_TOURS_EXPORTED', \count($pks)));

            $this->app->setHeader('Content-Type', 'application/json', true)
                ->setHeader('Content-Disposition', 'attachment; filename="' . $this->input->getCmd('view', 'joomla') . '.json"', true)
                ->setHeader('Cache-Control', 'must-revalidate', true)
                ->sendHeaders();

            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            $this->app->close();

        } catch (\Exception $e) {
            $this->app->enqueueMessage($e->getMessage(), 'warning');
        }
        $this->setRedirect(Route::_('index.php?option=com_guidedtourstoolkit&view=tours' . $this->getRedirectToListAppend(), false));
    }

    /**
     * Method to import tours from a .json file
     *
     * @return  void
     *
     * @throws  \Exception
     */
    public function importjson()
    {
        // Access checks.
        if (!$this->app->getIdentity()->authorise('core.create', 'com_guidedtours')) {
            throw new \Exception(Text::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
        }

        // Check for request forgeries.
        $this->checkToken();

        $formfiles = $this->input->files->get('jform', [], 'array');
        $file      = $formfiles['importjsonfile'];
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
        if ($file['type'] !== 'application/json') {
            $this->setRedirect($redirect, Text::_('COM_GUIDEDTOURSTOOLKIT_IMPORT_WRONG_FILE_TYPE_ERROR'), 'error');
            return;
        }

        if (!is_file($file['tmp_name'])) {
            $this->setRedirect($redirect, Text::_('COM_GUIDEDTOURSTOOLKIT_IMPORT_MISSING_FILE_ERROR'), 'error');
            return;
        }

        // Load the file data.
        $data = file_get_contents($file['tmp_name']);

        if ($data === false) {
            $this->setRedirect($redirect, Text::_('COM_GUIDEDTOURSTOOLKIT_IMPORT_PARSING_FILE_ERROR'), 'error');
            return;
        }

        $model = $this->getModel();

        try {
            // Set default message on error - overwrite if successful
            $this->setMessage(Text::_('COM_GUIDEDTOURSTOOLKIT_TOURS_IMPORT_NO_TOUR_IMPORTED'), 'error');

            if ($count = $model->importjson($data)) {
                $this->setMessage(Text::plural('COM_GUIDEDTOURSTOOLKIT_TOURS_IMPORTED', $count));
            }
        } catch (\Exception $e) {
            $this->app->enqueueMessage($e->getMessage(), 'warning');
        }

        $this->setRedirect($redirect);
    }

}
