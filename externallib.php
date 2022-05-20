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
 * External Web Service Template
 *
 * @package mod_leeloolxpvc
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author Leeloo LXP <info@leeloolxp.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . "/externallib.php");

/**
 * The external api class
 */
class mod_leeloolxpvc_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function update_recording_parameters() {
        return new external_function_parameters(
            array(
                'meeting_name' => new external_value(PARAM_RAW, 'Meeting Name', VALUE_DEFAULT, null),
                'recording_path' => new external_value(PARAM_RAW, 'Recording Path', VALUE_DEFAULT, null),
                'video_name' => new external_value(PARAM_RAW, 'Video Name', VALUE_DEFAULT, null),
                'videourl' => new external_value(PARAM_RAW, 'Video URL', VALUE_DEFAULT, null),
                'recording_url_base' => new external_value(PARAM_RAW, 'URL Base', VALUE_DEFAULT, null),
            )
        );
    }

    /**
     * Update Recording of Wespher Instance
     * @param string $reqmeetingname The meeting name
     * @param string $reqrecordingpath The recording path
     * @param string $reqvideoname The video name
     * @param string $reqvideourl The video url
     * @param string $reqrecordingurlbase The recording base url
     * @return string welcome message
     */
    public static function update_recording($reqmeetingname = '', $reqrecordingpath = '', $reqvideoname = '', $reqvideourl = '', $reqrecordingurlbase = '') {

        global $DB;
        // Parameter validation
        // REQUIRED
        $params = self::validate_parameters(
            self::update_recording_parameters(),
            array(
                'meeting_name' => $reqmeetingname,
                'recording_path' => $reqrecordingpath,
                'video_name' => $reqvideoname,
                'videourl' => $reqvideourl,
                'recording_url_base' => $reqrecordingurlbase,
            )
        );

        $meetingname = base64_decode($reqmeetingname);
        $recordingpath = base64_decode($reqrecordingpath);
        $videoname = base64_decode($reqvideoname);
        $videourl = base64_decode($reqvideourl);
        $recordingurlbase = base64_decode($reqrecordingurlbase);

        $checksql = 'SELECT * FROM {leeloolxpvc} WHERE roomname= ?';
        $wesphers = $DB->get_record_sql($checksql, [$meetingname]);

        $result = array();

        if ($wesphers) {
            if ($wesphers->recordedurl != "") {
                $result['old'] = $wesphers->recordedurl;
                $result['new'] = $recordingpath . '/' . $videoname;
                $recordingurlbase = str_ireplace('/config/recordings/', '', $recordingurlbase);
                $sql = 'UPDATE {leeloolxpvc} SET recordedurl = ? WHERE roomname = ?';
                $DB->execute($sql, [$recordingurlbase . '/' . 'output.mp4', $meetingname]);
            } else {

                $sql = 'UPDATE {leeloolxpvc} SET recordedurl = ? WHERE roomname = ?';
                $DB->execute($sql, [$videourl, $meetingname]);
            }
        }

        return json_encode($result);
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function update_recording_returns() {
        return new external_value(PARAM_RAW, 'Returns result');
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function view_leeloolxpvc_parameters() {
        return new external_function_parameters(
            array(
                'leeloolxpvcid' => new external_value(PARAM_INT, 'leeloolxpvc instance id'),
            )
        );
    }

    /**
     * Simulate the leeloolxpvc/view.php web interface leeloolxpvc: trigger events, completion, etc...
     *
     * @param int $leeloolxpvcid the leeloolxpvc instance id
     * @return array of warnings and status result
     * @since Moodle 3.0
     * @throws moodle_exception
     */
    public static function view_leeloolxpvc($leeloolxpvcid) {
        global $DB, $CFG;
        require_once($CFG->dirroot . "/mod/leeloolxpvc/lib.php");

        $params = self::validate_parameters(
            self::view_leeloolxpvc_parameters(),
            array(
                'leeloolxpvcid' => $leeloolxpvcid,
            )
        );
        $warnings = array();

        // Request and permission validation.
        $leeloolxpvc = $DB->get_record('leeloolxpvc', array('id' => $params['leeloolxpvcid']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($leeloolxpvc, 'leeloolxpvc');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/leeloolxpvc:view', $context);

        // Call the leeloolxpvc/lib API.
        leeloolxpvc_view($leeloolxpvc, $course, $cm, $context);

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function view_leeloolxpvc_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Describes the parameters for get_leeloolxpvcs_by_courses.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_leeloolxpvcs_by_courses_parameters() {
        return new external_function_parameters(
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'Course id'),
                    'Array of course ids',
                    VALUE_DEFAULT,
                    array()
                ),
            )
        );
    }

    /**
     * Returns a list of leeloolxpvcs in a provided list of courses.
     * If no list is provided all leeloolxpvcs that the user can view will be returned.
     *
     * @param array $courseids course ids
     * @return array of warnings and leeloolxpvcs
     * @since Moodle 3.3
     */
    public static function get_leeloolxpvcs_by_courses($courseids = array()) {

        global $USER, $CFG;
        $token = optional_param('wstoken', '', PARAM_ALPHANUM);
        $warnings = array();
        $returnedleeloolxpvcs = array();

        $params = array(
            'courseids' => $courseids,
        );
        $params = self::validate_parameters(self::get_leeloolxpvcs_by_courses_parameters(), $params);

        $mycourses = array();
        if (empty($params['courseids'])) {
            $mycourses = enrol_get_my_courses();
            $params['courseids'] = array_keys($mycourses);
        }

        // Ensure there are courseids to loop through.
        if (!empty($params['courseids'])) {

            list($courses, $warnings) = external_util::validate_courses($params['courseids'], $mycourses);

            // Get the leeloolxpvcs in this course, this function checks users visibility permissions.
            // We can avoid then additional validate_context calls.
            $leeloolxpvcs = get_all_instances_in_courses("leeloolxpvc", $courses);

            foreach ($leeloolxpvcs as $leeloolxpvc) {
                $context = context_module::instance($leeloolxpvc->coursemodule);
                // Entry to return.
                $leeloolxpvc->name = external_format_string($leeloolxpvc->name, $context->id);

                $options = array('noclean' => true);
                list($leeloolxpvc->intro, $leeloolxpvc->introformat) =
                    external_format_text($leeloolxpvc->intro, $leeloolxpvc->introformat, $context->id, 'mod_leeloolxpvc', 'intro', null, $options);
                $leeloolxpvc->introfiles = external_util::get_area_files($context->id, 'mod_leeloolxpvc', 'intro', false, false);

                $options = array('noclean' => true);
                list($leeloolxpvc->content, $leeloolxpvc->contentformat) = external_format_text(
                    $leeloolxpvc->content,
                    $leeloolxpvc->contentformat,
                    $context->id,
                    'mod_leeloolxpvc',
                    'content',
                    $leeloolxpvc->revision,
                    $options
                );
                $leeloolxpvc->contentfiles = external_util::get_area_files($context->id, 'mod_leeloolxpvc', 'content');

                $returnedleeloolxpvcs[] = $leeloolxpvc;
            }
        }

        $result = array(
            'leeloolxpvcs' => $returnedleeloolxpvcs,
            'warnings' => $warnings
        );

        return $result;
    }

    /**
     * Describes the get_leeloolxpvcs_by_courses return value.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function get_leeloolxpvcs_by_courses_returns() {
        return new external_single_structure(
            array(
                'leeloolxpvcs' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Module id'),
                            'coursemodule' => new external_value(PARAM_INT, 'Course module id'),
                            'course' => new external_value(PARAM_INT, 'Course id'),
                            'name' => new external_value(PARAM_RAW, 'leeloolxpvc name'),
                            'timeopen' => new external_value(PARAM_RAW, 'leeloolxpvc timeopen'),
                            'beforetime' => new external_value(PARAM_RAW, 'leeloolxpvc beforetime'),
                            'completed' => new external_value(PARAM_RAW, 'leeloolxpvc completed'),
                            'roomname' => new external_value(PARAM_RAW, 'leeloolxpvc roomname'),
                            'recordedurl' => new external_value(PARAM_RAW, 'leeloolxpvc recordedurl'),
                            'intro' => new external_value(PARAM_RAW, 'Summary'),
                            'introformat' => new external_format_value('intro', 'Summary format'),
                            'introfiles' => new external_files('Files in the introduction text'),
                            'content' => new external_value(PARAM_RAW, 'leeloolxpvc content'),
                            'contentformat' => new external_format_value('content', 'Content format'),
                            'contentfiles' => new external_files('Files in the content'),
                            'timemodified' => new external_value(PARAM_INT, 'Last time the leeloolxpvc was modified'),
                            'section' => new external_value(PARAM_INT, 'Course section id'),
                            'visible' => new external_value(PARAM_INT, 'Module visibility'),
                            'groupmode' => new external_value(PARAM_INT, 'Group mode'),
                            'groupingid' => new external_value(PARAM_INT, 'Grouping id'),
                        )
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }
}
