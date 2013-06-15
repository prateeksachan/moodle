<?php

require_once('../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/search/lib.php');

//admin_externalpage_setup('globalsearch');

class search_admin_form extends moodleform {

  function definition() {

    $mform = & $this->_form;
    $checkboxarray = array();
	$checkboxarray[] =& $mform->createElement('checkbox', 'index', '', get_string('index', 'search'));
	$checkboxarray[] =& $mform->createElement('checkbox', 'optimize', '', get_string('optimize', 'search'));
	$checkboxarray[] =& $mform->createElement('checkbox', 'delete', '', get_string('delete', 'search'));
	$mform->addGroup($checkboxarray, 'indexcheckbox', '', array(' '), false);
	$mform->closeHeaderBefore('indexcheckbox');
	
	$modcheckboxarray = array();
	$modcheckboxarray[] =& $mform->createElement('checkbox', 'all', '', 'All Modules');
	$modcheckboxarray[] =& $mform->createElement('checkbox', 'book', '', 'Book');
	$modcheckboxarray[] =& $mform->createElement('checkbox', 'glossary', '', 'Glossary');
	$modcheckboxarray[] =& $mform->createElement('checkbox', 'page', '', 'Page');
	$modcheckboxarray[] =& $mform->createElement('checkbox', 'forum', '', 'Forum');
	$modcheckboxarray[] =& $mform->createElement('checkbox', 'wiki', '', 'Wiki');
	$mform->addGroup($modcheckboxarray, 'modcheckbox', '', array(' '), false);
	$mform->closeHeaderBefore('modcheckbox');

	$mform->disabledIf('modcheckbox', 'indexcheckbox', 'checked');
	
	$this->add_action_buttons($cancel = false);
	$mform->setDefault('action', '');

  }

}
