<?php

require_once($CFG->libdir . '/formslib.php');

class search_form extends moodleform {

    function definition() {

		$mform =& $this->_form;
		$mform->addElement('header', 'search', get_string('search', 'search'));

		$mform->addElement('text', 'queryfield', get_string('query', 'search'));
		$mform->setType('queryfield', PARAM_TEXT);
		$mform->addRule('queryfield', get_string('emptyqueryfield', 'search'), 'required', null, 'client');

		get_string('filterquery', 'search');

		$mform->addElement('text', 'titlefilterqueryfield', get_string('titlefilterquery', 'search'));
		$mform->setType('titlefilterqueryfield', PARAM_TEXT);
		
		$mform->addElement('text', 'authorfilterqueryfield', get_string('authorfilterquery', 'search'));
		$mform->setType('authorfilterqueryfield', PARAM_TEXT);
		
		$mform->addElement('text', 'modulefilterqueryfield', get_string('modulefilterquery', 'search'));
		$mform->setType('modulefilterqueryfield', PARAM_TEXT);

		$this->add_action_buttons($cancel = false, $submitlabel='Search');
		$mform->setDefault('action', '');

	}

}
$PAGE->set_context(get_system_context());
