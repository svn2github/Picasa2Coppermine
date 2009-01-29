<?php
/****************************
  Picasa to Coppermine Upload
  ***************************
  Copyright (c) Aditya Mooley <adityamooley@sanisoft.com>

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License version 3
  as published by the Free Software Foundation.

  ********************************************
  Version: 1
  $HeadURL$
  $Revision$
  $LastChangedBy$
  $Date$
**********************************************/

if (!defined('IN_COPPERMINE')) { 
    die('Not in Coppermine...');
}

if (!GALLERY_ADMIN_MODE) {
  cpg_die($lang_common['error'], $lang_errors['access_denied'], __FILE__, __LINE__);
}

if($superCage->post->keyExists('action')){
    $action = $superCage->post->getAlpha('action');
} else {
    $action = $superCage->get->getAlpha('action');
}

switch ($action) {
    case 'config':
        pageheader('Configuration of Picasa Upload plugin');
        if ($superCage->post->keyExists('picsa_upload_config')) {
            picasa_update_config();
            msg_box($lang_common['information'], 'Setting updated successfully', $lang_common['continue'], 'pluginmgr.php');
        } else {
            picasa_plugin_configure();
        }
        break;
    
    default:
        cpg_die (ERROR, 'Picasa Upload Plugin: Unknown action value', __FILE__, __LINE__);
}

pagefooter();

ob_end_flush();
?>