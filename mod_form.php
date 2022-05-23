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
 * The main leeloolxpvc configuration form
 *
 * @package    mod_leeloolxpvc
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author     Leeloo LXP <info@leeloolxp.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Form Class
 */
class mod_leeloolxpvc_mod_form extends moodleform_mod {

    /**
     * Defination of form
     */
    public function definition() {

        global $CFG;

        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('wesphername', 'leeloolxpvc'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->setType('recordedurl', PARAM_TEXT);

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'wesphername', 'leeloolxpvc');

        $choicescompleted = array(
            0 => 'Pending',
            2 => 'Started',
            1 => 'Completed',
        );
        $mform->addElement('select', 'completed', get_string('completed', 'leeloolxpvc'), $choicescompleted);
        $mform->addElement('text', 'recordedurl', get_string('recordedurl', 'leeloolxpvc'), array('size' => '264'));

        $this->standard_intro_elements();

        $mform->addElement('header', 'availability', get_string('availability', 'assign'));
        $mform->setExpanded('availability', true);

        $name = get_string('allow', 'leeloolxpvc');

        $options = array('optional' => true);
        $mform->addElement('date_time_selector', 'timeopen', $name, $options);
        $choicesminspre = array(
            5 => 5,
            10 => 10,
            15 => 15,
            20 => 20,
            30 => 30,
        );

        $mform->addElement('select', 'beforetime', get_string('beforetime', 'leeloolxpvc'), $choicesminspre);

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }
}
