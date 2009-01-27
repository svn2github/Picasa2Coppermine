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

pageheader_mini('Login', true);
echo '<link rel="stylesheet" href="plugins/picasa_upload/picasa_upload.css" type="text/css" />';
?>
<table align="center" width="50%">
    <tr>
        <td><img src="images/coppermine-logo.png" /></td>
        <td><h2><?php echo $CONFIG['gallery_name']; ?></h2></td>
    </tr>
</table>
<br />
<?php
if (USER_ID && $superCage->get->keyExists('p_force_logout')) {
    $cpg_udb->logout_page();
}
if (!USER_ID || $superCage->get->keyExists('p_force_logout')) {
echo '<form action="login.php?referer=' . urlencode('index.php?file=picasa_upload/picasa_login') . '" method="post" name="loginbox" id="cpgform">';

$username_icon = cpg_fetch_icon('my_profile', 2);
$password_icon = cpg_fetch_icon('key_enter', 2);
$ok_icon = cpg_fetch_icon('ok', 2);

starttable("75%", cpg_fetch_icon('login', 2) . $lang_login_php['enter_login_pswd'], 2);

//see how users are allowed to login, can be username, email address or both
$login_method = ($CONFIG['login_method'] == 'username') ? 'Username' : (($CONFIG['login_method'] == 'email') ? 'Email' : 'Username/Email');

echo <<< EOT
    <tr>
        <td class="tableb" width="40%">{$username_icon}{$login_method}</td>
        <td class="tableb" width="60%"><input type="text" class="textinput" name="username" style="width: 100%" tabindex="1" /></td>
    </tr>
    <tr>
        <td class="tableb">{$password_icon}Password</td>
        <td class="tableb"><input type="password" class="textinput" name="password" style="width: 100%" tabindex="2" /></td>
    </tr>
    <tr>
        <td align="center" class="tablef">
            &nbsp;
        </td>
        <td align="left" class="tablef">
        <button type="submit" class="button" name="submitted" value="{$lang_common['ok']}"  tabindex="4">{$ok_icon}{$lang_common['ok']}</button>
        </td>
    </tr>

EOT;

endtable();

echo <<< EOT
</form>
<script language="javascript" type="text/javascript">
<!--
document.loginbox.username.focus();
-->
</script>
EOT;

} else {
    msg_box(INFORMATION, "You have logged in successfully. Please continue to upload photos", 'Upload Photos', 'index.php?file=picasa_upload/picasa_form');
}
pagefooter_mini();
?>