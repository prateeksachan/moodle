<?php

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/search/lib.php');
 
class globalsearch_form extends moodleform {
 
    function definition() {

		$mform =& $this->_form;
		$mform->addElement('header', 'search', get_string('search', 'search'));

		$mform->addElement('text', 'queryfield', get_string('query', 'search'));
		$mform->addHelpButton('queryfield', 'globalsearch', 'search');
		$mform->setType('queryfield', PARAM_TEXT);
		$mform->addRule('queryfield', get_string('emptyqueryfield', 'search'), 'required', null, 'client');

		//-------------------------------------------------------
		$mform->addElement('header', 'filterquerysection', get_string('filterqueryheader', 'search'));
	    $mform->addElement('text', 'titlefilterqueryfield', get_string('titlefilterquery', 'search'));
		$mform->setType('titlefilterqueryfield', PARAM_TEXT);
		
		$mform->addElement('text', 'authorfilterqueryfield', get_string('authorfilterquery', 'search'));
		$mform->setType('authorfilterqueryfield', PARAM_TEXT);
		
		$mods = search_get_modules();
		$modules = array();
		$modules [] = "All modules";
		foreach ($mods as $mod){
			$modules[$mod->name] = ucfirst($mod->name);
		}
		$mform->addElement('select', 'modulefilterqueryfield', get_string('modulefilterquery', 'search'), $modules);
		
		$this->add_action_buttons($cancel = false, $submitlabel='Search');
		$mform->setDefault('action', '');

	}
}
