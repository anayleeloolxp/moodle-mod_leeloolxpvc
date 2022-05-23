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
 * Shows the conference on page from leeloolxpvc server.
 *
 * @package    mod_leeloolxpvc
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author     Leeloo LXP <info@leeloolxp.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/lib/moodlelib.php');
require_once(dirname(__FILE__) . '/lib.php');
global $CFG;
require_once($CFG->dirroot . '/lib/filelib.php');

$PAGE->set_url($CFG->wwwroot . '/mod/leeloolxpvc/conference.php');
$PAGE->set_context(context_system::Instance());
$courseid = required_param('courseid', PARAM_INT);
$moduleid = required_param('cmid', PARAM_INT);
$context = context_module::instance($moduleid);

global $USER;
$diplayname = $USER->username;

$conferencenme = required_param('conferencename', PARAM_TEXT);
$conferencenmenospace = str_replace(' ', '', $conferencenme);

require_login($courseid);
$PAGE->set_title($conferencenme);
$PAGE->set_heading($conferencenme);
echo $OUTPUT->header();

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

if (!isset($settingleeloolxp->wespher_domain)) {
    notice(get_string('nowespherdefined', 'mod_leeloolxpvc'));
}

$maxusers = $settingleeloolxp->maxusers;
$maxconf = $settingleeloolxp->maxconf;

global $CFG;

$siteurl = $CFG->wwwroot;
$siteurlencoded = str_ireplace('https://', 'https_', $siteurl);
$siteurlencoded = str_ireplace('http://', 'http_', $siteurlencoded);
$siteurlencoded = str_ireplace('/', '__', $siteurlencoded);
$roomname = $siteurlencoded . '_____' . $conferencenmenospace;

$roomname = ($leeloolxplicense . '_____' . $conferencenmenospace);

if (!has_capability('mod/leeloolxpvc:view', $context)) {
    notice(get_string('nopermissiontoview', 'leeloolxpvc'));
}
echo "<div class='thirdpartynote'><b>" . get_string('note', 'leeloolxpvc') . "</div>";
echo "<div id='vc_notice' style='text-align: center;font-size: 30px;color: indianred;'></div>";
echo "<script src=\"https://" . $settingleeloolxp->wespher_domain . "/external_api.js\"></script>\n";
echo "<script>\n";
echo "var domain = \"" . $settingleeloolxp->wespher_domain . "\";\n";
echo "var options = {\n";
echo "roomName: \"" . $roomname . "\",\n";

$contextcourse = context_course::instance($courseid);
if (has_capability('moodle/course:manageactivities', $contextcourse)) {
    echo "jwt: \"teacher1\",\n";
}

if ($CFG->branch < 36) {
    echo "parentNode: document.querySelector('#region-main .card-body'),\n";
} else {
    echo "parentNode: document.querySelector('#region-main'),\n";
}

echo "width: '100%',\n";
echo "height: 650,\n";
echo "}\n";
echo "var api = new WespherExternalAPI(domain, options);\n";
echo "api.executeCommand('displayName', '" . $diplayname . "');\n";
echo "api.executeCommand('toggleVideo');\n";

echo "api.on('videoConferenceJoined', () => {
    var max = " . $maxusers . ";
    var number = api.getNumberOfParticipants();
    if(number > max){
        api.executeCommand('hangup');
        document.getElementById('vc_notice').innerHTML = '" . get_string('maxusers', 'mod_leeloolxpvc') . "';
    }
});\n";

if (has_capability('moodle/course:manageactivities', $contextcourse)) {
    $falsevar = 0;
} else {
    echo "api.on('participantRoleChanged', (data) => {
        //console.log('participantRoleChanged');
        //console.log(data);
        if( data.role == 'moderator' ){
            document.getElementById('vc_notice').innerHTML = '" . get_string('noteacherinroom', 'mod_leeloolxpvc') . "';
            api.executeCommand('hangup');
        }
    });\n";
}

echo "</script>\n";

echo $OUTPUT->footer();
