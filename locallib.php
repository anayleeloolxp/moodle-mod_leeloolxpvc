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
 * Internal library for leeloolxpvc
 *
 * @package    mod_leeloolxpvc
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author     Leeloo LXP <info@leeloolxp.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Update Calendar
 *
 * @param stdClass|stdobject $wespher wespher
 * @param stdClass|stdobject $cmid cmid
 * @return bool result true
 */
function leeloolxpvc_update_calendar($wespher, $cmid) {
    global $DB, $CFG;

    require_once($CFG->dirroot . '/calendar/lib.php');

    $event = new stdClass();
    $event->eventtype = 'open';
    $event->type = CALENDAR_EVENT_TYPE_STANDARD;

    if ($event->id = $DB->get_field('event', 'id',
        array('modulename' => 'leeloolxpvc', 'instance' => $wespher->id,
            'eventtype' => $event->eventtype))) {
        if ((!empty($wespher->timeopen)) && ($wespher->timeopen > 0)) {
            $event->name = get_string('wespherstart', 'leeloolxpvc', $wespher->name);
            $event->timestart = $wespher->timeopen;
            $event->timesort = $wespher->timeopen;
            $event->visible = instance_is_visible('leeloolxpvc', $wespher);
            $event->timeduration = 0;

            $calendarevent = calendar_event::load($event->id);
            $calendarevent->update($event);
        } else {
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->delete();
        }
    } else {
        if ((!empty($wespher->timeopen)) && ($wespher->timeopen > 0)) {
            $event->name = get_string('wespherstart', 'leeloolxpvc', $wespher->name);
            $event->courseid = $wespher->course;
            $event->groupid = 0;
            $event->userid = 0;
            $event->modulename = 'leeloolxpvc';
            $event->instance = $wespher->id;
            $event->timestart = $wespher->timeopen;
            $event->timesort = $wespher->timeopen;
            $event->visible = instance_is_visible('leeloolxpvc', $wespher);
            $event->timeduration = 0;
            calendar_event::create($event);
        }
    }
    return true;
}
