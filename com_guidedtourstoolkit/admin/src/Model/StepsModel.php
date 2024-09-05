<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtourstoolkit
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtourstoolkit\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\Guidedtours\Administrator\Extension\GuidedtoursComponent;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Model class for Steps
 */
class StepsModel extends ListModel
{

    /**
     * A mapping for the step types
     */
    protected $stepTypes = [
        'next'        => [GuidedtoursComponent::STEP_NEXT, 'COM_GUIDEDTOURS_FIELD_VALUE_STEP_TYPE_NEXT'],
        'redirect'    => [GuidedtoursComponent::STEP_REDIRECT, 'COM_GUIDEDTOURS_FIELD_VALUE_STEP_TYPE_REDIRECT'],
        'interactive' => [GuidedtoursComponent::STEP_INTERACTIVE, 'COM_GUIDEDTOURS_FIELD_VALUE_STEP_TYPE_INTERACTIVE'],
    ];

    /**
     * A mapping for the step interactive types
     */
    protected $stepInteractiveTypes = [
        'submit'         => [GuidedtoursComponent::STEP_INTERACTIVETYPE_FORM_SUBMIT, 'COM_GUIDEDTOURS_FIELD_VALUE_INTERACTIVESTEP_TYPE_FORM_SUBMIT'],
        'text'           => [GuidedtoursComponent::STEP_INTERACTIVETYPE_TEXT, 'COM_GUIDEDTOURS_FIELD_VALUE_INTERACTIVESTEP_TYPE_TEXT_FIELD'],
        'other'          => [GuidedtoursComponent::STEP_INTERACTIVETYPE_OTHER, 'COM_GUIDEDTOURS_FIELD_VALUE_INTERACTIVESTEP_TYPE_OTHER'],
        'button'         => [GuidedtoursComponent::STEP_INTERACTIVETYPE_BUTTON, 'COM_GUIDEDTOURS_FIELD_VALUE_INTERACTIVESTEP_TYPE_BUTTON'],
        'checkbox_radio' => [GuidedtoursComponent::STEP_INTERACTIVETYPE_CHECKBOX_RADIO, 'COM_GUIDEDTOURS_FIELD_VALUE_INTERACTIVESTEP_TYPE_CHECKBOX_RADIO_FIELD'],
        'select'         => [GuidedtoursComponent::STEP_INTERACTIVETYPE_SELECT, 'COM_GUIDEDTOURS_FIELD_VALUE_INTERACTIVESTEP_TYPE_SELECT_LIST'],
    ];

    /**
     * A mapping for the step positions
     */
    protected $stepPositions = [
        'center' => 'JGLOBAL_CENTER',
        'top'    => 'JGLOBAL_TOP',
        'bottom' => 'JGLOBAL_BOTTOM',
        'left'   => 'JGLOBAL_LEFT',
        'right'  => 'JGLOBAL_RIGHT',
    ];

