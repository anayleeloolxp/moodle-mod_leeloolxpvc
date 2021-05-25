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
 * Index file
 *
 * @package    mod_leeloolxpvc
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author     Leeloo LXP <info@leeloolxp.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

$id = required_param('id', PARAM_INT); // Course.

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_course_login($course);

$wesname = get_string('modulenameplural', 'mod_leeloolxpvc');
$PAGE->set_url('/mod/leeloolxpvc/index.php', array('id' => $id));
$PAGE->navbar->add($wesname);
$PAGE->set_title("$course->shortname: $wesname");
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('incourse');

echo $OUTPUT->header();
echo $OUTPUT->heading($wesname);

if (!$wesphers = get_all_instances_in_course('leeloolxpvc', $course)) {
    notice(get_string('nowesphers', 'leeloolxpvc'), new moodle_url('/course/view.php', array('id' => $course->id)));
}

$coursesections = course_format_uses_sections($course->format);

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($coursesections) {
    $strsectionname = get_string('sectionname', 'format_' . $course->format);
    $table->head = array($strsectionname, $wesname);
    $table->align = array('center', 'left');
} else {
    $table->head = array($wesname);
    $table->align = array('left');
}

$modinfo = get_fast_modinfo($course);
$currentsection = '';
foreach ($modinfo->instances['leeloolxpvc'] as $cm) {
    $row = array();
    if ($coursesections) {
        if ($cm->sectionnum !== $currentsection) {
            if ($cm->sectionnum) {
                $row[] = get_section_name($course, $cm->sectionnum);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $cm->sectionnum;
        }
    }

    $class = $cm->visible ? null : array('class' => 'dimmed');

    $row[] = html_writer::link(new moodle_url('view.php', array('id' => $cm->id)), $cm->get_formatted_name(), $class);

    $table->data[] = $row;
}

echo html_writer::table($table);

echo $OUTPUT->footer();
