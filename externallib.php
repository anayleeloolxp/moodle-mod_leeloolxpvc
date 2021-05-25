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

        $checksql = 'SELECT * FROM {leeloolxpvc} WHERE `roomname`= ?';
        $wesphers = $DB->get_record_sql($checksql, [$meetingname]);

        $result = array();

        if ($wesphers) {
            if ($wesphers->recordedurl != "") {
                $result['old'] = $wesphers->recordedurl;
                $result['new'] = $recordingpath . '/' . $videoname;
                $recordingurlbase = str_ireplace('/recordings/', '', $recordingurlbase);
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
}