    /**
    * Import csv data
    *
    * @param   string  $data  The data as a csv object.
    *
    * @return  boolean|integer returns the step count or false on error
    */
    public function importcsv($file, $tourId)
    {
//         if (empty($data)) {
//             return false;
//         }

        $user = $this->getCurrentUser();
        $db   = $this->getDatabase();
        $date = Factory::getDate()->toSql();

        $delimiter = ';';
        $enclosure = '"';

        $columns = [];
        $steps   = [];

        ini_set('auto_detect_line_endings', TRUE); // to deal with MAC line endings DEPRECATED

        if (($handle = fopen($file, "r")) !== FALSE) {
            $row = 1;

            while (($csvdata = fgetcsv($handle, 0, $delimiter, $enclosure)) !== FALSE) {
                $num = count($csvdata); // number of fields

                if ($row > 1) {
                    $step = [];
                }

                for ($c = 0; $c < $num; $c++) { // go through all cells of one line

                    if ($row == 1) {
                        $column_name = trim(strtolower($csvdata[$c]));

                        $columns[] = $column_name;

                    } else {
                        $column = $columns[$c];

                        // check if the data has been escaped
                        $csvdata[$c] = str_replace('\\' . $delimiter, $delimiter, $csvdata[$c]);

                        if (mb_detect_encoding($csvdata[$c], 'UTF-8', true) === false) {
                            $step[$column] = utf8_encode($csvdata[$c]);
                        } else {
                            $step[$column] = $csvdata[$c];
                        }
                    }
                }

                if ($row > 1) {

                    // if missing tourId, stop import
                    if (empty($tourId) && (!isset($step['tour_id']) || empty($step['tour_id']))) {
                        $errors[$row] = Text::sprintf('COM_GUIDEDTOURSTOOLKIT_STEPS_IMPORT_CSV_MISSING_COLUMN', 'tour_id');
                        continue;
                    }

                    // if missing title, stop import
                    if (!isset($step['title']) || empty($step['title'])) {
                        $errors[$row] = Text::sprintf('COM_GUIDEDTOURSTOOLKIT_STEPS_IMPORT_CSV_MISSING_COLUMN', 'title');
                        continue;
                    }

                    $steps[] = $step;
                }

                $row++;
            }
            fclose($handle);
        }

        ini_set('auto_detect_line_endings', FALSE); // to deal with MAC line endings

        // report the errors, if any
        if (!empty($errors)) {
            foreach ($errors as $key => $value) {
                if ($key === 1) {
                    Factory::getApplication()->enqueueMessage($value, 'warning'); // don't stop the import
                } else {
                    Factory::getApplication()->enqueueMessage('row '.$key.' => '.$value, 'warning');
                }
            }

            if ((count($errors) == 1 && !isset($errors[1])) || count($errors) > 1) {
                return false;
            }
        }

        // Insert steps

        $query = $db->getQuery(true);

        $columns = [
            'tour_id',
            'title',
            'description',
            'position',
            'target',
            'type',
            'interactive_type',
            'url',
            'created',
            'created_by',
            'modified',
            'modified_by',
            'published',
            'language',
            'ordering',
            'note',
        ];

        if (version_compare(JVERSION, '5.1', '>=')) {
            $columns[] = 'params';
        }

        $dataTypes = [
            ParameterType::INTEGER,
            ParameterType::STRING,
            ParameterType::STRING,
            ParameterType::STRING,
            ParameterType::STRING,
            ParameterType::INTEGER,
            ParameterType::INTEGER,
            ParameterType::STRING,
            ParameterType::STRING,
            ParameterType::INTEGER,
            ParameterType::STRING,
            ParameterType::INTEGER,
            ParameterType::INTEGER,
            ParameterType::STRING,
            ParameterType::INTEGER,
            ParameterType::STRING,
        ];

        if (version_compare(JVERSION, '5.1', '>=')) {
            $dataTypes[] = ParameterType::STRING;
        }

        $step_values = [];

        foreach ($steps as $step) {
            $isValid = array_key_exists('title', $step);

            if (!$isValid) {
                continue;
            }

            $step['description'] = isset($step['description']) ? trim($step['description']) : '';
            $step['position'] = isset($step['position']) ? trim($step['position']) : '';
            $step['target'] = isset($step['target']) ? trim($step['target']) : '';
            $step['type'] = isset($step['type']) ? trim($step['type']) : '';
            $step['interactive_type'] = isset($step['interactive_type']) ? trim($step['interactive_type']) : '';
            $step['url'] = isset($step['url']) ? trim($step['url']) : '';
            $step['note'] = isset($step['note']) ? trim($step['note']) : '';

            if (!empty($step['position'])) {
                foreach ($this->stepPositions as $key => $value) {
                    if (Text::_($value) == $step['position']) {
                        $step['position'] = $key;
                    }
                }
            }

            $lang = Factory::getLanguage();
            $lang->load('com_guidedtours', JPATH_ADMINISTRATOR);

            if (!empty($step['type'])) {
                foreach ($this->stepTypes as $key => $value) {
                    if (Text::_($value[1]) == $step['type']) {
                        $step['type'] = $key;
                    }
                }
            }

            if (!empty($step['interactive_type'])) {
                foreach ($this->stepInteractiveTypes as $key => $value) {
                    if (Text::_($value[1]) == $step['interactive_type']) {
                        $step['interactive_type'] = $key;
                    }
                }
            }

            $tmp_step_values = [
                !empty($tourId) ? $tourId : $step['tour_id'],
                $step['title'],
                !empty($step['description']) ? $step['description'] : '',
                !empty($step['position']) && array_key_exists($step['position'], $this->stepPositions) ? $step['position'] : 'center',
                !empty($step['target']) ? $step['target'] : '',
                !empty($step['type']) && isset($this->stepTypes[$step['type']]) ? $this->stepTypes[$step['type']][0] : 0,
                !empty($step['interactive_type']) && isset($this->stepInteractiveTypes[$step['interactive_type']]) ? $this->stepInteractiveTypes[$step['interactive_type']][0] : 1,
                !empty($step['url']) ? $step['url'] : '',
                $date,
                $user->id,
                $date,
                $user->id,
                $step['published'] ?? 0,
                '*',
                0,
                !empty($step['note']) ? $step['note'] : '',
            ];

            if (version_compare(JVERSION, '5.1', '>=')) {
                $tmp_params = [];

                if (isset($step['required'])) {
                    $step['required'] = intVal($step['required']);
                    if (in_array($step['required'], [0, 1])) {
                        $tmp_params['required'] = $step['required'];
                    }
                }

                if (isset($step['requiredvalue'])) {
                    $step['requiredvalue'] = trim($step['requiredvalue']);
                    // We allow required empty values
                    if (!empty($step['requiredvalue']) || (isset($tmp_params['required']) && $tmp_params['required'])) {
                        $tmp_params['requiredvalue'] = $step['requiredvalue'];
                    }
                }

                if (!empty($tmp_params)) {
                    $tmp_step_values[] = json_encode($tmp_params);
                } else {
                    $tmp_step_values[] = null;
                }
            }

            $step_values[] = $tmp_step_values;
        }

        $query->clear();

        $query->insert($db->quoteName('#__guidedtour_steps'), 'id');
        $query->columns($db->quoteName($columns));

        foreach ($step_values as $values) {
            $query->values(implode(',', $query->bindArray($values, $dataTypes)));
        }

        $db->setQuery($query);

        try {
            $result = $db->execute();
            if ($result === false) {
                return false;
            }
        } catch (ExecutionFailureException $e) {
            Factory::getApplication()->enqueueMessage($e->getQuery());
            return false;
        }

        return count($steps);
    }

