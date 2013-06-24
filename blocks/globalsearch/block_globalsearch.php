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
	
	//this block has a settings.php file
	function has_config() {
      return true;
    }

	function get_content() {
	    if ($this->content !== null) {
	    	return $this->content;
	    }
	 
	    $this->content         =  new stdClass;
	    $this->content->text   = 'The content of our Global Search block!';
	    $this->content->footer = '';
	 
	    return $this->content;
  	}

  	//running the cron job for indexing
  	function cron(){
  		global $CFG;
	
		include($CFG->dirroot . '/search/cron.php');
    }
}
