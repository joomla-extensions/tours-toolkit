<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtourstoolkit
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtourstoolkit\Administrator\Controller;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Version;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 *
 */
class TourController extends AdminController
{
    public function export()
    {
        $pks = (array) $this->input->getInt('id');
        $pks = array_filter($pks);
        try {
            if (empty($pks)) {
                throw new \Exception(Text::_('COM_GUIDEDTOURSTOOLKIT_ERROR_NO_GUIDEDTOURS_SELECTED'));
            }

            $factory = $this->app->bootComponent('com_guidedtours')->getMVCFactory();
            $tourModel = $factory->createModel('Tour', 'Administrator', ['ignore_request' => true]);
            $stepsModel = $factory->createModel('Steps', 'Administrator', ['ignore_request' => true]);

            $data           = [];
            $tours_data     = [];
            $last_tour_name = '';

            foreach ($pks as $pk) {
                // Get the tour data.
                $tour = $tourModel->getItem($pk);

                $last_tour_name = OutputFilter::stringURLSafe(Text::_($tour->title));

                $tour_data = [
                    'title'       => $tour->title,
                    'description' => $tour->description,
                    'extensions'  => $tour->extensions,
                    'url'         => $tour->url,
                    'published'   => 0,
                    'language'    => $tour->language,
                    'note'        => $tour->note,
                    'access'      => $tour->access,
                ];

                if (isset($tour->uid)) {
                    $tour_data['uid'] = $tour->uid;
                    $last_tour_name   = $tour->uid;
                }

                if (isset($tour->autostart)) {
                    $tour_data['autostart'] = $tour->autostart;
                }

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

                    if (isset($step->params)) {
                        $step_data['params'] = $step->params;
                    }

                    $steps_data[] = $step_data;
                }

                $tour_data['steps'] = $steps_data;

                $tours_data[] = $tour_data;
            }

            $data['tours'] = $tours_data;

            $this->setMessage(Text::plural('COM_GUIDEDTOURSTOOLKIT_TOURS_EXPORTED', \count($pks)));

            $name = 'guidedtours';
            if (\count($pks) == 1) {
                $name = 'guidedtour_' . $last_tour_name;
            }

            $version       = new Version();
            $joomlaVersion = '_joomla-' . OutputFilter::stringURLSafe($version->getShortVersion());

            $dateTime = '_' . date('Y-m-d') . '_' . date('H-i-s');

            $this->app->setHeader('Content-Type', 'application/json', true)
                ->setHeader('Content-Disposition', 'attachment; filename="' . $name . $joomlaVersion . $dateTime . '.json"', true)
                ->setHeader('Cache-Control', 'must-revalidate', true)
                ->sendHeaders();

            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            $this->app->close();

        } catch (\Exception $e) {
            $this->app->enqueueMessage($e->getMessage(), 'warning');
        }
        $this->setRedirect(Route::_('index.php?option=com_guidedtourstoolkit&view=tours' . $this->getRedirectToListAppend(), false));
    }
}