    /**
     * Build an SQL query to load the list data.
     * Needed because com_guidedtours translates title and description
     *
     * @return \Joomla\Database\DatabaseQuery
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.*'
            )
        );

        $query->from($db->quoteName('#__guidedtour_steps', 'a'));

        // Join with user table
        $query->select(
            [
                $db->quoteName('uc.name', 'editor'),
            ]
        )
            ->join('LEFT', $db->quoteName('#__users', 'uc'), $db->quoteName('uc.id') . ' = ' . $db->quoteName('a.checked_out'));

        $tourId = $this->getState('filter.tour_id');

        if (is_numeric($tourId)) {
            $tourId = (int) $tourId;
            $query->where($db->quoteName('a.tour_id') . ' = :tour_id')
                ->bind(':tour_id', $tourId, ParameterType::INTEGER);
        } elseif (\is_array($tourId)) {
            $tourId = ArrayHelper::toInteger($tourId);
            $query->whereIn($db->quoteName('a.tour_id'), $tourId);
        }

        // Published state
        $published = (string) $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where($db->quoteName('a.published') . ' = :published');
            $query->bind(':published', $published, ParameterType::INTEGER);
        } elseif ($published === '') {
            $query->where('(' . $db->quoteName('a.published') . ' = 0 OR ' . $db->quoteName('a.published') . ' = 1)');
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $search = (int) substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :search')
                    ->bind(':search', $search, ParameterType::INTEGER);
            } elseif (stripos($search, 'description:') === 0) {
                $search = '%' . substr($search, 12) . '%';
                $query->where('(' . $db->quoteName('a.description') . ' LIKE :search1)')
                    ->bind([':search1'], $search);
            } else {
                $search = '%' . str_replace(' ', '%', trim($search)) . '%';
                $query->where(
                    '(' . $db->quoteName('a.title') . ' LIKE :search1'
                    . ' OR ' . $db->quoteName('a.description') . ' LIKE :search2'
                    . ' OR ' . $db->quoteName('a.note') . ' LIKE :search3)'
                )
                    ->bind([':search1', ':search2', ':search3'], $search);
            }
        }

        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'a.ordering');
        $orderDirn = $this->state->get('list.direction', 'ASC');

        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }

}
