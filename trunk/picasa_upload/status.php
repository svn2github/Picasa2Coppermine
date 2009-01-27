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
  $HeadURL:$
  $Revision:$
  $LastChangedBy:$
  $Date:$
**********************************************/

if (!defined('IN_COPPERMINE')) die('Not in Coppermine...');
pageheader($lang_common['information']);

if ('success' == $superCage->get->getEscaped('status')) {
    $mesg = $superCage->get->getEscaped('mesg').'<br />Click on Continue to add details for the uploaded file(s).';
    msg_box($lang_common['information'], $mesg, $lang_common['continue'], 'editpics.php?album='.$superCage->get->getInt('aid'));
} else {
    msg_box($lang_common['error'], $superCage->get->getEscaped('mesg'), $lang_common['continue'], 'index.php', 'error');
}

pagefooter();
?>