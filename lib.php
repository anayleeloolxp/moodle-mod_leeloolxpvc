<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library for leeloolxpvc
 *
 * @package    mod_leeloolxpvc
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author     Leeloo LXP <info@leeloolxp.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Support
 *
 * @param object $feature
 * @return bool true
 */
function leeloolxpvc_supports($feature) {

    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return false;
        default:
            return null;
    }
}

/**
 * Add Instance
 *
 * @param object $wespher The instance
 * @param object $mform The mform
 * @return int $id id
 */
function leeloolxpvc_add_instance($wespher, $mform = null) {

    global $CFG, $DB;
    require_once($CFG->dirroot . '/mod/leeloolxpvc/locallib.php');

    $wespher->timecreated = time();
    $cmid = $wespher->coursemodule;

    $wespher->roomname = str_replace(' ', '', $wespher->name);

    $wespher->id = $DB->insert_record('leeloolxpvc', $wespher);
    leeloolxpvc_update_calendar($wespher, $cmid);

    return $wespher->id;
}

/**
 * Update Instance
 *
 * @param object $wespher The instance
 * @param object $mform The mform
 * @return object $result result
 */
function leeloolxpvc_update_instance($wespher, $mform = null) {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/mod/leeloolxpvc/locallib.php');

    $wespher->timemodified = time();
    $wespher->id = $wespher->instance;
    $cmid = $wespher->coursemodule;

    $wespher->roomname = str_replace(' ', '', $wespher->name);

    $result = $DB->update_record('leeloolxpvc', $wespher);
    leeloolxpvc_update_calendar($wespher, $cmid);

    return $result;
}

/**
 * Refresh events
 *
 * @param int $courseid The page
 * @param int $instance The course
 * @param object $cm The course module
 * @return bool $result true
 */
function leeloolxpvc_refresh_events($courseid = 0, $instance = null, $cm = null) {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/mod/leeloolxpvc/locallib.php');

    if (isset($instance)) {
        if (!is_object($instance)) {
            $instance = $DB->get_record('leeloolxpvc', array('id' => $instance), '*', MUST_EXIST);
        }
        if (isset($cm)) {
            if (!is_object($cm)) {
                $cm = (object) array('id' => $cm);
            }
        } else {
            $cm = get_coursemodule_from_instance('leeloolxpvc', $instance->id);
        }
        leeloolxpvc_update_calendar($instance, $cm->id);
        return true;
    }

    if ($courseid) {
        if (!is_numeric($courseid)) {
            return false;
        }
        if (!$wesphers = $DB->get_records('leeloolxpvc', array('course' => $courseid))) {
            return true;
        }
    } else {
        return true;
    }

    foreach ($wesphers as $wespher) {
        $cm = get_coursemodule_from_instance('leeloolxpvc', $wespher->id);
        leeloolxpvc_update_calendar($wespher, $cm->id);
    }

    return true;
}

/**
 * Delete instance
 *
 * @param string $id The page
 * @return bool $result true
 */
function leeloolxpvc_delete_instance($id) {
    global $CFG, $DB;

    if (!$wespher = $DB->get_record('leeloolxpvc', array('id' => $id))) {
        return false;
    }

    $result = true;

    if (!$DB->delete_records('leeloolxpvc', array('id' => $wespher->id))) {
        $result = false;
    }

    return $result;
}

/**
 * Set View
 *
 * @param object $page The page
 * @param object $course The course
 * @param object $cm The course module
 * @param object $context The context
 */
function leeloolxpvc_view($page, $course, $cm, $context) {

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $page->id,
    );

    $event = \mod_leeloolxpvc\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('leeloolxpvc', $page);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}
