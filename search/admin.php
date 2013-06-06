<?php

require_once('../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/search/lib.php');
require_once('connection.php');


admin_externalpage_setup('globalsearch');
global $DB;

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
	$modcheckboxarray[] =& $mform->createElement('advcheckbox', 'all', '', 'All Modules', array('group' => 1));
	$modcheckboxarray[] =& $mform->createElement('advcheckbox', 'book', '', 'Book', array('group' => 2));
	$modcheckboxarray[] =& $mform->createElement('advcheckbox', 'glossary', '', 'Glossary', array('group' => 2));
	$modcheckboxarray[] =& $mform->createElement('advcheckbox', 'page', '', 'Page', array('group' => 2));
	$modcheckboxarray[] =& $mform->createElement('advcheckbox', 'forum', '', 'Forum', array('group' => 2));
	$modcheckboxarray[] =& $mform->createElement('advcheckbox', 'wiki', '', 'Wiki', array('group' => 2));
	$mform->addGroup($modcheckboxarray, 'modadvcheckbox', '', array(' '), false);
	$mform->closeHeaderBefore('modadvcheckbox');

	$mform->disabledIf('modadvcheckbox', 'delete', 'notchecked');
	$mform->disabledIf('index', 'delete', 'checked');
	$mform->disabledIf('optimize', 'delete', 'checked');
	$mform->disabledIf('index', 'optimize', 'checked');
	$mform->disabledIf('delete', 'optimize', 'checked');
	$mform->disabledIf('delete', 'index', 'checked');
	$mform->disabledIf('optimize', 'index', 'checked');
	
	$this->add_action_buttons($cancel = false);
	$mform->setDefault('action', '');	

  }

}

require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));

$mform = new search_admin_form();

if ($data = $mform->get_data()) {
  if (!empty($data->index)) {
    search_index($client);
  }
  if (!empty($data->optimize)) {
    search_optimize_index($client);
  }
  if (!empty($data->delete)) {
    if (!empty($data->all)){
    	$data->module = NULL;
    }
    else{
    	$a = '';
    	foreach ($data as $key => $value) {
 		   if ($value && $key!='delete' && $key!='submitbutton') {
				$a .= $key . ',';
    		}
		}
		$data->module = substr($a, 0, strlen($a) - 1);
    }
    search_delete_index($client, $data);
  }
}


echo $OUTPUT->header();
echo $OUTPUT->heading('Index statistics');
echo $OUTPUT->box_start();
echo $OUTPUT->box_end();
//echo $OUTPUT->heading('Last indexing statistics');
echo $OUTPUT->box_start();
echo $OUTPUT->box_end();
echo $OUTPUT->container_start();
echo $mform->display();
echo $OUTPUT->container_end();
echo $OUTPUT->footer();
