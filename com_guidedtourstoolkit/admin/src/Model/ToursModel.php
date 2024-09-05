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
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Model class for Tours
 */
class ToursModel extends ListModel
{
    /**
    * Import json data
    *
    * @param   string  $data  The data as a json string.
    *
    * @return  boolean|integer returns the tour count or false on error
    */
    public function importjson($data)
    {
        if (empty($data)) {
            return false;
        }

        $data = json_decode($data, true);

        $user = $this->getCurrentUser();
        $db   = $this->getDatabase();
        $date = Factory::getDate()->toSql();

        if (!isset($data['tours'])) {
            return false;
        }

        foreach ($data['tours'] as $tour) {
            // Insert a tour

            $isValid =
                array_key_exists('title', $tour) &&
                array_key_exists('url', $tour) &&
                array_key_exists('extensions', $tour);

            if (!$isValid) {
                continue;
            }

            $query = $db->getQuery(true);

            $columns = [
                'title',
                'description',
                'extensions',
                'url',
                'created',
                'created_by',
                'modified',
                'modified_by',
                'published',
                'language',
                'ordering',
                'note',
                'access',
            ];

            if (version_compare(JVERSION, '5.0', '>=')) {
                $columns[] = 'uid';
            }

            if (version_compare(JVERSION, '5.1', '>=')) {
                $columns[] = 'autostart';
            }

            $values = [
                $tour['title'],
                $tour['description'] ?? '',
                $tour['extensions'],
                $tour['url'],
                $date,
                $user->id,
                $date,
                $user->id,
                $tour['published'] ?? 0,
                $tour['language'] ?? '*',
                1,
                $tour['note'] ?? '',
                $tour['access'] ?? 1,
            ];

            if (version_compare(JVERSION, '5.0', '>=')) {
                if (isset($tour['uid'])) {
                    $values[] = $tour['uid'];
                } else {
                    // TODO create uid
                    $values[] = '';
                }
            }

            if (version_compare(JVERSION, '5.1', '>=')) {
                if (isset($tour['autostart'])) {
                    $values[] = $tour['autostart'];
                } else {
                    $values[] = 0;
                }
            }

            $dataTypes = [
                ParameterType::STRING,
                ParameterType::STRING,
                ParameterType::STRING,
                ParameterType::STRING,
                ParameterType::STRING,
                ParameterType::INTEGER,
                ParameterType::STRING,
                ParameterType::INTEGER,
                ParameterType::INTEGER,
                ParameterType::STRING,
                ParameterType::INTEGER,
                ParameterType::STRING,
                ParameterType::INTEGER,
            ];

            if (version_compare(JVERSION, '5.0', '>=')) {
                $dataTypes[] = ParameterType::STRING;
            }

            if (version_compare(JVERSION, '5.1', '>=')) {
                $dataTypes[] = ParameterType::INTEGER;
            }

            $query->insert($db->quoteName('#__guidedtours'), 'id');
            $query->columns($db->quoteName($columns));
            $query->values(implode(',', $query->bindArray($values, $dataTypes)));

            $db->setQuery($query);

            try {
                $result = $db->execute();
                if ($result && !empty($tour['steps'])) {
                    $tourId = $db->insertid();

                    // Insert steps for the tour

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
                        $columns[] = 'params';
                        $dataTypes[] = ParameterType::STRING;
                    }

                    $step_values = [];

                    foreach ($tour['steps'] as $step) {
                        $isValid = array_key_exists('title', $step);

                        if (!$isValid) {
                            continue;
                        }

                        $tmp_step_values = [
                            $tourId,
                            $step['title'],
                            $step['description'] ?? '',
                            $step['position'] ?? 'center',
                            $step['target'] ?? '',
                            $step['type'] ?? 0,
                            $step['interactive_type'] ?? 1,
                            $step['url'] ?? '',
                            $date,
                            $user->id,
                            $date,
                            $user->id,
                            $step['published'] ?? 0,
                            $step['language'] ?? '*',
                            1,
                            $step['note'] ?? '',
                        ];

                        if (version_compare(JVERSION, '5.1', '>=')) {
                            if (isset($step['params'])) {
                                $tmp_step_values[] = json_encode($step['params']);
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

                    $result = $db->execute();
                }
            } catch (ExecutionFailureException $e) {
                Factory::getApplication()->enqueueMessage($e->getQuery());
                return false;
            }
        }

        return count($data['tours']);
    }

}
