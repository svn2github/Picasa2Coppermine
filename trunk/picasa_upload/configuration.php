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

$name = 'Picasa to Coppermine Upload';
$description = 'Plugin to upload the selected files from Google Picasa to your Coppermine Photo Gallery';
$author = 'Aditya Mooley';
$version = '1';
$link = 'picasa://importbutton/?url='.$CONFIG['ecards_more_pic_target'].'albums/edit/coppermine.pbz';
$extra_info = <<<EOT
<table border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td class="admin_menu">
          <a href="$link" title="Picasa Button">Install Picasa Button</a>
        </td>
    </tr>
</table>
EOT;
// $install_info = $lang_plugin_php['onlinestats_config_install'];
?>