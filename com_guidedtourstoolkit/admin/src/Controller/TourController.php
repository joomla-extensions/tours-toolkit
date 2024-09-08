<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtourstoolkit
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtourstoolkit\Administrator\Controller;

use Joomla\CMS\Application\ApplicationHelper;
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
    /**
     * Method to export tours as a text file containing MySQL and PostgreSQL
     */
    public function exportsql()
    {
        $this->exportdb();
    }

    /**
     * Method to export tours as a text file containing MySQL and PostgreSQL
     * Language keys are created as well and replace the text in the SQL
     */
    public function exportsqlini()
    {
        $this->exportdb(true);
    }

    /**
     * Method to export a tour as an array
     *
     * @param   integer  $id  The id of the tour.
     *
     * @return  array    the tour data. May return an empty array on failure
     */
    protected function export($id)
    {
        $factory   = $this->app->bootComponent('com_guidedtours')->getMVCFactory();
        $tourModel = $factory->createModel('Tour', 'Administrator', ['ignore_request' => true]);

        $factory    = $this->app->bootComponent('com_guidedtourstoolkit')->getMVCFactory();
        $stepsModel = $factory->createModel('Steps', 'Administrator', ['ignore_request' => true]);

        // Get the tour data.
        $tour = $tourModel->getItem($id);

        if ($tour === false) {
            return [];
        }

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

        if (isset($tour->uid)) {
            $tour_data['uid'] = $tour->uid;
        }

        if (isset($tour->autostart)) {
            $tour_data['autostart'] = $tour->autostart;
        }

        // Get the steps data.
        $stepsModel->setState('filter.tour_id', $id);

        // CANNOT USE getItems() from com_guidedtours because it translates the title and description, which is WRONG
        $steps = $stepsModel->getItems();

        $tour_data['steps'] = [];

        if ($steps !== false) {

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
        }

        return $tour_data;
    }

    /**
     * Method to export tours as a text file containing MySQL and PostgreSQL
     * Language keys are created if necessary
     *
     * @param   boolean   $includeLanguageKeys
     */
    protected function exportdb($includeLanguageKeys = false)
    {
        $pks = (array) $this->input->getInt('id');
        $pks = array_filter($pks);

        try {
            if (empty($pks)) {
                throw new \Exception(Text::_('COM_GUIDEDTOURSTOOLKIT_ERROR_NO_GUIDEDTOURS_SELECTED'));
            }

            $tours_data     = [];
            $tour_count     = 0;
            $last_tour_name = '';

            foreach ($pks as $pk) {
                $tour_data = $this->export($pk);

                if (empty($tour_data)) {
                    continue;
                }

                ++$tour_count;

                if (isset($tour_data['uid']) && !empty($tour_data['uid'])) {
                    $last_tour_name = $tour_data['uid'];
                } else {
                    $last_tour_name = OutputFilter::stringURLSafe(Text::_($tour_data['title']));
                }

                $tours_data[] = $tour_data;
            }

            $this->setMessage(Text::plural('COM_GUIDEDTOURSTOOLKIT_TOURS_EXPORTED', $tour_count));

            if ($tour_count == 0) {
                $this->setRedirect(Route::_('index.php?option=com_guidedtourstoolkit&view=tours' . $this->getRedirectToListAppend(), false));
                return;
            }

            $name = 'guidedtours';
            if ($tour_count == 1) {
                $name = 'guidedtour_' . $last_tour_name;
            }

            $version       = new Version();
            $joomlaVersion = '_joomla-' . OutputFilter::stringURLSafe($version->getShortVersion());

            $dateTime = '_' . date('Y-m-d') . '_' . date('H-i-s');

            $this->app->setHeader('Content-Type', 'text/plain', true)
                ->setHeader('Content-Disposition', 'attachment; filename="' . $name . $joomlaVersion . $dateTime . '.txt"', true)
                ->setHeader('Cache-Control', 'must-revalidate', true)
                ->sendHeaders();

            if ($includeLanguageKeys) {
                // output the language keys

                echo Text::_('COM_GUIDEDTOURSTOOLKIT_MESSAGE_REPLACE_COM_GUIDEDTOURS') . "\n\n";

                $language_keys = $this->generateTourINI($tour_data);
                if ($language_keys) {
                    if (isset($tour_data['uid']) && !empty($tour_data['uid'])) {
                        echo '-- guidedtours.' . str_replace(['-'], ['_'], $tour_data['uid']) . '.ini' . "\n\n";
                    }

                    echo $language_keys;
                    echo "\n";
                }

                $language_keys = $this->generateStepsINI($tour_data);
                if ($language_keys) {
                    if (isset($tour_data['uid']) && !empty($tour_data['uid'])) {
                        echo '-- guidedtours.' . str_replace(['-'], ['_'], $tour_data['uid']) . '_steps.ini' . "\n\n";
                    }

                    echo $language_keys;
                    echo "\n";
                }
            }

            // output MySQL

            echo '-- mySQL';

            foreach ($tours_data as $tour_data) {
                echo "\n\n" . $this->generateTourSQL($tour_data, $includeLanguageKeys, '`') . ';';
                if (!empty($tour_data['steps'])) {
                    echo "\n\n" . $this->generateStepsSQL($tour_data, $includeLanguageKeys, '`') . ';';
                }
            }

            // output postgreSQL

            echo "\n\n" . '-- postgreSQL';

            foreach ($tours_data as $tour_data) {
                echo "\n\n" . $this->generateTourSQL($tour_data, $includeLanguageKeys) . ';';
                if (!empty($tour_data['steps'])) {
                    echo "\n\n" . $this->generateStepsSQL($tour_data, $includeLanguageKeys) . ';';
                }
            }

            // uninstall SQL

            echo "\n\n" . '-- uninstall mySQL';

            if (!empty($tour_data['steps'])) {
                echo "\n\n" . $this->generateUninstallStepsSQL($tour_data, '`') . ';';
            }
            echo "\n\n" . $this->generateUninstallTourSQL($tour_data, '`') . ';';

            echo "\n\n" . '-- uninstall postgreSQL';

            if (!empty($tour_data['steps'])) {
                echo "\n\n" . $this->generateUninstallStepsSQL($tour_data) . ';';
            }
            echo "\n\n" . $this->generateUninstallTourSQL($tour_data) . ';';

            $this->app->close();

        } catch (\Exception $e) {
            $this->app->enqueueMessage($e->getMessage(), 'warning');
        }

        $this->setRedirect(Route::_('index.php?option=com_guidedtourstoolkit&view=tours' . $this->getRedirectToListAppend(), false));
    }

    /**
     * Generate the prefix of the language keys
     *
     * @param  array $tour
     * @return string
     */
    protected function generateLanguagePrefix($tour)
    {
        $prefix = 'COM_GUIDEDTOURS_TOUR_';

        if (isset($tour['uid']) && !empty($tour['uid'])) {
            $uid_array = explode('-', $tour['uid']);
            unset($uid_array[0]); // Remove the first part of the uid (for instance 'joomla-')
            $tourName = implode('_', $uid_array);
        } else {
            $tourName = str_replace(['-'], ['_'], ApplicationHelper::stringURLSafe($tour['title'], 'en-GB'));
        }

        return $prefix . strtoupper($tourName);
    }

    /**
     * Generate tour language keys for the INI files
     *
     * @param  array $tour
     * @return string
     */
    protected function generateTourINI($tour)
    {
        $output = '';

        $language_key_prefix = $this->generateLanguagePrefix($tour);
        $language_keys       = [];

        $language_keys[$language_key_prefix . '_DESCRIPTION'] = str_replace(["\r", "\n", "\""], ['', '', '\"'], $tour['description']);
        $language_keys[$language_key_prefix . '_TITLE'] = $tour['title'];

        if (!empty($language_keys)) {
            foreach($language_keys as $key => $value) {
                $output .= $key . ' = "' . $value . '"' . "\n";
            }
        }

        return $output;
    }

    /**
     * Generate step language keys for the INI files
     *
     * @param  array $tour
     * @return string
     */
    protected function generateStepsINI($tour)
    {
        $output = '';

        $language_key_prefix = $this->generateLanguagePrefix($tour);
        $language_keys       = [];

        foreach ($tour['steps'] as $step_key => $step) {
            $language_keys[$language_key_prefix . '_STEP_' . $step_key . '_DESCRIPTION'] = str_replace(["\r", "\n", "\""], ['', '', '\"'], $step['description']);
            $language_keys[$language_key_prefix . '_STEP_' . $step_key . '_TITLE'] = $step['title'];
        }

        if (!empty($language_keys)) {
            foreach($language_keys as $key => $value) {
                $output .= $key . ' = "' . $value . '"' . "\n";
            }
        }

        return $output;
    }

    /**
     * Generate tour SQL
     *
     * @param  array $tour
     * @param  string $quotes to differentiate between MySQL and PostgreSQL
     *
     * @return string
     */
    protected function generateTourSQL($tour, $includeLanguageKeys = false, $quotes = '"')
    {
        $keys = array_keys($tour);
        if (!in_array('created', $keys)) {
            $keys[] = 'created';
        }
        if (!in_array('created_by', $keys)) {
            $keys[] = 'created_by';
        }
        if (!in_array('modified', $keys)) {
            $keys[] = 'modified';
        }
        if (!in_array('modified_by', $keys)) {
            $keys[] = 'modified_by';
        }

        $keys_to_escape = ['title', 'description', 'uid', 'extensions', 'url', 'language', 'note'];

        $output_array = [];
        foreach($keys as $key) {
            if ($key == 'steps') {
                continue;
            }
            $output_array[] = $quotes . $key . $quotes;
        }

        $output = 'INSERT INTO ' . $quotes . '#__guidedtours' . $quotes . ' (' . implode(', ', $output_array) . ') VALUES' . "\n";

        if ($includeLanguageKeys) {
            $language_key_prefix = $this->generateLanguagePrefix($tour);
        }

        $output_array = [];
        foreach($keys as $key) {

            if ($key == 'steps') {
                continue;
            }
            if ($key == 'created' || $key == 'modified') {
                $output_array[] = 'CURRENT_TIMESTAMP' . ($quotes !== '"' ? '()' : '');
                continue;
            }
            if ($key == 'created_by' || $key == 'modified_by') {
                $output_array[] = '0';
                continue;
            }

            if ($includeLanguageKeys && ($key == 'title' || $key == 'description')) {
                $tour[$key] = $language_key_prefix . '_' . strtoupper($key);
            }

            $output_array[] = in_array($key, $keys_to_escape) ? '\'' . $tour[$key] . '\'' : $tour[$key];
        }

        return $output . '(' . implode(', ', $output_array) . ')';
    }

    /**
     * Generate steps SQL
     *
     * @param  array    $tour
     * @param  boolean  $includeLanguageKeys
     * @param  string   $quotes to differentiate between MySQL and PostgreSQL
     *
     * @return string
     *
     */
    protected function generateStepsSQL($tour, $includeLanguageKeys = false, $quotes = '"')
    {
        $output = '';

        $keys = array_keys($tour['steps'][0]);
        if (!in_array('tour_id', $keys)) {
            $keys[] = 'tour_id';
        }
        if (!in_array('created', $keys)) {
            $keys[] = 'created';
        }
        if (!in_array('created_by', $keys)) {
            $keys[] = 'created_by';
        }
        if (!in_array('modified', $keys)) {
            $keys[] = 'modified';
        }
        if (!in_array('modified_by', $keys)) {
            $keys[] = 'modified_by';
        }

        $keys_to_escape = ['title', 'description', 'position', 'target', 'url', 'language', 'note', 'params'];

        $output_array = [];
        foreach($keys as $key) {
            $output_array[] = $quotes . $key . $quotes;
        }

        $output .= 'INSERT INTO ' . $quotes . '#__guidedtour_steps' . $quotes . ' (' . implode(', ', $output_array) . ')';
        $output .= "\n" . 'SELECT ';

        if ($includeLanguageKeys) {
            $language_key_prefix = $this->generateLanguagePrefix($tour);
        }

        $output_array = [];
        foreach ($tour['steps'] as $step_key => $step) {
            $output_array_internal = [];
            foreach($keys as $key) {

                if ($key == 'tour_id') {
                    $output_array_internal[] = 'MAX(' . $quotes . 'id' . $quotes . ')';
                    continue;
                }

                if ($key == 'created' || $key == 'modified') {
                    $output_array_internal[] = 'CURRENT_TIMESTAMP' . ($quotes !== '"' ? '()' : '');
                    continue;
                }

                if ($key == 'created_by' || $key == 'modified_by') {
                    $output_array_internal[] = '0';
                    continue;
                }

                if ($includeLanguageKeys && ($key == 'title' || $key == 'description')) {
                    $step[$key] = $language_key_prefix . '_STEP_' . $step_key . '_' . strtoupper($key);
                }

                $output_array_internal[] = in_array($key, $keys_to_escape) ? '\'' . $step[$key] . '\'' : $step[$key];
            }
            $output_array[] = implode(', ', $output_array_internal);
        }

        $output .= implode(', ', $output_array);

        $output .= "\n" . '  FROM ' . $quotes . '#__guidedtours' . $quotes;
        $output .= "\n" . ' WHERE ' . $quotes . 'uid' . $quotes . ' = \'' . $tour['uid'] . '\'';

        return $output;
    }

    /**
     * Generate uninstall tour script SQL
     *
     * @param  array $tour
     * @param  string $quotes to differentiate between MySQL and PostgreSQL
     *
     * @return string
     */
    protected function generateUninstallTourSQL($tour, $quotes = '"')
    {
        $output = '';

        $output .= 'DELETE FROM ' . $quotes . '#__guidedtours' . $quotes;
        $output .= "\n" . ' WHERE ' . $quotes . 'uid' . $quotes . ' = \'' . $tour['uid'] . '\'';

        return $output;
    }

    /**
     * Generate uninstall steps script SQL
     *
     * @param  array $tour
     * @param  string $quotes to differentiate between MySQL and PostgreSQL
     *
     * @return string
     */
    protected function generateUninstallStepsSQL($tour, $quotes = '"')
    {
        $output = '';

        $output .= 'DELETE FROM ' . $quotes . '#__guidedtour_steps' . $quotes;
        $output .= "\n" . ' WHERE ' . $quotes . 'tour_id' . $quotes . ' = (SELECT ' . $quotes . 'id' . $quotes . ' FROM ' . $quotes . '#__guidedtours' . $quotes . ' WHERE ' . $quotes . 'uid' . $quotes . ' = \'' . $tour['uid'] . '\')';

        return $output;
    }

    /**
     * Method to export tours as a json file
     */
    public function exportjson()
    {
        $pks = (array) $this->input->getInt('id');
        $pks = array_filter($pks);

        try {
            if (empty($pks)) {
                throw new \Exception(Text::_('COM_GUIDEDTOURSTOOLKIT_ERROR_NO_GUIDEDTOURS_SELECTED'));
            }

            $data           = [];
            $tours_data     = [];
            $tour_count     = 0;
            $last_tour_name = '';

            foreach ($pks as $pk) {
                $tour_data = $this->export($pk);

                if (empty($tour_data)) {
                    continue;
                }

                ++$tour_count;

                if (isset($tour_data['uid']) && !empty($tour_data['uid'])) {
                    $last_tour_name = $tour_data['uid'];
                } else {
                    $last_tour_name = OutputFilter::stringURLSafe(Text::_($tour_data['title']));
                }

                $tours_data[] = $tour_data;
            }

            $this->setMessage(Text::plural('COM_GUIDEDTOURSTOOLKIT_TOURS_EXPORTED', $tour_count));

            if ($tour_count == 0) {
                $this->setRedirect(Route::_('index.php?option=com_guidedtourstoolkit&view=tours' . $this->getRedirectToListAppend(), false));
                return;
            }

            $data['tours'] = $tours_data;

            $name = 'guidedtours';
            if ($tour_count == 1) {
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
