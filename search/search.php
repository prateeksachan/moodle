<?php

require_once('../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/search/lib.php');

require_login();

class search_form extends moodleform {

    function definition() {

        $mform =& $this->_form;
		$mform->addElement('header', 'search', get_string('search', 'search'));

        $mform->addElement('text', 'queryfield', get_string('query', 'search'));
        $mform->setType('queryfield', PARAM_TEXT);
        $mform->addRule('queryfield', get_string('emptyqueryfield', 'search'), 'required', null, 'client');
        $mform->addRule('queryfield', get_string('maximumchars', '', 128), 'maxlength', 128, 'client');

        $this->add_action_buttons($cancel = false, $submitlabel='Search');
        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $mform->setDefault('action', '');
	}

}
$PAGE->set_context(get_system_context());

$solr_search_form = new search_form();
$solr_search_form->display();