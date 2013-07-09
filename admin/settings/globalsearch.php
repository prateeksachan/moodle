<?php

if ($hassiteconfig) { // speedup for non-admins, add all caps used on this page

    // "solr" settingpage
    
    $temp = new admin_settingpage('solrsettingpage', new lang_string('solrsetting', 'admin'));
    
    $hostname = '127.0.0.1';
    $temp->add(new admin_setting_configtext('SOLR_SERVER_HOSTNAME', new lang_string('solrserverhostname', 'admin'), new lang_string('solrserverhostname_desc', 'admin'), $hostname, PARAM_TEXT));
    $temp->add(new admin_setting_configcheckbox('SOLR_SECURE', new lang_string('solrsecuremode', 'admin'), new lang_string('solrsecuremode_desc', 'admin'), 0, 1, 0));
    
    $version = solr_get_version();
    $options = array('4.0'=>'4.x', '3.0'=>'3.x');
    if ($version != '1.0.3-alpha'){
        array_shift($options);
    }
    $temp->add(new admin_setting_configselect('SOLR_VERSION', new lang_string('solrversion', 'admin'), new lang_string('solrversion_desc', 'admin', $version), ($version == '1.0.3-alpha' ? '4.0' : '3.0'), $options));    
    
    $temp->add(new admin_setting_configtext('SOLR_SERVER_PORT', new lang_string('solrhttpconnectionport', 'admin'), new lang_string('solrhttpconnectionport_desc', 'admin'), (isset($CFG->SOLR_SECURE) ? 8443 : 8983), PARAM_INT));
    $temp->add(new admin_setting_configtext('SOLR_SERVER_USERNAME', new lang_string('solrauthuser', 'admin'), new lang_string('solrauthuser_desc', 'admin'), '', PARAM_RAW));
    $temp->add(new admin_setting_configtext('SOLR_SERVER_PASSWORD', new lang_string('solrauthpassword', 'admin'), new lang_string('solrauthpassword_desc', 'admin'), '', PARAM_RAW));
    $temp->add(new admin_setting_configtext('SOLR_SERVER_TIMEOUT', new lang_string('solrhttpconnectiontimeout', 'admin'), new lang_string('solrhttpconnectiontimeout_desc', 'admin'), 30, PARAM_INT));
    $temp->add(new admin_setting_configtext('SOLR_SSL_CERT', new lang_string('solrsslcert', 'admin'), new lang_string('solrsslcert_desc', 'admin'), '', PARAM_RAW));
    $temp->add(new admin_setting_configtext('SOLR_SSL_CERT_ONLY', new lang_string('solrsslcertonly', 'admin'), new lang_string('solrsslcertonly_desc', 'admin'), '', PARAM_RAW));
    $temp->add(new admin_setting_configtext('SOLR_SSL_KEY', new lang_string('solrsslkey', 'admin'), new lang_string('solrsslkey_desc', 'admin'), '', PARAM_RAW));
    $temp->add(new admin_setting_configtext('SOLR_SSL_KEYPASSWORD', new lang_string('solrsslkeypassword', 'admin'), new lang_string('solrsslkeypassword_desc', 'admin'), '', PARAM_RAW));
    $temp->add(new admin_setting_configtext('SOLR_SSL_CAINFO', new lang_string('solrsslcainfo', 'admin'), new lang_string('solrsslcainfo_desc', 'admin'), '', PARAM_RAW));
    $temp->add(new admin_setting_configtext('SOLR_SSL_CAPATH', new lang_string('solrsslcapath', 'admin'), new lang_string('solrsslcapath_desc', 'admin'), '', PARAM_RAW));            

    $ADMIN->add('globalsearch', $temp);

    $temp = new admin_settingpage('supportedmods', new lang_string('supportedmods', 'admin'));
    $temp->add(new admin_setting_configcheckbox('gs_support_book', new lang_string('gs_support_book', 'admin'), new lang_string('gs_support_book_desc', 'admin'), 1, 1, 0));
    $temp->add(new admin_setting_configcheckbox('gs_support_forum', new lang_string('gs_support_forum', 'admin'), new lang_string('gs_support_forum_desc', 'admin'), 1, 1, 0));
    $temp->add(new admin_setting_configcheckbox('gs_support_label', new lang_string('gs_support_label', 'admin'), new lang_string('gs_support_label_desc', 'admin'), 1, 1, 0));
    $temp->add(new admin_setting_configcheckbox('gs_support_lesson', new lang_string('gs_support_lesson', 'admin'), new lang_string('gs_support_lesson_desc', 'admin'), 1, 1, 0));
    $temp->add(new admin_setting_configcheckbox('gs_support_page', new lang_string('gs_support_page', 'admin'), new lang_string('gs_support_page_desc', 'admin'), 1, 1, 0));
    $temp->add(new admin_setting_configcheckbox('gs_support_url', new lang_string('gs_support_url', 'admin'), new lang_string('gs_support_url_desc', 'admin'), 1, 1, 0));
    $temp->add(new admin_setting_configcheckbox('gs_support_wiki', new lang_string('gs_support_wiki', 'admin'), new lang_string('gs_support_wiki_desc', 'admin'), 1, 1, 0));
    
    $ADMIN->add('globalsearch', $temp);    

    $ADMIN->add('globalsearch', new admin_externalpage('statistics', new lang_string('statistics','admin'), "$CFG->wwwroot/search/admin.php"));
}
