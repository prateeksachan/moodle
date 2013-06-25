<?php

/** 
 * This is the globalsearch block.
 * A single query will be entered.
 * Users will be navigated to another page displaying the results.
 *
 * @package globalsearch
 * @subpackage globalsearch block
 * @date: 2013 06 22
 */

class block_globalsearch extends block_base {

  function init() {
     $this->title = get_string('pluginname', 'block_globalsearch');
  }
	
	//this block has a settings.php file @TODO settings.php
	function has_config() {
    return true;
  } 

	function get_content() {
		global $CFG, $OUTPUT;
    if ($this->content !== null) {
  	 	return $this->content;
    }

    require_once('globalsearch_html.php');
    $block_mform = new globalsearch_form();
    
    $this->content         =  new stdClass;
    $this->content->footer = '';

    $url = new moodle_url('/search/index.php');
		$this->content->footer .= html_writer::link($url, get_string('advancequeries', 'search'));
		
		$this->content->text  = html_writer::start_tag('div', array('class' => 'searchform'));
    //$this->content->text .= $block_mform->display();
    $this->content->text .= '<form action="'.$CFG->wwwroot.'/search/index.php" style="display:inline">';
    $this->content->text .= '<fieldset class="invisiblefieldset">';
    $this->content->text .= '<label for="searchform_search">Search: </label>'.
                            '<input id="searchform_search" name="search" type="text" size="15" /><br>';
    $this->content->text .= '<label for="searchform_fq_module">Module Filter: </label>'.
                            '<input id="searchform_fq_module" name="fq_module" type="text" size="15" />';
    $this->content->text .= '<button id="searchform_button" type="submit" title="globalsearch">Go!</button><br />';
    $this->content->text .= $OUTPUT->help_icon('globalsearch', 'search');
    $this->content->text .= '</fieldset>';
    $this->content->text .= '</form>';
    $this->content->text .= html_writer::end_tag('div');

    return $this->content;
  }

  	//running the cron job for indexing
	function cron(){
		global $CFG;

    include($CFG->dirroot . '/search/cron.php');
  }

}
