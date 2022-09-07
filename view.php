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
 * Prints instance of leeloolxpvc
 *
 * @package    mod_leeloolxpvc
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author     Leeloo LXP <info@leeloolxp.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

global $CFG;
require_once($CFG->dirroot . '/lib/filelib.php');

global $USER;

$id = optional_param('id', 0, PARAM_INT);
$n = optional_param('n', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('leeloolxpvc', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $wespher = $DB->get_record('leeloolxpvc', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $wespher = $DB->get_record('leeloolxpvc', array('id' => $n), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $wespher->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('leeloolxpvc', $wespher->id, $course->id, false, MUST_EXIST);
} else {
    throw new moodle_exception('missingparam');
}

require_login($course, true, $cm);

// Completion and trigger events.

$PAGE->set_url('/mod/leeloolxpvc/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($wespher->name));
$PAGE->set_heading(format_string($course->fullname));

$context = context_module::instance($cm->id);

leeloolxpvc_view($wespher, $course, $cm, $context);

echo $OUTPUT->header();
echo $OUTPUT->heading($wespher->name);

$leeloolxplicense = get_config('mod_leeloolxpvc')->license;
$url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';
$postdata = [
    'license_key' => $leeloolxplicense,
];

$curl = new curl;

$options = array(
    'CURLOPT_RETURNTRANSFER' => true,
    'CURLOPT_HEADER' => false,
    'CURLOPT_POST' => count($postdata),
);

if (!$output = $curl->post($url, $postdata, $options)) {
    notice(get_string('nolicense', 'mod_leeloolxpvc'));
}

$infoleeloolxp = json_decode($output);

if ($infoleeloolxp->status != 'false') {
    $leeloolxpurl = $infoleeloolxp->data->install_url;
} else {
    notice(get_string('nolicense', 'mod_leeloolxpvc'));
}

$url = $leeloolxpurl . '/admin/Theme_setup/get_wespher_conference_settings';

$postdata = [
    'license_key' => $leeloolxplicense,
];

$curl = new curl;

$options = array(
    'CURLOPT_RETURNTRANSFER' => true,
    'CURLOPT_HEADER' => false,
    'CURLOPT_POST' => count($postdata),
);

if (!$output = $curl->post($url, $postdata, $options)) {
    notice(get_string('nolicense', 'mod_leeloolxpvc'));
}

$resposedata = json_decode($output);
$settingleeloolxp = $resposedata->data->wespher_conference;
$maxusers = $settingleeloolxp->maxusers;
$maxconf = $settingleeloolxp->maxconf;

$checksql = "SELECT count(*) activeconf FROM {leeloolxpvc} WHERE completed= ?";
$wesphers = $DB->get_record_sql($checksql, [2]);
$activeconf = $wesphers->activeconf;

if ($activeconf > $maxconf) {
    notice(get_string('maxconf', 'leeloolxpvc'));
} else {
    if (!has_capability('mod/leeloolxpvc:view', $context)) {
        notice(get_string('nopermissiontoview', 'leeloolxpvc'));
    }

    if ($wespher->intro) {
        echo $OUTPUT->box(format_module_intro('leeloolxpvc', $wespher, $cm->id), 'generalbox mod_introbox', 'wespherintro');
    }

    $urlparams = array('conferencename' => $wespher->name, 'courseid' => $course->id, 'cmid' => $id);

    $today = getdate();

    if ($wespher->completed == 1) {
        $recordedurls = array_reverse(explode('|', $wespher->recordedurl));

        foreach ($recordedurls as $recordedurl) {
            if ($recordedurl != '') {
                echo '<video width="100%" controls><source src="' .
                    $recordedurl .
                    '" type="video/mp4">Your browser does not support HTML5 video.</video>';
            }
        }
    } else if ($wespher->completed == 2) {
        echo $OUTPUT->box(get_string('joininfo', 'leeloolxpvc'));
        echo $OUTPUT->single_button(
            new moodle_url('/mod/leeloolxpvc/conference.php', $urlparams),
            get_string('join', 'leeloolxpvc'),
            'get'
        );
    } else {

        echo $OUTPUT->box(get_string('conferenenotstartedbyteacher', 'leeloolxpvc'));
    }
}

echo $OUTPUT->footer();
