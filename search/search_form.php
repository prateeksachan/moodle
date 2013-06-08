<?php

require_once($CFG->libdir . '/formslib.php');

class search_form extends moodleform {

    function definition() {

		$mform =& $this->_form;
		$mform->addElement('header', 'search', get_string('search', 'search'));

		$mform->addElement('text', 'queryfield', get_string('query', 'search'));
		$mform->setType('queryfield', PARAM_TEXT);
		$mform->addRule('queryfield', get_string('emptyqueryfield', 'search'), 'required', null, 'client');

		$this->add_action_buttons($cancel = false, $submitlabel='Search');
		$mform->setDefault('action', '');

	}

}
$PAGE->set_context(get_system_context());
