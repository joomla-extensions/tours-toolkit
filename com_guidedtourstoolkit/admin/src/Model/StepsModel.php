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
use Joomla\Database\ParameterType;

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
        'next'        => GuidedtoursComponent::STEP_NEXT,
        'redirect'    => GuidedtoursComponent::STEP_REDIRECT,
        'interactive' => GuidedtoursComponent::STEP_INTERACTIVE,
    ];

    /**
     * A mapping for the step interactive types
     */
    protected $stepInteractiveTypes = [
        'submit'         => GuidedtoursComponent::STEP_INTERACTIVETYPE_FORM_SUBMIT,
        'text'           => GuidedtoursComponent::STEP_INTERACTIVETYPE_TEXT,
        'other'          => GuidedtoursComponent::STEP_INTERACTIVETYPE_OTHER,
        'button'         => GuidedtoursComponent::STEP_INTERACTIVETYPE_BUTTON,
        'checkbox_radio' => GuidedtoursComponent::STEP_INTERACTIVETYPE_CHECKBOX_RADIO,
        'select'         => GuidedtoursComponent::STEP_INTERACTIVETYPE_SELECT,
    ];
    
    /**
     * Positions
     */
    protected $stepPositions = [
        'center', 'top', 'bottom', 'left', 'right',
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

            $tmp_step_values = [
                !empty($tourId) ? $tourId : $step['tour_id'],
                $step['title'],
                isset($step['description']) && !empty($step['description']) ? $step['description'] : '',
                isset($step['position']) && !empty($step['position']) && in_array($step['position'], $this->stepPositions) ? $step['position'] : 'center',
                isset($step['target']) && !empty($step['target']) ? $step['target'] : '',
                isset($step['type']) && !empty($step['type']) && isset($this->stepTypes[$step['type']]) ? $this->stepTypes[$step['type']] : 0,
                isset($step['interactive_type']) && !empty($step['interactive_type']) && isset($this->stepInteractiveTypes[$step['type']]) ? $this->stepInteractiveTypes[$step['interactive_type']] : 1,
                isset($step['url']) && !empty($step['url']) ? $step['url'] : '',
                $date,
                $user->id,
                $date,
                $user->id,
                $step['published'] ?? 0,
                '*',
                0,
                isset($step['note']) && !empty($step['note']) ? $step['note'] : '',
            ];

            if (version_compare(JVERSION, '5.1', '>=')) {
                $tmp_params = [];
                if (isset($step['required']) && !empty($step['required'])) {
                    $tmp_params['required'] = $step['required'];
                }
                
                if (isset($step['requiredvalue']) && !empty($step['requiredvalue'])) {
                    $tmp_params['requiredvalue'] = $step['requiredvalue'];
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
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getQuery());
            return false;
        }

        return count($steps);
    }

}
