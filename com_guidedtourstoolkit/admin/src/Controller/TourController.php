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
                    echo "\n\n" . $this->generateStepsSQL($tour_data, $includeLanguageKeys, '`');
                }
//                if (!empty($tour_data['steps'])) {
//                    echo "\n\n" . 'UPDATE `#__guidedtour_steps` SET `tour_id` = LAST_INSERT_ID() WHERE `tour_id`=0;';
//                }
            }

            // output postgreSQL

            echo "\n\n" . '-- postgreSQL';

            foreach ($tours_data as $tour_data) {
                echo "\n\n" . $this->generateTourSQL($tour_data, $includeLanguageKeys) . ';';
                if (!empty($tour_data['steps'])) {
                    echo "\n\n" . $this->generateStepsSQL($tour_data, $includeLanguageKeys);
                }
//                if (!empty($tour_data['steps'])) {
//                    echo "\n\n" . 'UPDATE "#__guidedtour_steps" SET "tour_id" = currval(pg_get_serial_sequence(\'#__guidedtours\',\'id\')) WHERE "tour_id"=0;';
//                }
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
                $output .= $key . '="' . $value . '"' . "\n";
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
                $output .= $key . '="' . $value . '"' . "\n";
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
        $language_key_prefix = '';
        if ($includeLanguageKeys) {
            $language_key_prefix = $this->generateLanguagePrefix($tour);
        }

        $columns = array_keys($tour);

        if (in_array('steps', $columns)) {
            unset($columns[array_search('steps', $columns)]);
        }

        $columns_to_add_if_missing = ['created', 'created_by', 'modified', 'modified_by'];

        foreach ($columns_to_add_if_missing as $column) {
            if (!in_array($column, $columns)) {
                $columns[] = $column;
            }
        }

        $columns_to_escape = ['title', 'description', 'uid', 'extensions', 'url', 'language', 'note'];

        $output = 'INSERT INTO ' . $quotes . '#__guidedtours' . $quotes . ' (' . implode(', ', array_map(function($column) use ($quotes) { return $quotes . $column . $quotes; }, $columns)) . ') VALUES' . "\n";

        $output_array = [];
        foreach ($columns as $column) {
            if ($column == 'created' || $column == 'modified') {
                $output_array[] = 'CURRENT_TIMESTAMP' . ($quotes !== '"' ? '()' : '');
                continue;
            }

            if ($column == 'created_by' || $column == 'modified_by') {
                $output_array[] = '0';
                continue;
            }

            if ($language_key_prefix && ($column == 'title' || $column == 'description')) {
                $tour[$column] = $language_key_prefix . '_' . strtoupper($column);
            }

            $output_array[] = in_array($column, $columns_to_escape) ? '\'' . str_replace(["'"], ["\'"], $tour[$column]) . '\'' : $tour[$column];
        }

        return $output . '(' . implode(', ', $output_array) . ')';
    }

    /**
     * Generate step SQL
     *
     * @param  integer  $step_number
     * @param  array    $step
     * @param  array    $columns
     * @param  array    $columns_to_escape
     * @param  string   $tour_uid
     * @param  boolean  $language_key_prefix
     * @param  string   $quotes to differentiate between MySQL and PostgreSQL
     *
     * @return string
     *
     */
    protected function generateStepSQL($step_number, $step, $columns, $columns_to_escape, $tour_uid, $language_key_prefix = '', $quotes = '"')
    {
        $output = '';

        $output .= 'INSERT INTO ' . $quotes . '#__guidedtour_steps' . $quotes . ' (' . implode(', ', array_map(function($column) use ($quotes) { return $quotes . $column . $quotes; }, $columns)) . ')';
        $output .= "\n" . 'SELECT ';

        $output_array = [];

        foreach ($columns as $column) {

            if ($column == 'tour_id') {
                $output_array[] = 'MAX(' . $quotes . 'id' . $quotes . ')';
                continue;
            }

            if ($column == 'created' || $column == 'modified') {
                $output_array[] = 'CURRENT_TIMESTAMP' . ($quotes !== '"' ? '()' : '');
                continue;
            }

            if ($column == 'created_by' || $column == 'modified_by') {
                $output_array[] = '0';
                continue;
            }

            if ($language_key_prefix && ($column == 'title' || $column == 'description')) {
                $step[$column] = $language_key_prefix . '_STEP_' . $step_number . '_' . strtoupper($column);
            }

            $output_array[] = in_array($column, $columns_to_escape) ? '\'' . str_replace(["'"], ["\'"], $step[$column]) . '\'' : $step[$column];
        }

        $output .= implode(', ', $output_array);

        $output .= "\n" . '  FROM ' . $quotes . '#__guidedtours' . $quotes;
        $output .= "\n" . ' WHERE ' . $quotes . 'uid' . $quotes . ' = \'' . $tour_uid . '\';';

        return $output;
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
        $language_key_prefix = '';
        if ($includeLanguageKeys) {
            $language_key_prefix = $this->generateLanguagePrefix($tour);
        }

        $columns = array_keys($tour['steps'][0]);

        $columns_to_add_if_missing = ['created', 'created_by', 'modified', 'modified_by', 'tour_id'];

        foreach ($columns_to_add_if_missing as $column) {
            if (!in_array($column, $columns)) {
                $columns[] = $column;
            }
        }

        $columns_to_escape = ['title', 'description', 'position', 'target', 'url', 'language', 'note', 'params'];

        $output_array = [];

        foreach ($tour['steps'] as $key => $step) {
            $output_array[] = $this->generateStepSQL($key, $step, $columns, $columns_to_escape, $tour['uid'], $language_key_prefix, $quotes);
        }

        return implode("\n\n", $output_array);
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
